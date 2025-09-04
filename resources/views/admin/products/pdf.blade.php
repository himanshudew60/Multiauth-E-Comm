<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
    <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        button {
            margin-bottom: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()">Print / Save as PDF</button>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th class="text-end">Price</th>
                <th>Category</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
