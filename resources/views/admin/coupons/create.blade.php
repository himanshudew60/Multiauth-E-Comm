@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create Coupon</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following errors:
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="code" class="form-label">Coupon Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" required>
            </div>

            <div class="col-md-6">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                    <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Percent</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="value" class="form-label">Value</label>
                <input type="number" step="0.01" name="value" id="value" class="form-control" value="{{ old('value') }}" required>
            </div>

            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}" required>
            </div>

            <div class="col-md-4">
                <label for="expiry_date" class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date') }}" required>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success">Create Coupon</button>
        </div>
    </form>
</div>
@endsection
