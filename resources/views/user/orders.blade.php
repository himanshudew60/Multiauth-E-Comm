<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>

    <!-- Bootstrap & Icons -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #f8fafc, #e0f2fe);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .order-section {
            margin-top: 40px;
        }

        .date-header {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e3a8a;
            border-left: 5px solid #2563eb;
            padding-left: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-bill {
            background-color: #2563eb;
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
        }

        .btn-bill:hover {
            background-color: #1e40af;
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.6);
        }

        .order-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px;
            padding-left: 40px;
            padding-right: 40px;
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .order-meta {
            font-size: 0.9rem;
            color: #475569;
            margin-top: 6px;
        }

        .product-image {
    width: 100%;
    max-width: 60px;      /* smaller size */
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

        @media (max-width: 576px) {
            .date-header {
                font-size: 1.3rem;
            }

            .order-card {
                padding: 15px;
            }

            .product-image {
                max-width: 80px;
            }
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
   
<div class="container py-4">
      @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    <h2 class="text-center mb-5">🛒 My Orders</h2>

    @php
        $groupedOrders = $orders->groupBy(function($order) {
            return \Carbon\Carbon::parse($order->created_at)->format('d M Y');
        });
    @endphp

    @forelse ($groupedOrders as $date => $dailyOrders)
        <div class="order-section">
            <div class="date-header">
                <span>{{ $date }}</span>
                <a href="{{ route('user.generateBillByDate', ['date' => $date]) }}" class="btn btn-bill">
                    <i class="fas fa-file-invoice"></i> Generate Bill
                </a>
            </div>

            @foreach ($dailyOrders as $order)
                <div class="order-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1"><i class="bi bi-box-seam me-2"></i> {{ $order->product->name }}</h5>
                            <p class="order-meta mb-0">Ordered at: {{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}</p>
                        </div>
                       
                        <div class="col-md-4 text-md-end text-center mt-2 mt-md-0">
@php
    $photos = json_decode($order->product->photo, true);
    $firstPhoto = $photos[0] ?? 'default.jpg'; // fallback image
@endphp

<img src="{{ asset('storage/' . $firstPhoto) }}" alt="Product Image" class="product-image">                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="alert alert-info text-center">
            You haven't placed any orders yet.
        </div>
    @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
