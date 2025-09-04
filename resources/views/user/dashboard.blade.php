<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Products</title>

    <!-- Font Awesome -->
    <!-- Bootstrap CSS -->
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <!-- Bootstrap Icons -->

    <style>
        body {
            background: linear-gradient(to right, #f1f5f9, #e0f2fe);
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            background: linear-gradient(to right, #1e3a8a, #3b82f6);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem;
            color: #fff !important;
        }

        .navbar .nav-link,
        .dropdown-toggle {
            color: #f1f5f9 !important;
        }

        .product-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
        }

        .tag {
            background: #e0f2fe;
            color: #0369a1;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-cart {
            background: linear-gradient(to right, #6366f1, #4f46e5);
            color: #fff;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-cart:hover {
            background: linear-gradient(to right, #4f46e5, #4338ca);
        }

        .form-label {
            font-weight: 600;
            color: #1e3a8a;
        }

        h6.fw-bold {
            font-size: 1.1rem;
            color: #111827;
        }

        .text-muted {
            font-size: 0.9rem;
        }

        h2 {
            color: #1e3a8a;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
        }



        .tag-badges {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .product-card-wrapper {
            height: 100%;
        }

        .product-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 200px;
            /* Set a fixed min height for uniformity */
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .product-card h6,
        .product-card p {
            margin-bottom: 0.5rem;
            flex-grow: 0;
        }

        .product-card .btn-cart {
            margin-top: auto;
        }

        .btn-outline-primary,
        .btn-success,
        .btn-danger {
            border-radius: 30px;
            font-weight: 600;
        }

        .dropdown-menu {
            border-radius: 12px;
        }
    </style>
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    @include('partials.navbar')
    <!-- Main Container -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">🛒 Explore Our Products</h2>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterSection" aria-expanded="false" aria-controls="filterSection">
                <i class="bi bi-funnel-fill"></i> Filters
            </button>
        </div>

        <!-- Filter Section -->
        <div class="collapse mb-4" id="filterSection">
            <form method="get" action="{{ route('user.dashboard') }}"
                class="row g-3 bg-white rounded shadow-sm p-4 border border-2">
                <div class="col-md-4">
                    <label class="form-label">🔍 Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search products..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">📂 Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">🏷️ Tag</label>
                    <select name="tag_id" class="form-select">
                        <option value="">All Tags</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-search"></i> Apply
                    </button>
                    <a href="{{ route('user.dashboard') }}" class="btn btn-danger px-4">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Product Listing -->
        <div class="row g-4">
            @forelse ($products as $pro)
                @php
                    $photos = json_decode($pro->photo, true);
                    $qty = $pro->quantity->qty ?? null;
                @endphp
                <div class="col-lg-3 col-md-6">
                    <div class="product-card-wrapper position-relative">



                        <div class="product-card">
                            <div class="tag-badges">
                                @foreach ($pro->tags as $tag)
                                    <span class="tag">{{ $tag->name }}</span>
                                @endforeach
                            </div>

                            @if (!empty($photos) && is_array($photos) && count($photos) > 0)
                                <div id="carousel{{ $pro->id }}" class="carousel slide mb-3" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @foreach ($photos as $index => $img)
                                            @if (!empty($img) && file_exists(public_path('storage/' . $img)))
                                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                    <img src="{{ asset('storage/' . $img) }}" class="product-image" alt="Product Image">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @if (count($photos) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $pro->id }}"
                                            data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $pro->id }}"
                                            data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <img src="https://via.placeholder.com/300x200.png?text=No+Image" alt="No Image"
                                    class="product-image mb-3">
                            @endif

                            <h6 class="fw-bold">{{ $pro->name }}</h6>
                            <p class="text-muted mb-2">Price: ₹{{ number_format($pro->price, 2) }}</p>
                            @if ($qty !== null)
                                @if ($qty <= 0)
                                    <p class="text-danger">
                                        <i class="fa-solid fa-circle-xmark fa-beat-fade"></i> Out of stock
                                    </p>
                                @elseif ($qty < 20)
                                    <p class="text-warning">
                                        <i class="fa-solid fa-bolt fa-bounce"></i> Stock is limited
                                    </p>
                                @endif
                            @endif

                            <a href="{{ route('user.addToCart', $pro->id) }}" class="btn btn-cart mt-auto w-100">
                                <i class="bi bi-plus-circle"></i> Add to Cart
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No products available right now.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>