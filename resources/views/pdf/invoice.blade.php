<!DOCTYPE html>
<html>
<head>
    <title>Facture</title>

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

            .logo-section .company-info {
      
                font-size: 10px; 
                padding-left: 10px ;
            }
   
    </style>

</head>
<body>

{{-- ---------------------------------------------------------------- --}}
<div class="parent">
    <div class="header">
          <div class="logo-section">
            @if($logoBase64)
            <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
            @else
                <h3>Pharmacie</h3>
            @endif

              <div class="company-info">
                  <p>Pharmacie {{ $nomEntreprise }}</p>
              </div>
          </div>
    </div>

  

    <div class="content">
        @foreach ($sales as $sale)
        <p>Numéro de facture : {{ $sale['invoice_number'] }}</p>
        <p>Client : {{ $sale['clientName'] }}</p>
        <p>Date prévue : {{ $sale['playmentDatePrevueAt'] }}</p>
        <p>Montant total : {{ $sale['saleAmout'] }}</p>
        <p>Montant payé : {{ $sale['salePayed'] }}</p>
        <p>Jours restants : {{ $sale['saleStay'] }}</p>
        <hr>
    @endforeach

    <h2>Total général : {{ $grandTotal }}</h2>
    </div>
    <div class="footer">
    {{-- <p class="left">Nombre de lignes : <span class="bold-text"> {{ $totalProducts }} Total</span></p> --}}
    {{-- <p class="right">1/1</p> --}}
    </div>
  </div>
</body>
</html>
