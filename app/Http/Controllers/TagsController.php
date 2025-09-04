<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function index(Request $request)
{
   
    $pageTitle = 'Tags';
    $query = $this->filterTags($request);
    
    $tags = $query->latest()->paginate(5)->appends($request->query());
    

    return view('admin.tags.index', compact('tags', 'pageTitle'));
}

    // In TagsController.php
private function filterTags(Request $request)
{
    // Validate
    $validated = $request->validate([
        'name' => ['nullable', 'string', 'min:3', 'regex:/^[A-Za-z\s]+$/'],
        'start_date' => ['nullable', 'date'],
        'end_date' => ['nullable', 'date'],
    ], [
        'name.regex' => 'The name may only contain letters and spaces.',
    ]);

    // Build query
    $query = Tag::query();

    if (!empty($validated['name'])) {
        $query->where('name', 'like', '%' . $validated['name'] . '%');
    }

    if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
        $query->whereBetween('created_at', [
            $validated['start_date'],
            $validated['end_date'] . ' 23:59:59',
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
        $pageTitle = 'Tags | Create';
        return view('admin.tags.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $encryptedPayload = $request->input('payload');
        $decryptedJson = decryptAES($encryptedPayload);
        $data = json_decode($decryptedJson, true);

        $request->merge($data);

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        Tag::create(['name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Tag created successfully!']);
    }

    public function edit(string $uuid)
    {
        $pageTitle = 'Tags | Update';
        $tag = Tag::where('uuid', $uuid)->firstOrFail();
        return view('admin.tags.edit', compact('tag', 'pageTitle'));
    }

   public function update(Request $request, string $uuid)
{
    // Optional: Debug to check incoming value
    // dd($request->status);

    // Validate request data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'status' => 'required|integer|in:0,1', // FIXED: 'tinyint' replaced with 'integer'
    ]);

    // Find the tag by UUID or fail with 404
    $tag = Tag::where('uuid', $uuid)->firstOrFail();

    // Update the tag with validated data
    $tag->update($validated);

    // Return a success JSON response
    return response()->json([
        'success' => true,
        'message' => 'Tag updated successfully!',
    ]);
}

    public function destroy(string $uuid)
    {
        $tag = Tag::where('uuid', $uuid)->firstOrFail();
        $tag->softDelete();

        return redirect()->back()->with('success', 'Tag deleted successfully!');
    }


    public function exportCSV(Request $request)
    {
        $fileName = 'tags_' . now()->format('Ymd_His') . '.csv';

         $query = $this->filterTags($request);
  $tags = $query->latest()->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name', 'Created At'];

        $callback = function () use ($tags, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);


            foreach ($tags as $tag) {
                fputcsv($file, [
                    $tag->name,
                    $tag->created_at->format('d-m-Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
  public function exportPdfView(Request $request)
{
    
  $query = $this->filterTags($request);
  $tags = $query->latest()->get();
  
    $pageTitle = 'Tags PDF';

    return view('admin.tags.pdf', compact('tags', 'pageTitle'));
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
                    if (empty($name))
                        continue;

                    Tag::firstOrCreate(['name' => $name]);
                }
                DB::commit();
                return redirect()->back()->with('success', 'Tags imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to import categories: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Invalid file format.');
    }
}
