<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ProductQuantity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)

    {
        $pageTitle = 'Products';
        $query = $this->filterProducts($request);
       $products = $query->latest()->paginate(10)->appends($request->query());
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.products.index', compact('products', 'categories', 'tags','pageTitle'));
    }


    private function filterProducts(Request $request){
        $validated = $request->validate([
            'name' => [
                'nullable',
                'string',
                'min:3',
                'regex:/^[A-Za-z\s]+$/'
            ],
            'price_min' => ['nullable', 'numeric'],
            'price_max' => ['nullable', 'numeric'],
            'created_at_start' => ['nullable', 'date'],
            'created_at_end' => ['nullable', 'date'],
            'category_id' => ['nullable', 'array'],
            'category_id.*' => ['exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $query = Product::with(['category', 'tags']);

        if (!empty($validated['name'])) {
            $query->where('name', 'like', '%' . $validated['name'] . '%');
        }

        if (!empty($validated['price_min'])) {
            $query->where('price', '>=', $validated['price_min']);
        }

        if (!empty($validated['price_max'])) {
            $query->where('price', '<=', $validated['price_max']);
        }

        if (!empty($validated['created_at_start']) && !empty($validated['created_at_end'])) {
            $query->whereBetween('created_at', [
                $validated['created_at_start'],
                $validated['created_at_end']
            ]);
        }

        if (!empty($validated['category_id'])) {
            $query->wherein('category_id', $validated['category_id']);
        }

        if (!empty($validated['tag_ids'])) {
            $query->whereHas('tags', function ($q) use ($validated) {
                $q->whereIn('tags.id', $validated['tag_ids']);
            });
        }
        return $query;
    }


    public function create()
    {
        $pageTitle = 'Products | Create';
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.products.create', compact('categories', 'tags','pageTitle'));
    }

    public function store(Request $request)
    {
        try {
            $decryptedPayload = decryptAES($request->input('payload'));
            $data = json_decode($decryptedPayload, true);
            

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Invalid data after decryption.']);
            }

            $validator = Validator::make($data, [
                'name' => ['required', 'regex:/^[\p{L}\s0-9\-.,]+$/u'],
                'price' => ['required', 'numeric', 'min:0'],
                'qty' => ['required', 'numeric', 'min:0'],
                'category_id' => ['required', 'exists:categories,id'],
                'tags' => ['required', 'array'],
                'tags.*' => ['exists:tags,id']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'field_errors' => $validator->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }

            $validated = $validator->validated();

            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if ($image->isValid()) {
                        $path = $image->store('products', 'public');
                        $imagePaths[] = $path;
                    }
                }
            }

            $product = new Product();
            $product->uuid = Str::uuid();
            $product->name = $validated['name'];
            $product->price = $validated['price'];
            $product->category_id = $validated['category_id'];
            $product->photo = json_encode($imagePaths);
            $product->save();

            $product->tags()->sync($validated['tags']);

    ProductQuantity::create([
    'product_id' => $product->id,
    'qty' => $validated['qty']
]);

            return response()->json(['success' => true, 'message' => 'Product created successfully.']);

        } catch (\Exception $e) {
            Log::error('Product Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function show($uuid)
    {
        $pageTitle = 'Products | View';
        $product = Product::with(['category', 'tags'])->where('uuid', $uuid)->firstOrFail();
        return view('admin.products.show', compact('product','pageTitle'));
    }

    public function edit($uuid)
    {
        $pageTitle = 'Products | Update';
        $product = Product::with('tags','quantity')->where('uuid', $uuid)->firstOrFail();
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.products.edit', compact('product', 'categories', 'tags','pageTitle'));
    }
public function update(Request $request, $uuid)
{
    try {
        $decrypted = decryptAES($request->input('payload'));
        $data = json_decode($decrypted, true);

        if (!$data) {
            return response()->json(['message' => 'Invalid decrypted data.'], 422);
        }

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s0-9\-.,]+$/u'],
            'price' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'status' => ['required', 'in:0,1']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->status = $validated['status'];
        $product->category_id = $validated['category_id'] ?? null;

        // Handle photo upload or removal
        if ($request->hasFile('photo')) {
            if ($product->photo) {
                foreach (json_decode($product->photo, true) ?? [] as $oldPhotoPath) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }
            }

            $file = $request->file('photo');
            $path = $file->store('products', 'public');
            $product->photo = json_encode([$path]);
        } elseif ($request->input('remove_existing_photo') === '1') {
            if ($product->photo) {
                foreach (json_decode($product->photo, true) ?? [] as $photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
            }
            $product->photo = null;
        }

        $product->save();

        // Update tags
        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $product->tags()->sync($validated['tags']);
        } else {
            $product->tags()->detach();
        }

        // Update or create quantity
        $product->quantity()->updateOrCreate(
            ['product_id' => $product->id],
            ['qty' => $validated['qty']]
        );

        return response()->json(['message' => 'Product updated successfully.']);

    } catch (\Exception $e) {
        Log::error('Product Update Error: ' . $e->getMessage());
        return response()->json(['message' => 'Server error.'], 500);
    }
}

    public function destroy($uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();

        // Call the overridden delete method to set 'is_deleted' to 1
        $product->softDelete();

        return redirect()->back()->with('success', 'Product deleted successfully!');
    }
    public function exportCSV(Request $request)
    {
        $fileName = 'products_' . now()->format('Ymd_His') . '.csv';
  
        $query = $this->filterProducts($request);
    $products = $query->latest()->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name','Price','Category','Created At'];

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->price,
                    $product->category->name,
                    $product->created_at->format('d-m-Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function exportPdfView(Request $request)
{
    $query = $this->filterProducts($request);
    $products = $query->latest()->get();

    return view('admin.products.pdf', compact('products'));
}
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');

        // Open and read the file
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle); // Read the first line as header
           
            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle)) !== false) {
                
                    // Assuming the CSV column order is: Name
                    $name = $row[0];
                   
                    $price=$row[1];
                    $categoryname = $row[2];
                    


                    // Optional: Skip empty names or duplicate entries
                    if (empty($name))
                        continue;

                    $category = Category::firstOrCreate(['name'=>$categoryname]);
                    $category_id = $category->id;

                    $product = Product::firstOrCreate(
                        ['name' => $name,
                            'price' => $price,
                            'category_id' => $category_id
                       
                        ]
                    );
                    ProductQuantity::updateOrCreate(
                    ['product_id' => $product->id],
                    ['qty' => $row[3]]
                );
                }
                DB::commit();
                return redirect()->back()->with('success', 'Product imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to import categories: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Invalid file format.');
    }

}
