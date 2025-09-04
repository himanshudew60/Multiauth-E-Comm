<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Invoice Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f1f5f9;
      color: #1e293b;
      margin: 0;
      padding: 10px 20px;
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .logo {
      font-weight: 700;
      font-size: 28px;
      color: #2563eb;
    }

    .date-box {
      font-size: 14px;
      color: #475569;
    }

    .date-box strong {
      color: #0f172a;
    }

    .summary {
      font-size: 15px;
      color: #475569;
      margin-bottom: 30px;
    }

    .summary strong {
      color: #0f172a;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
      color: #334155;
      margin-bottom: 30px;
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid #cbd5e1;
      text-align: left;
    }

    th {
      background-color: #e2e8f0;
      font-weight: 600;
    }

    tbody tr:hover {
      background-color: #f0f9ff;
    }

    tfoot td {
      background-color: #f8fafc;
      font-weight: 700;
      font-size: 16px;
      border-top: 2px solid #2563eb;
    }

    .highlight {
      color: #0f766e;
    }

    .print-button {
      display: inline-block;
      background-color: #16a34a;
      color: white;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 6px;
      padding: 8px 20px;
      cursor: pointer;
    }

    .print-button:hover {
      background-color: rgb(11, 104, 107);
    }

    .footer {
      font-size: 13px;
      text-align: center;
      color: #64748b;
      padding-top: 20px;
    }

    @media print {
      .print-button {
        display: none;
      }
    }

    .strike {
      text-decoration: line-through;
      color: #999;
      font-size: 14px;
    }
  </style>
</head>

<body>
  <div class="d-flex justify-content-end mb-3">
    <button class="print-button" onclick="window.print()">
      <i class="fa-solid fa-print fa-bounce"></i> Print
    </button>
  </div>

  <div class="invoice-header mt-4">
    <div class="logo">🧾 Order Invoice</div>
    <div class="date-box">
      Date: <strong>{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</strong>
    </div>
  </div>

  <div class="summary">
    <div class="row">
      <div class="col-4"><strong>Customer:</strong> {{ $orders->first()->user->name ?? 'N/A' }}</div>
      <div class="col-4"><strong>Email:</strong> {{ $orders->first()->user->email ?? 'N/A' }}</div>
      <div class="col-4"><strong>Address:</strong> {{ $profile->address ?? 'N/A' }}</div>
    </div>
    <div class="row">
      <div class="col-4"><strong>Phone:</strong> {{ $profile->phone ?? 'N/A' }}</div>
      <div class="col-4"><strong>Total Products:</strong> {{ count($orders) }}</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Product</th>
        <th>Unit Price (₹)</th>
        <th>Quantity</th>
        <th>Total (₹)</th>
      </tr>
    </thead>
    <tbody>
      @php $grandTotal = 0; @endphp
      @foreach ($orders as $index => $order)
        @php
          $unitPrice = $order->unit_price;
          $lineTotal = $order->total_price;
          $grandTotal += $lineTotal;

          $originalPrice = $order->product->price;
          $isDiscounted = $unitPrice < $originalPrice;
        @endphp
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $order->product->name }}</td>
          <td>
            @if ($isDiscounted)
              <span class="strike">₹{{ number_format($originalPrice, 2) }}</span><br>
              ₹{{ number_format($unitPrice, 2) }}
            @else
              ₹{{ number_format($unitPrice, 2) }}
            @endif
          </td>
          <td>{{ $order->quantity }}</td>
          <td>₹{{ number_format($lineTotal, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4" style="text-align: right;">Grand Total</td>
        <td class="highlight">₹{{ number_format($grandTotal, 2) }}</td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    Thank you for your purchase!<br />
    This is an auto-generated receipt. No signature required.
  </div>
</body>

</html>
