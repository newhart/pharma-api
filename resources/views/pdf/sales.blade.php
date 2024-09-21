<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Impression facture</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box; 
          }
            body {
                font-size: 0.875rem;
                font-family: sans-serif;
                margin: 10px
            }
            .w-full {
            width: 40%;
            }
            .w-table {
                width: 60%;
            }
            .w-td {
            width: 20%;
            }
            .w-half {
                width: 40%;
            }
            .margin-top {
            margin-top: 1.25rem;
            }
            .margin-top-small {
            margin-top: 1rem;
            }

            table {
            width: 100%;
            border-spacing: 0;
            }
            .rouge{
                background-color: red
            }
            .vert{
                background-color: green
            }

            table.products tr {
                /* background-color: rgb(96 165 250); */
                /* color: #ffffff; */
                padding: 0.5rem;
                font-weight: bold;
            }
            
            table tr.items td {
                padding: 0.5rem;
            }

            .heurs {
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
                /* background-color: rgb(241 245 249); */
            }

        
            .container {
                width: 50%;
                overflow: hidden;
                margin-left: 40px;
            }
            .container-paragraphe {
                width: 50%;
                overflow: hidden;
                margin-left: 150px;
            }

            .container-paragraphe p {
                display: inline;
                margin: 0;
            }
            .container p {
                display: inline;
                margin: 0;
            }
            .price, .ariary, .multiply, .quantity {
                display: inline-block;
                margin: 0;
            }
            .ariary {
                margin-right: 20px; 
            }
            .quantity {
                margin-right: 20px; 
            }
            .multiply {
                margin-right: 20px; 
            }

            .total{
                display: inline-block;
                margin: 0;
                margin-right: 20px;
            }


            .separator {
                border: 0;
                border-top: 2px solid black;
                margin: 10px 0; 
                width: 42%
            }

            .fw-bold{
                font-weight: bold;
                font-size: 1rem;
            }
            
            .text-color {
                color: #6c757d
            }
            .logo-section img {
                max-width: 100px;
                height: auto;
                display: block;
                margin: 0 auto;
            
            }

            .content-image {
                text-align: center;
            }
   
    </style>
</head>
<body>
    <div>

        <div class="w-half content-image">

            <div class="logo-section">
                @if($logoBase64)
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" />
            @endif
            <p class="heurs text-color">{{ $currentDateTime }}</p>
            </div>
  
        </div>

        <div class="margin-top">
            <table class="w-full">
                @if (count($sales) > 0)
                    <tr>
                        <td class="w-half">
                            Facture N° : {{ $sales[0]['invoice_number'] ?? 'Non disponible' }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="w-half">
                            Facture N° : Non disponible
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        <table class="w-full products margin-top">
            @foreach ($sales as $sale)
                @foreach ($sale['cartProducts'] as $product)
                    <tr>
                        <td class="w-half">
                            {{ $product['name'] }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </table>

        <table class=" products margin-top">
            @foreach ($sales as $sale)
                @foreach ($sale['cartProducts'] as $product)
                <div class="container margin-top-small">
                    <p>
                        <span class="price">
                            @if ($product['quantityBoite'] > 0)
                                {{ $product['priceBoite'] }}
                                <?php $total = $product['priceBoite'] * $product['quantityBoite']; ?>
                            @elseif ($product['quantityGellule'] > 0)
                                {{ $product['priceGellule'] }}
                                <?php $total = $product['priceGellule'] * $product['quantityGellule']; ?>
                            @elseif ($product['quantityPlaquette'] > 0)
                                {{ $product['pricePlaquette'] }}
                                <?php $total = $product['pricePlaquette'] * $product['quantityPlaquette']; ?>
                            @else
                                N/A
                                <?php $total = 0; ?>
                            @endif
                        </span>
                        <span class="ariary">Ar</span>
                        <span class="multiply">x</span>
                        <span class="quantity">
                            @if ($product['quantityBoite'] > 0)
                                {{ $product['quantityBoite'] }}
                            @elseif ($product['quantityGellule'] > 0 && $product['quantityBoite'] == 0)
                                {{ $product['quantityGellule'] }}
                            @elseif ($product['quantityPlaquette'] > 0 && $product['quantityBoite'] == 0 && $product['quantityGellule'] == 0)
                                {{ $product['quantityPlaquette'] }}
                            @endif
                        </span>
                    </p>
                    <p>
                        <span class="quantity">=</span>
                        <span class="price">{{ $total }}</span>   
                        <span class="ariary">Ar</span>
                    </p>
                </div>
                @endforeach
            @endforeach
        </table>
        
        <hr class="separator">

        <div class="margin-top">
           
            <p class="container-paragraphe">
                <span class="quantity">Total :</span> 
                <span class="price fw-bold">{{ $grandTotal }}</span> 
                <span class="ariary fw-bold">Ar</span>
            </p>
             
        </div>
        
    </div>

</body>
</html>
