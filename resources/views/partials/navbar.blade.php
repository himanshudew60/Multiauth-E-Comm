<nav class="navbar navbar-expand-lg custom-navbar navbar-dark sticky-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('user.dashboard') }}">🛍 MyShop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3 position-relative">
                    <a class="nav-link" href="{{ route('user.cart') }}">
                        <i class="bi bi-cart4 me-1"></i> Cart
                        @php $cart = session('cart', []); $count = count($cart); @endphp
                        @if($count > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $count }}
                            </span>
                        @endif
                    </a>
                </li>

                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li class="dropdown-item-text fw-semibold">{{ Auth::user()->name }}</li>
                            <li class="dropdown-item-text fw-semibold">{{ Auth::user()->email }}</li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-primary" href="{{ route('user.info', ['id' => Auth::user()->id]) }}">
                                    <i class="bi bi-info-circle me-2"></i> Info
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-secondary" href="{{ route('user.orders', ['id' => Auth::user()->id]) }}">
                                    <i class="bi bi-box-seam me-2"></i> Orders
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<style>
    .custom-navbar {
        background: linear-gradient(to right, #1e3a8a, #3b82f6);
    }

    .navbar-brand {
        font-weight: bold;
        font-size: 1.8rem;
        color: #fff !important;
    }

    .navbar .nav-link,
    .navbar .dropdown-toggle {
        color: #f1f5f9 !important;
    }

    .navbar .dropdown-menu {
        border-radius: 12px;
    }

    .dropdown-item-text {
        padding: 0.5rem 1rem;
    }

    .navbar .badge {
        font-size: 0.75rem;
        padding: 0.3em 0.6em;
    }
</style>
