@extends('layouts.app')

@section('content')
<title>{{ $pageTitle }}</title>

<div class="container">
    <h2 class="mb-4 mt-3">Edit Coupon</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="editCouponForm" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Coupon Code</label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ $coupon->code }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" name="type" id="type" required>
                            <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percent" {{ $coupon->type === 'percent' ? 'selected' : '' }}>Percent</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="value" class="form-label">Value</label>
                        <input type="number" name="value" id="value" class="form-control" step="0.01" value="{{ $coupon->value }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                            value="{{ \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
                            value="{{ \Carbon\Carbon::parse($coupon->expiry_date)->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Cancel</a>

                    <button type="submit" class="btn btn-success">Update Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('#editCouponForm').submit(function(e) {
        e.preventDefault();

        const form = $(this);
        const formData = new FormData(this);

        $.ajax({
            url: "{{ route('admin.coupons.update', $coupon->id) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Coupon Updated',
                        text: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = "{{ route('admin.coupons.index') }}";
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
</script>
@endsection
