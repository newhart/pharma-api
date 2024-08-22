<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
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
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Sale Date</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Remaining</th>
                <th>State</th>
                <th>Client Name</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->saleDate }}</td>
                    <td>{{ $sale->saleAmout }}</td>
                    <td>{{ $sale->salePayed }}</td>
                    <td>{{ $sale->amount_remaining }}</td>
                    <td>{{ $sale->stateSale }}</td>
                    <td>{{ $sale->clientName }}</td>
                    <td>{{ $sale->description }}</td>
                    <td>{{ $sale->created_at }}</td>
                    <td>{{ $sale->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
