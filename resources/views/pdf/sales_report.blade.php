<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    {{-- <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style> --}}
</head>
<body>
    <h1>Sales Report</h1>
    {{-- <p>Stay: {{ $stay }}</p> --}}
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Sale Date</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>State</th>
            </tr>
        </thead>
        <tbody>
            {{-- @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->saleDate }}</td>
                    <td>{{ $sale->saleAmount }}</td>
                    <td>{{ $sale->salePayed }}</td>
                    <td>{{ $sale->stateSale }}</td>
                </tr>
            @endforeach --}}
        </tbody>
    </table>
</body>
</html>
