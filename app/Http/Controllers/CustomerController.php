<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;

;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Customers';
        // Validate the inputs

        $query = $this->filterCustomer($request);
        $customers = $query->latest()->paginate(10)->appends($request->query());

        return view('admin.customers.index', compact('customers', 'pageTitle'));
    }

    private function filterCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'nullable',
                'string',
                'min:3',
                'regex:/^[A-Za-z\s]+$/'
            ],
            'number' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'array'],
            'gender.*' => ['in:1,2,3'],
        ], [
            'name.regex' => 'The name may only contain letters and spaces.',
        ]);

        $query = Customer::query();

        // Name Filter (only if 3+ characters)
        if (!empty($validated['name'])) {
            $query->where('name', 'like', '%' . $validated['name'] . '%');
        }
        if (!empty($validated['gender'])) {
            $query->whereIn('gender', $validated['gender']);
        }
        // Phone Number Filter (digits only)
        if (!empty($validated['number'])) {
            $number = preg_replace('/\D/', '', $validated['number']);
            if (!empty($number)) {
                $query->where('number', 'like', '%' . $number . '%');
            }
        }

        // Date Range Filter
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
        $pageTitle = 'Customers | Create';
        return view('admin.customers.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        try {
            $encryptedPayload = decryptAES($request->input('payload'));
            $data = json_decode($encryptedPayload, true);

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Decryption failed: Invalid data.']);
            }

            $validator = Validator::make($data, [
                'name' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
                'email' => ['required', 'email', 'unique:customers,email'],
                'password' => [
                    'required',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*?&]/',
                ],
                'number' => ['required', 'digits_between:10,15', 'regex:/^[6-9]\d{9}$/'],
                'gender' => ['required', 'in:1,2,3'],
                'bio' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'field_errors' => $validator->errors(),
                    'message' => 'Validation failed.',
                ], 422);
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');

                if ($photo->getSize() > 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Photo must be less than 1MB.']);
                }

                $photoPath = $photo->store('photos', 'public');
            }

            Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'number' => $data['number'],
                'gender' => $data['gender'],
                'bio' => $data['bio'],
                'photo' => $photoPath,
            ]);

            return response()->json(['success' => true, 'message' => 'Customer created successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Processing failed: ' . $e->getMessage()]);
        }
    }

    public function show(string $uuid)
    {
        $pageTitle = 'Customers | View';
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        return view('admin.customers.show', compact('customer', 'pageTitle'));
    }

    public function edit(string $uuid)
    {
        $pageTitle = 'Customers | Update';
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        return view('admin.customers.edit', compact('customer', 'pageTitle'));
    }

    public function update(Request $request, string $uuid)
    {

        // Validate decrypted AES payload data
        $encryptedPayload = decryptAES($request->input('payload'));
        $data = json_decode($encryptedPayload, true);
        $request->merge($data);

        $validator = Validator::make($data, [
            'name' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'unique:customers,email,' . Customer::where('uuid', $uuid)->firstOrFail()->id],
            'password' => ['nullable', 'min:8'],
            'number' => ['required', 'digits_between:10,15'],
            'gender' => ['required', 'in:1,2,3'],
            'bio' => ['required', 'string'],
            'status' => ['required','in:0,1']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'field_errors' => $validator->errors(),
                'message' => 'Validation failed.',
            ], 422);
        }

        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        $password = !empty($data['password']) ? bcrypt($data['password']) : $customer->password;

        // Photo handling
        $photoPath = $customer->photo;
        if ($request->hasFile('photo') || $request->input('remove_existing_photo') === '1') {
            if ($request->input('remove_existing_photo') === '1' && $photoPath) {
                Storage::disk('public')->delete($photoPath);
                $photoPath = null;
            }
            if ($request->hasFile('photo')) {
                if ($request->file('photo')->getSize() > 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Photo must be less than 1MB.']);
                }
                $photoPath = $request->file('photo')->store('photos', 'public');
            }
        }

        // Update record
        $customer->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
            'number' => $data['number'],
            'gender' => $data['gender'],
            'bio' => $data['bio'],
            'photo' => $photoPath,
            'status' => $data['status']
        ]);

        return response()->json(['success' => true, 'message' => 'Customer updated successfully!']);
    }

    public function destroy(string $uuid)
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        if ($customer->photo) {
            Storage::disk('public')->delete($customer->photo);
        }

        $customer->softDelete(); // Use custom soft delete logic
        return redirect()->back()->with('success', 'Customer deleted successfully!');
    }

    public function exportCSV(Request $request)
    {
        $fileName = 'customers_' . now()->format('Ymd_His') . '.csv';

        $query = $this->filterCustomer($request);
        $customers = $query->latest()->get(); // Get all customer records

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name', 'Email', 'Phone Number', 'Bio', 'Gender', 'Created At'];

        $callback = function () use ($customers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $genderMap = [1 => 'Male', 2 => 'Female', 3 => 'Other'];

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->email,
                    $customer->number,
                    $customer->bio,
                    $genderMap[$customer->gender] ?? 'N/A',
                    $customer->created_at->format('d-m-Y H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdfView(Request $request)
    {
        $query = $this->filterCustomer($request);
        $customers = $query->latest()->get(); // Get all customer records
        return view('admin.customers.pdf', compact('customers'));
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
                    $email = $row[1];
                    $number = $row[2];
                    $bio = $row[3];
                    $gender = isset($row[4])
                        ? ($row[4] === 'Male' ? 1 : ($row[4] === 'Female' ? 2 : 3))
                        : 3;


                    // Optional: Skip empty names or duplicate entries
                    if (empty($name))
                        continue;

                    Customer::firstOrCreate(
                        ['email' => $email], // Match only by unique email
                        [
                            'name' => $name,
                            'number' => $number,
                            'bio' => $bio,
                            'gender' => $gender,
                            'password' => bcrypt('password')
                        ]
                    );

                }
                DB::commit();
                return redirect()->back()->with('success', 'Customer imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to import Customer: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Invalid file format.');
    }
}
