@extends('layouts.app')

@section('title', 'Product List')

@section('content')
    <title>{{ $pageTitle }}</title>
<div class="container mt-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h2 class="mb-0">Product List</h2>

        <div class="d-flex flex-wrap gap-2 align-items-center">

            <!-- Create Button -->
            <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>

            <!-- Export Buttons -->
           


              <form action="{{ route('admin.products.export.csv') }}" method="POST">
    @csrf
    <input type="hidden" name="name" value="{{ request('name') }}">
    <input type="hidden" name="price_min" value="{{ request('price_min') }}">
    <input type="hidden" name="price_max" value="{{ request('price_max') }}">

    @if(request()->has('category_id'))
        @foreach((array)request('category_id') as $c)
            <input type="hidden" name="category_id[]" value="{{ $c }}">
        @endforeach
    @endif

    <input type="hidden" name="created_at_start" value="{{ request('created_at_start') }}">
    <input type="hidden" name="created_at_end" value="{{ request('created_at_end') }}">

    <button type="submit" class="btn btn-outline-info">
                       <i class="fa-solid fa-file-csv me-1"></i> Excel
                    </button>

</form>

                <form action="{{ route('admin.products.pdf') }}" method="POST" target="_blank">
    @csrf
    <input type="hidden" name="name" value="{{ request('name') }}">
    <input type="hidden" name="price_min" value="{{ request('price_min') }}">
    <input type="hidden" name="price_max" value="{{ request('price_max') }}">

    @if(request()->has('category_id'))
        @foreach((array)request('category_id') as $c)
            <input type="hidden" name="category_id[]" value="{{ $c }}">
        @endforeach
    @endif

    <input type="hidden" name="created_at_start" value="{{ request('created_at_start') }}">
    <input type="hidden" name="created_at_end" value="{{ request('created_at_end') }}">

    <button type="submit" class="btn btn-outline-danger">
        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
    </button>
</form>



            <!-- Import CSV Form with Button Inside Input Box -->
            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group input-group">
                    <input type="file" name="csv_file" class="form-control" required>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                    <a href="{{ asset("sample_csv/products_20250514_061044.csv") }}" class="btn btn-secondary "><i class="bi bi-download me-1"></i> Sample</a>
                </div>
            </form>

        </div>
    </div>
</div>


        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter Form -->
        <form method="POST" action="{{ route('admin.products.search') }}" class="row g-3 align-items-end mb-4">
            @csrf

            <!-- Product Name -->
            <div class="col-md-4 col-lg-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" id="name" name="name" value="{{ request('name') }}" class="form-control"
                    placeholder="Enter name">
            </div>

            <!-- Minimum Price -->
            <div class="col-md-4 col-lg-3">
                <label for="price_min" class="form-label">Minimum Price</label>
                <input type="number" id="price_min" name="price_min" value="{{ request('price_min') }}" class="form-control"
                    placeholder="Min price">
            </div>

            <!-- Maximum Price -->
            <div class="col-md-4 col-lg-3">
                <label for="price_max" class="form-label">Maximum Price</label>
                <input type="number" id="price_max" name="price_max" value="{{ request('price_max') }}" class="form-control"
                    placeholder="Max price">
            </div>

            <!-- Start Date -->
            <div class="col-md-4 col-lg-3">
                <label for="created_at_start" class="form-label">Start Date</label>
                <input type="date" id="created_at_start" name="created_at_start" value="{{ request('created_at_start') }}"
                    class="form-control">
            </div>

            <!-- End Date -->
            <div class="col-md-4 col-lg-3">
                <label for="created_at_end" class="form-label">End Date</label>
                <input type="date" id="created_at_end" name="created_at_end" value="{{ request('created_at_end') }}"
                    class="form-control">
            </div>

            <!-- Category -->
           <div class="col-md-4 col-lg-3">
    <label for="category_id" class="form-label">Category</label>
    <select id="category_id" name="category_id[]" class="form-select w-100" multiple>
        <option value="">-- All Categories --</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ collect(request('category_id'))->contains($category->id) ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>


            <!-- Action Buttons -->
            <div class="col-12 col-lg-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                </a>
            </div>
        </form>


        <!-- Summary -->


        <!-- Product Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="text-right">Price</th>
                        <th>Category</th>
                        <th>Tags</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $index => $product)
                        <tr>
                            <td>{{ $products->firstItem() + $index }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="text-right">₹{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>
                                @foreach ($product->tags as $tag)
                                    <span class="badge bg-info text-dark">{{ $tag->name }}</span>
                                @endforeach
                            </td>
                            <td>
                        @if($product->status == "0")
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                            <td>{{ $product->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.products.show', $product->uuid) }}"
                                    class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('admin.products.edit', $product->uuid) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm deleteBtn" data-uuid="{{ $product->uuid }}">Delete</button>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>



        <div class="d-flex justify-content-between align-items-center">
            <div class="col-md-6 text-start">
                <strong>
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                </strong>
            </div>
            <nav class="d-flex justify-content-end">
                {{ $products->withQueryString()->links('pagination::bootstrap-4') }}
            </nav>
        </div>

        <!-- Pagination -->

    </div>


    <!-- Delete Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
               $('#category_id').select2({ 
        theme: 'bootstrap-5',
        allowClear: true,
        placeholder: "Select Categories"
    });
            let deleteUrl = '';

            $('.deleteBtn').on('click', function () {
                let uuid = $(this).data('uuid');
                deleteUrl = '{{ url("admin/products") }}/' + uuid;
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function () {
                let form = $('#deleteForm');
                form.attr('action', deleteUrl);
                form.submit();
            });
        });
    </script>

@endsection

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session("success") }}',
                confirmButtonColor: '#3085d6'
            })
        });
    </script>
@endif