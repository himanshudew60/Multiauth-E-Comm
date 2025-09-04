@extends('layouts.app')

@section('content')
    <title>{{ $pageTitle }}</title>



    <div class="container mt-2">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="mb-0">Tag List</h2>

            <div class="d-flex flex-wrap gap-2 align-items-center">

                <!-- Create Button -->
                <a href="{{ route('admin.tags.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Create
                </a>

                <!-- Export Buttons -->

                <form action="{{ route('admin.tags.export.csv') }}" method="POST">
                    @csrf
                    <input type="hidden" name="name" value="{{ request('name') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">

                    <button type="submit" class="btn btn-outline-info">
                        <i class="fa-solid fa-file-csv me-1"></i> Excel
                    </button>
                </form>

                <form action="{{ route('admin.tags.pdf') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="name" value="{{ request('name') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">

                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </button>
                </form>

                <!-- Import CSV Form -->
                <form action="{{ route('admin.tags.import') }}" method="POST" enctype="multipart/form-data"
                    class="d-flex flex-wrap gap-2 align-items-center">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="csv_file" class="form-control" required>
                        <button class="btn btn-primary " type="submit">
                            <i class="bi bi-upload me-1"></i> Import
                        </button>
                        <a href="{{ asset("sample_csv/tags_20250514_061011.csv") }}" download class="btn btn-secondary "><i
                                class="bi bi-download me-1"></i> Sample</a>
                    </div>

                </form>

            </div>
        </div>
    </div>








    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <form method="POST" action="{{ route('admin.tags.search') }}" class="row g-3 align-items-end">
            @csrf

            <!-- Tag Name -->
            <div class="col-md-4 col-lg-3">
                <label for="name" class="form-label">Tag Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', request('name')) }}"
                    placeholder="Search by name (3+ letters)" class="form-control">
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Start Date -->
            <div class="col-md-4 col-lg-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="form-control">
                @error('start_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- End Date -->
            <div class="col-md-4 col-lg-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
                @error('end_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Search and Reset Buttons -->
            <div class="col-md-12 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>


    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Status</th>
                <th>Created At</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tags as $index => $tag)
                <tr>
                    <td>{{ $tags->firstItem() + $index }}</td>
                    <td>{{ $tag->name }}</td>
                    <td>
                        @if($tag->status == "0")
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $tag->created_at->format('d M Y') }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.tags.edit', $tag->uuid) }}" class="btn btn-sm btn-warning me-2">Edit</a>
                        <button class="btn btn-danger btn-sm deleteBtn" data-uuid="{{ $tag->uuid }}">Delete</button>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No tags found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center justify-content-center mb-4">
            <strong>Showing {{ $tags->firstItem() }} - {{ $tags->lastItem() }} of {{ $tags->total() }}
                Tags</strong>
        </div>
        <nav>
            {{ $tags->withQueryString()->links('pagination::bootstrap-4') }}
        </nav>
    </div>



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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        $(document).ready(function () {
            let deleteUrl = '';

            $('.deleteBtn').on('click', function () {
                let uuid = $(this).data('uuid');
                deleteUrl = '{{ url("admin/tags") }}/' + uuid;
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