<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS Libraries -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #f1f5f9, #e0f2fe);
            font-family: 'Segoe UI', sans-serif;
        }

        h2, h4 {
            font-weight: 700;
            color: #2c3e50;
        }

        .table {
            background-color: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .table th {
            background-color: #0077ff;
            color: #fff;
            text-align: center;
        }

        .table td, .table th {
            vertical-align: middle !important;
            text-align: center;
        }

        .btn-outline-secondary,
        .btn-outline-secondary:hover {
            border-radius: 50%;
            width: 34px;
            height: 34px;
            padding: 0;
            font-weight: bold;
        }

        .btn-danger {
            border-radius: 50%;
            padding: 6px 10px;
        }

        .btn-secondary {
            border-radius: 25px;
            padding: 8px 20px;
        }

        .btn-success {
            background-color: #4c00ff;
            color: white;
            border-radius: 25px;
            padding: 6px 14px;
            font-weight: bold;
            border: none;
        }

        .btn-success:hover {
            background-color: #3a00cc;
        }

        .btn-primary {
            background-color: #00b894;
            border: none;
            border-radius: 25px;
            padding: 6px 14px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #019874;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }

        .total-label,
        .total-value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .total-value {
            color: #0d6efd;
        }

        .empty-cart {
            text-align: center;
            color: #555;
            margin-top: 50px;
            font-size: 1.2rem;
        }
    </style>
      <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    @include('partials.navbar')

    <div class="container py-5">
        <div class="d-flex mb-4 justify-content-between align-items-center flex-wrap">
            <h2>🛒 Your Shopping Cart</h2>

            <form action="{{ route('user.cart.applyCoupon') }}" method="post" class="d-flex gap-2">
                @csrf
                <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code" required>
                <button type="submit" class="btn btn-primary">Apply</button>
            </form>

            <a href="{{ route('user.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                @if(session('coupon'))
                    <div class="mt-1">Coupon Applied: <strong>{{ session('coupon') }}</strong></div>
                @endif
            </div>
        @endif

        @if (count($cart) > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Price (₹)</th>
                            <th>Quantity</th>
                            <th>Total (₹)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach ($cart as $id => $item)
                            @php
                                $originalPrice = $item['price'];
                                $discountedPrice = $item['discounted_price'] ?? $originalPrice;
                                $total = $discountedPrice * $item['quantity'];
                                $grandTotal += $total;
                            @endphp
                            <tr>
                                <td>
                                    @if ($item['photo'])
                                        <img src="{{ asset('storage/' . $item['photo']) }}" class="product-image" alt="Product Image">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $item['name'] }}</td>
                                <td>
                                    @if(isset($item['discounted_price']))
                                        <span class="text-danger fw-semibold">₹{{ number_format($item['discounted_price'], 2) }}</span>
                                        <br>
                                        <span class="text-muted text-decoration-line-through small">₹{{ number_format($item['price'], 2) }}</span>
                                    @else
                                        ₹{{ number_format($item['price'], 2) }}
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <form action="{{ route('user.cart.update', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="decrease">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">−</button>
                                        </form>

                                        <span class="fw-semibold">{{ $item['quantity'] }}</span>

                                        <form action="{{ route('user.cart.update', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="increase">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td>₹{{ number_format($total, 2) }}</td>
                                <td>
                                    <form action="{{ route('user.cart.remove', $id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash-fill text-white"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="text-end total-label">Grand Total</td>
                            <td colspan="2" class="total-value">₹{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form action="{{ route('user.buyAll') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-success">Buy All</button>
            </form>
        @else
            <p class="empty-cart">🛍️ Your cart is empty. <a href="{{ route('user.dashboard') }}">Go shopping</a>!</p>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
