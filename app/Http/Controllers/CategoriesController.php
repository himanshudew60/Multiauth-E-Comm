<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
   public function index(Request $request)
{
    $pageTitle = 'Categories';
    $query = $this->filterCategories($request);
    $categories = $query->latest()->paginate(5)->appends($request->query());

    return view('admin.categories.index', compact('categories', 'pageTitle'));
}

    private function filterCategories(Request $request)
{
    // Validate
    $validated = $request->validate([
        'name'        => ['nullable','string','min:3','regex:/^[A-Za-z\s]+$/'],
        'start_date'  => ['nullable','date'],
        'end_date'    => ['nullable','date'],
    ], [
        'name.regex'  => 'The name may only contain letters and spaces.',
    ]);

    // Build the query
    $query = Category::query();

    if (!empty($validated['name'])) {
        $query->where('name', 'like', '%' . $validated['name'] . '%');
    }

    if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
        $query->whereBetween('created_at', [
            $validated['start_date'],
            $validated['end_date'] . ' 23:59:59'
        ]);
    } elseif (!empty($validated['start_date'])) {
        $query->whereDate('created_at', '>=', $validated['start_date']);
    } elseif (!empty($validated['end_date'])) {
        $query->whereDate('created_at', '<=', $validated['end_date']);
    }

    return $query;
}



    public function create()
    {
        $pageTitle ='Category | Create';
        return view('admin.categories.create' , compact('pageTitle'));
    }

    public function store(Request $request)
    {
       
       
            // Decrypt the AES payload (ensure your decryptAES helper works securely)
            $encryptedPayload = $request->input('payload');
            $decryptedJson = decryptAES($encryptedPayload);
            
            
            $data = json_decode($decryptedJson, true);
           
    
    
            // Merge decrypted data into request for validation
            $request->merge($data);
    
            // Validate the decrypted data
            $request->validate([
                'name' => 'required|string|max:255'
            ]);
    
            // Store the category
            Category::create([
                'name' => $request->name
            ]);
    
            return response()->json(['success' => true, 'message' => 'Category Created successfully!']);
    }
    

    public function edit(string $uuid)
    {
        $pageTitle ='Category | Update';
        $category = Category::where('uuid', $uuid)->firstOrFail();
        return view('admin.categories.edit', compact('category','pageTitle'));
    }

    public function update(Request $request, string $uuid)
    {
        $validated = $request->validate(['name' => 'required|string|max:255','status' => 'required|integer|in:0,1',]);
        
        $category = Category::where('uuid', $uuid)->firstOrFail();
        $category->update($validated);

        return response()->json(['success' => true, 'message' => 'Category updated successfully!']);
    }

    public function destroy(string $uuid)
    {
        $category = Category::where('uuid', $uuid)->firstOrFail();
        $category->softDelete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
    public function exportCSV(Request $request)
    {
        $fileName = 'categories_' . now()->format('Ymd_His') . '.csv';

          $query = $this->filterCategories($request);
        $categories =  $query->latest()->get(); // Get all customer records

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name','Created At'];

        $callback = function () use ($categories, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

           

            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->name,                   
                    $category->created_at->format('d-m-Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function exportPdfView(Request $request)
    {
        $query = $this->filterCategories($request);
        $categories =  $query->latest()->get(); // Get all customer records
        return view('admin.categories.pdf', compact('categories'));
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

                // Optional: Skip empty names or duplicate entries
                if (empty($name)) continue;

                Category::firstOrCreate(['name' => $name]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Categories imported successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to import categories: ' . $e->getMessage());
        }
    }

    return redirect()->back()->with('error', 'Invalid file format.');
}
}
