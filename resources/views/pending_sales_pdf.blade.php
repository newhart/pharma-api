<!DOCTYPE html>
<html>
<head>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;  
    }
    h4 {
        margin: 0;
    }
    .w-full {
        width: 100%;
    }
    .w-half {
        width: 50%;
    }
    .margin-top {
        margin-top: 1.25rem;
    }
    .footer {
        font-size: 0.875rem;
        padding: 1rem;
        background-color: rgb(241 245 249);
    }
    table {
        width: 100%;
        border-spacing: 0;
    }
    table.products {
        font-size: 10px;
    }
    table.products tr {
        background-color: rgb(96 165 250);
    }
    table.products th {
        color: #ffffff;
        padding: 9px;
    }
    table tr.items {
        background-color: rgb(241 245 249);
    }
    table tr.items td {
        padding: 0.5rem;
    }
    .total {
        text-align: right;
        margin-top: 1rem;
        font-size: 0.875rem;
    }

    .header {
        position: relative;
        padding: 60px;
        box-sizing: border-box;
        overflow: hidden;
    }

    .logo-section {
        position: absolute;
        top: 0px;
        left: 30px;
        max-width: 150px;
        text-align: center;
    }


    .header h3 {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        margin: 0;
        top: 10px;
    }

    .header .date {
        position: absolute;
        right: 0;
        top: 0;
        margin: 10px;
    }

    .description .info2 {
        position: absolute;
        left: 40%;
        transform: translateX(-50%);
        margin: 0;
        top: 120px;
    }

    .logo-section img {
        max-width: 100px;
        height: 100px;
    }
    </style>
</head>
<body> 
    <div class="header">
        <div class="logo-section">
            <h3 class="logo">
                @if ($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
                @endif
            </h3>
        </div>

        <h3>LISTE DES VENTES EN COURS</h3>
        <p class="date">{{ $now->format('d/m/Y') }}</p>
    </div>

    <div class="description">
        <div class="info1">
            <div><h4>{{ $nomEntreprise }}</h4></div>
            <div>{{ $nif }}</div>
            <div>{{ $stat }}</div>
        </div>

        <div class="info2">
            <div><h4>{{ $mail }}</h4></div>
            <div>{{ $tel }}</div>
        </div>       
    </div>


    <div class="margin-top">
        <table class="products">
            <tr>
                <th>Référence</th>
                <th>D/ de la vente</th>
                <th>Nom du client</th>
                <th>M/ payer</th>
                <th>Reste à payer</th>
                <th>M/ de la vente</th>
                <th>D/ de paiement</th>
                <th>Information supplémentaire</th>
            </tr>
            @forelse ($sales as $sale)
                <tr class="items">
                    <td>{{ $sale['reference'] }}</td>
                    <td>{{ $sale['saleDate'] }}</td>
                    <td>{{ $sale['clientName'] }}</td>
                    <td>{{ number_format($sale['salePayed']) }}</td>
                    <td>{{ number_format($sale['amount_remaining']) }}</td>
                    <td>{{ number_format($sale['saleAmout']) }}</td>
                    <td>{{ $sale['playmentDatePrevueAt'] }}</td>
                    <td>{{ $sale['description'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Aucune vente en attente.</td>
                </tr>
            @endforelse
        </table>
    </div>
 
    <div class="total">
        Nombre de lignes :  {{ $sales->count() }}
    </div>
 
   </body>
</html>
