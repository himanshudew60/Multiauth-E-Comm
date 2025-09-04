@extends('layouts.app')

@section('title', 'User List')

@section('content')
    <title>{{ $pageTitle }}</title>
 <div class="container ">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="mb-0">User List</h2>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>

            


            <form action="{{ route('admin.customers.export.csv') }}" method="POST">
    @csrf
    <input type="hidden" name="name" value="{{ request('name') }}">
    <input type="hidden" name="email" value="{{ request('email') }}">
    <input type="hidden" name="number" value="{{ request('number') }}">
    @if(request()->has('gender'))
        @foreach((array)request('gender') as $g)
            <input type="hidden" name="gender[]" value="{{ $g }}">
        @endforeach
    @endif
    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
    <input type="hidden" name="end_date" value="{{ request('end_date') }}">

    <button type="submit" class="btn btn-outline-info mt-3">
       <i class="fa-solid fa-file-csv me-1"></i> Excel
    </button>
</form>

                <form action="{{ route('admin.customers.pdf') }}" method="POST" target="_blank">
    @csrf
    <input type="hidden" name="name" value="{{ request('name') }}">
    <input type="hidden" name="email" value="{{ request('email') }}">
    <input type="hidden" name="number" value="{{ request('number') }}">
    @if(request()->has('gender'))
        @foreach((array)request('gender') as $g)
            <input type="hidden" name="gender[]" value="{{ $g }}">
        @endforeach
    @endif
    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
    <input type="hidden" name="end_date" value="{{ request('end_date') }}">

    <button type="submit" class="btn btn-outline-danger mt-3">
        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
    </button>
</form>

            {{-- Import Form --}}
            <form class="mt-3" action="{{ route('admin.customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="csv_file" class="form-control" required>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                    <a href="{{ asset("sample_csv/customers_20250514_063056.csv") }}" class="btn btn-secondary"><i class="bi bi-download me-1"></i> Sample</a>
                </div>
            </form>
        </div>
    </div>
</div>


  @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
  @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Filter Form -->
        <form id="filterForm" method="POST" action="{{ route('admin.customers.search') }}" class="row g-3 align-items-end mb-4">
            @csrf

            <!-- Name -->
            <div class="col-md-3 col-lg-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" value="{{ request('name') }}" class="form-control"
                    placeholder="Enter name" maxlength="50" pattern="[A-Za-z\s]*" title="Only letters allowed">
            </div>

            <!-- Gender -->
            <div class="col-md-3 col-lg-3">
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender[]" class="form-select w-100" multiple>
                        
                    <option value="1" {{ collect(request('gender'))->contains('1') ? 'selected' : '' }}>Male</option>
                    <option value="2" {{ collect(request('gender'))->contains('2') ? 'selected' : '' }}>Female</option>
                    <option value="3" {{ collect(request('gender'))->contains('3') ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <!-- Phone Number -->
            <div class="col-md-3 col-lg-2">
                <label for="number" class="form-label">Phone Number</label>
                <input type="text" id="number" name="number" value="{{ request('number') }}" class="form-control"
                    placeholder="Enter number" maxlength="10" pattern="\d{10}" title="Enter 10 digit number only">
            </div>

            <!-- Start Date -->
            <div class="col-md-3 col-lg-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>

            <!-- End Date -->
            <div class="col-md-3 col-lg-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                </a>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Serial No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="text-end">Phone</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $index => $customer)
                        <tr>
                            <td>{{ $customers->firstItem() + $index }}</td>
                            <td>{{ $customer->name }}</td>
                            <td><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></td>
                            <td class="text-end"><a href="phoneto:{{ $customer->number }}">{{ $customer->number }}</a></td>
                            <td>
    @php
        $genderMap = [
            1 => ['label' => 'Male', 'class' => 'bg-primary'],
            2 => ['label' => 'Female', 'class' => 'bg-pink'], // custom color if using extended palette
            3 => ['label' => 'Other', 'class' => 'bg-secondary'],
        ];
        $gender = $genderMap[$customer->gender] ?? ['label' => 'N/A', 'class' => 'bg-dark'];
    @endphp

    <span class="badge {{ $gender['class'] }}">{{ $gender['label'] }}</span>
</td>
                            <td>
                        @if($customer->status == "0")
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                            <td>{{ $customer->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer->uuid) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('admin.customers.edit', $customer->uuid) }}" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm deleteBtn" data-uuid="{{ $customer->uuid }}">Delete</button>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center">
            <div class="mb-4">
                <strong>Showing {{ $customers->firstItem() }} - {{ $customers->lastItem() }} of {{ $customers->total() }} users</strong>
            </div>
            <nav>
                {{ $customers->withQueryString()->links('pagination::bootstrap-4') }}
            </nav>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

<!-- Success Message -->
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session("success") }}',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
@endif

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
       $('#gender').select2({ 
          theme: 'bootstrap-5',
    allowClear: true,
    placeholder: "Select Gender(s)"
});

        // Custom validation on form submit
        $('#filterForm').on('submit', function (e) {
            let phone = $('#number').val().trim();
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();

            if (phone && !/^\d{10}$/.test(phone)) {
                alert('Phone number must be exactly 10 digits.');
                e.preventDefault();
                return false;
            }

            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be greater than end date.');
                e.preventDefault();
                return false;
            }
        });
    });



    $(document).ready(function () {
            let deleteUrl = '';

            $('.deleteBtn').on('click', function () {
                let uuid = $(this).data('uuid');
                deleteUrl = '{{ url("admin/customers") }}/' + uuid;
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function () {
                let form = $('#deleteForm');
                form.attr('action', deleteUrl);
                form.submit();
            });
});
</script>
