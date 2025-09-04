@extends('layouts.app')

@section('content')
    <title>{{ $pageTitle }}</title>

    <div class="container mt-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="mb-0">Coupon List</h2>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Create
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Start Date</th>
                    <th>Expire Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $index => $coupon)
                    <tr>
                        <td>{{ $coupons->firstItem() + $index }}</td>
                        <td>{{ $coupon->code }}</td>
                        <td>{{ ucfirst($coupon->type) }}</td>
                        <td>{{ \Carbon\Carbon::parse($coupon->start_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($coupon->expiry_date)->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-warning me-2">Edit</a>
                            <button class="btn btn-danger btn-sm deleteBtn" data-id="{{ $coupon->id }}">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No coupons found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center">
            <div class="mb-3">
                <strong>Showing {{ $coupons->firstItem() }} - {{ $coupons->lastItem() }} of {{ $coupons->total() }} Coupons</strong>
            </div>
            <nav>
                {{ $coupons->withQueryString()->links('pagination::bootstrap-4') }}
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
                    Are you sure you want to delete this coupon? This action cannot be undone.
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
                let id = $(this).data('id');
                deleteUrl = '{{ url("admin/coupons") }}/' + id;
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function () {
                $('#deleteForm').attr('action', deleteUrl).submit();
            });
        });
    </script>
@endsection
