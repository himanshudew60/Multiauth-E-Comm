@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="app-main py-4">
  
  <div class="container-fluid">

    <!-- Cards: Basic Stats -->
    <div class="row g-4 mb-4">
      @php
        $cards = [
          ['label' => 'Customers', 'count' => $customerCount, 'icon' => 'bi-people-fill', 'route' => route('admin.customers.index'), 'bg' => 'gradient-warning'],
          ['label' => 'Products', 'count' => $productCount, 'icon' => 'bi-cart-fill', 'route' => route('admin.products.index'), 'bg' => 'gradient-success'],
          ['label' => 'Categories', 'count' => $categoryCount, 'icon' => 'bi-folder-fill', 'route' => route('admin.categories.index'), 'bg' => 'gradient-danger'],
          ['label' => 'Tags', 'count' => $tagCount, 'icon' => 'bi-tags', 'route' => route('admin.tags.index'), 'bg' => 'gradient-primary'],
        ];
      @endphp

      @foreach ($cards as $card)
      <div class="col-12 col-sm-6 col-md-3">
        <a href="{{ $card['route'] }}" class="text-decoration-none">
          <div class="card shadow-sm border-0 h-100 hover-shadow transition {{ $card['bg'] }} text-white">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="icon-wrapper bg-light text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                <i class="bi {{ $card['icon'] }} fs-3"></i>
              </div>
              <div>
                <h6 class="mb-1">{{ $card['label'] }}</h6>
                <h4 class="mb-0 fw-bold">{{ $card['count'] }}</h4>
              </div>
            </div>
          </div>
        </a>
      </div>
      @endforeach
    </div>

    <!-- Cards: Price Summary -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card text-white bg-dark shadow-sm">
          <div class="card-body">
            <h6 class="card-title text-uppercase">Max Price</h6>
            <h3 class="card-text">₹{{ number_format($maxPrice, 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-secondary shadow-sm">
          <div class="card-body">
            <h6 class="card-title text-uppercase">Min Price</h6>
            <h3 class="card-text">₹{{ number_format($minPrice, 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-info shadow-sm">
          <div class="card-body">
            <h6 class="card-title text-uppercase">Avg Price</h6>
            <h3 class="card-text">₹{{ number_format($avgPrice, 2) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts: Analytics -->
    <div class="row g-4 mb-5">
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Products per Category</h6>
            <i class="bi bi-bar-chart-fill fs-5"></i>
          </div>
          <div class="card-body">
            <canvas id="productsCategoryChart" height="230"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Customer Gender Distribution</h6>
            <i class="bi bi-pie-chart-fill fs-5"></i>
          </div>
          <div class="card-body">
            <canvas id="genderChart" height="230"></canvas>
            <div class="mt-2 small text-muted">
              <strong>1</strong>: Male | <strong>2</strong>: Female | <strong>3</strong>: Other
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tables: Latest Entries -->
    <div class="row g-4">
      @php
        $cards = [
          ['title' => 'Latest Customers', 'bg' => 'bg-warning text-dark', 'route' => route('admin.customers.index'), 'headers' => ['#', 'Name', 'Email'], 'data' => $latestCustomers, 'cols' => 3, 'fields' => fn($item, $index) => [$index + 1, $item->name, $item->email]],
          ['title' => 'Latest Products', 'bg' => 'bg-success text-white', 'route' => route('admin.products.index'), 'headers' => ['#', 'Name', 'Price'], 'data' => $latestProducts, 'cols' => 3, 'fields' => fn($item, $index) => [$index + 1, $item->name, '₹' . number_format($item->price, 2)]],
          ['title' => 'Latest Categories', 'bg' => 'bg-danger text-white', 'route' => route('admin.categories.index'), 'headers' => ['#', 'Name'], 'data' => $latestCategories, 'cols' => 2, 'fields' => fn($item, $index) => [$index + 1, $item->name]],
          ['title' => 'Latest Tags', 'bg' => 'bg-primary text-white', 'route' => route('admin.tags.index'), 'headers' => ['#', 'Name'], 'data' => $latestTags, 'cols' => 2, 'fields' => fn($item, $index) => [$index + 1, $item->name]],
        ];
      @endphp

      @foreach ($cards as $card)
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header {{ $card['bg'] }} d-flex align-items-center">
            <h6 class="mb-0">{{ $card['title'] }}</h6>
            <a href="{{ $card['route'] }}" class="btn btn-sm border ms-auto">View All</a>
          </div>
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  @foreach ($card['headers'] as $header)
                  <th>{{ $header }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @forelse ($card['data'] as $index => $item)
                <tr>
                  @foreach ($card['fields']($item, $index) as $field)
                  <td>{{ $field }}</td>
                  @endforeach
                </tr>
                @empty
                <tr>
                  <td colspan="{{ $card['cols'] }}" class="text-muted text-center">No {{ strtolower(str_replace('Latest ', '', $card['title'])) }} found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endforeach
    </div>

  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Bar Chart for Products per Category
  const barCtx = document.getElementById('productsCategoryChart').getContext('2d');
  const gradient = barCtx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, 'rgba(75,192,192,0.8)');
  gradient.addColorStop(1, 'rgba(153,102,255,0.6)');

  new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: @json($productsPerCategory->pluck('category.name')),
      datasets: [{
        label: 'Products',
        data: @json($productsPerCategory->pluck('total')),
        backgroundColor: gradient,
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // Doughnut Chart for Gender
  new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
      labels: ['Male', 'Female', 'Other'],
      datasets: [{
        data: @json($genderStats->pluck('total')),
        backgroundColor: ['#3498db', '#e91e63', '#9b59b6'],
        hoverOffset: 10
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
});
</script>

<style>
  .hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  .transition {
    transition: all 0.3s ease-in-out;
  }
  .gradient-warning {
    background: linear-gradient(to right, #f6c23e, #f0ad4e);
  }
  .gradient-success {
    background: linear-gradient(to right, #1cc88a, #28a745);
  }
  .gradient-danger {
    background: linear-gradient(to right, #e74a3b, #c82333);
  }
  .gradient-primary {
    background: linear-gradient(to right, #4e73df, #007bff);
  }
</style>
@endsection
