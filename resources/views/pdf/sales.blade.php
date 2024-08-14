<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Impression facture</title>
    <style>
    .w-full {
    width: 100%;
    }
    .w-half {
        width: 50%;
    }
    .margin-top {
    margin-top: 1.25rem;
    }

    table {
    width: 100%;
    border-spacing: 0;
    }
    table.products {
        font-size: 0.875rem;
    }
    table.products tr {
        background-color: rgb(96 165 250);
    }
    table.products th {
        color: #ffffff;
        padding: 0.5rem;
    }
    table tr.items {
        background-color: rgb(241 245 249);
    }
    table tr.items td {
        padding: 0.5rem;
    }
    h4 {
        margin: 0;
    }
    .total {
        text-align: right;
        margin-top: 1rem;
        font-size: 0.875rem;
    }
    .footer {
        font-size: 0.875rem;
        padding: 1rem;
        background-color: rgb(241 245 249);
    }
    </style>
</head>
<body>
    <table class="w-full">
        <tr>
            <td class="w-half">
                {{-- @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" />
                @endif --}}
                logo
            </td>
            <td class="w-half">
                N° Facture: VNT-01
            </td>
        </tr>
    </table>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>{{ $setting->nomEntreprise }}:</h4></div>
                    <div>{{ $setting->nif }}</div>
                    <div>{{ $setting->mail }}</div>
                    <div>{{ $setting->tel }}</div>
                </td>
                <td class="w-half"> 
                    <div>{{ $currentDateTime }}</div>
                    
                </td>
            </tr>
        </table>
    </div>

    <div class="margin-top">
        <table class="products">
            <tr>
                <th>Désignation</th>
                @if ($sales->contains(function($sale) {
                    return $sale['cartProducts']->contains(function($product) {
                        return $product['quantityBoite'] > 0;
                    });
                }))
                    <th>Prix Boîte</th>
                    <th>Quantité Boîte</th>
                @endif
                @if ($sales->contains(function($sale) {
                    return $sale['cartProducts']->contains(function($product) {
                        return $product['quantityGellule'] > 0;
                    });
                }))
                    <th>Prix Gellule</th>
                    <th>Quantité Gellule</th>
                @endif
                @if ($sales->contains(function($sale) {
                    return $sale['cartProducts']->contains(function($product) {
                        return $product['quantityPlaquette'] > 0;
                    });
                }))
                    <th>Prix Plaquette</th>
                    <th>Quantité Plaquette</th>
                @endif
            </tr>
            @foreach ($sales as $sale)
                @foreach ($sale['cartProducts'] as $product)
                    <tr class="items">
                        <td>{{ $product['name'] }}</td>
                        <td>
                            @if ($product['quantityBoite'] > 0)
                                {{ $product['priceBoite'] }}
                            @elseif ($product['quantityGellule'] > 0)
                                {{ $product['priceGellule'] }}
                            @elseif ($product['quantityPlaquette'] > 0)
                                {{ $product['pricePlaquette'] }}
                            @else
                                N/A
                            @endif
                        </td>
                    
                        <td>
                            @if ($product['quantityBoite'] > 0)
                                Boîte: {{ $product['quantityBoite'] }}<br>
                            @endif
                            @if ($product['quantityGellule'] > 0 && $product['quantityBoite'] == 0)
                                Gellule: {{ $product['quantityGellule'] }}<br>
                            @endif
                            @if ($product['quantityPlaquette'] > 0 && $product['quantityBoite'] == 0 && $product['quantityGellule'] == 0)
                                Plaquette: {{ $product['quantityPlaquette'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>
 
    <div class="total">
        Total: {{ $grandTotal }} Ar
    </div>
 
    <div class="footer margin-top">
        <div>Merci !</div>
        <div>&copy; {{ $setting->nomEntreprise }}</div>
    </div>

    {{-- @foreach ($sales as $sale)
        <h2>Sale ID: {{ $sale['cartProducts'][0]['sale_id'] }}</h2>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Quantity (Boite)</th>
                    <th>Quantity (Gellule)</th>
                    <th>Quantity (Plaquette)</th>
                    <th>Price (Boite)</th>
                    <th>Price (Gellule)</th>
                    <th>Price (Plaquette)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale['cartProducts'] as $product)
                    <tr>
                        <td>{{ $product['id'] }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['montant'] }}</td>
                        <td>{{ $product['quantityBoite'] }}</td>
                        <td>{{ $product['quantityGellule'] }}</td>
                        <td>{{ $product['quantityPlaquette'] }}</td>
                        <td>{{ $product['priceBoite'] }}</td>
                        <td>{{ $product['priceGellule'] }}</td>
                        <td>{{ $product['pricePlaquette'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p>Total: {{ $sale['total'] }}</p>
        <p>Remise: {{ $sale['remise'] }}</p>
        <p>Total Amount: {{ $sale['totalAmount'] }}</p>
        <hr>
    @endforeach --}}

    {{-- <h2>Grand Total: {{ $grandTotal }}</h2> --}}
</body>
</html>
