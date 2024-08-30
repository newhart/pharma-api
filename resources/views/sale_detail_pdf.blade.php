<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détail de la Vente</title>
        
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 0;
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

            .logo-section img {
                max-width: 100px;
                height: 100px;
            }

            .container {
                position: relative;
                height: 150px;
                margin-bottom: 20px;
                font-size: 11px
            }

            .inline-block {
                position: absolute;
                top: 0;
                height: 100px; 
                box-sizing: border-box;
            }

            .inline-block:nth-child(1) {
                left: 0;
                width: 50%; 
            }

            .inline-block:nth-child(2) {
                left: 50%; 
                width: 30%;
            }

            .inline-block:nth-child(3) {
                left: 85%; 
                width: 30%;
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

        
        </style>
    </head>
<body>
    {{-- <div class="container">
        <div class="header">
            @if($logoBase64)
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
            @endif
            <h1>Détail de la Vente</h1>
            <p>{{ $nomEntreprise }}</p>
            <p>{{ $dateToday }}</p>
        </div>

        <div class="details">
            <h2>Détails de la Vente</h2>
            <table>
                <tr>
                    <th>Référence</th>
                    <td>{{ $sale['reference'] }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $sale['saleDate'] }}</td>
                </tr>
                <tr>
                    <th>Montant</th>
                    <td>{{ $sale['saleAmout'] }}</td>
                </tr>
                <tr>
                    <th>Montant Payé</th>
                    <td>{{ $sale['salePayed'] }}</td>
                </tr>
                <tr>
                    <th>Montant Restant</th>
                    <td>{{ $sale['amount_remaining'] }}</td>
                </tr>
                <tr>
                    <th>Crédit</th>
                    <td>{{ $sale['estACredit'] ? 'Oui' : 'Non' }}</td>
                </tr>
                <tr>
                    <th>Mode de Paiement</th>
                    <td>{{ $sale['playmentMode'] }}</td>
                </tr>
                <tr>
                    <th>Date de Paiement Prévue</th>
                    <td>{{ $sale['playmentDatePrevueAt'] }}</td>
                </tr>
                <tr>
                    <th>Nom du Client</th>
                    <td>{{ $sale['clientName'] }}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $sale['description'] }}</td>
                </tr>
                <tr>
                    <th>État de la Vente</th>
                    <td>{{ $sale['stateSale'] }}</td>
                </tr>
                <tr>
                    <th>Remise</th>
                    <td>{{ $sale['remise'] }}</td>
                </tr>
                <tr>
                    <th>Date de Création</th>
                    <td>{{ $sale['created_at'] }}</td>
                </tr>
                <tr>
                    <th>Date de Mise à Jour</th>
                    <td>{{ $sale['updated_at'] }}</td>
                </tr>
                <tr>
                    <th>Utilisateur</th>
                    <td>{{ $sale['user_id'] }}</td>
                </tr>
                <tr>
                    <th>Numéro de Facture</th>
                    <td>{{ $sale['invoice_number'] }}</td>
                </tr>
            </table>
        </div>

        <div class="products">
            <h2>Produits</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID du Produit</th>
                        <th>Nom</th>
                        <th>Quantité (Gélule)</th>
                        <th>Quantité (Plaquette)</th>
                        <th>Quantité (Boîte)</th>
                        <th>Prix Vente (Gélule)</th>
                        <th>Prix Vente (Plaquette)</th>
                        <th>Prix Vente (Boîte)</th>
                        <th>Montant</th>
                        <th>Utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale['products'] as $product)
                        <tr>
                            <td>{{ $product['product_id'] }}</td>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['quantityGellule'] }}</td>
                            <td>{{ $product['quantityPlaquette'] }}</td>
                            <td>{{ $product['quantityBoite'] }}</td>
                            <td>{{ $product['priceSaleGellule'] }}</td>
                            <td>{{ $product['priceSalePlaquette'] }}</td>
                            <td>{{ $product['priceSaleBoite'] }}</td>
                            <td>{{ $product['amount'] }}</td>
                            <td>{{ $product['user'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Merci pour votre achat !</p>
        </div>
    </div> --}}

    <div class="header">
        <div class="logo-section">
            <h3 class="logo">
                @if ($logoBase64)
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
                @endif

            </h3>
        </div>

        <h3>PANIER D'ACHAT DU CLIENT</h3>
        <p class="date">{{ $now->format('d/m/Y') }}</p>
    </div>
    <div class="description">
        <div class="info1">
            <div>{{ $nomEntreprise }}</div>
            <div>{{ $nif }}</div>
            <div>{{ $stat }}</div>
            <div>{{ $mail }}</div>
            <div>{{ $tel }}</div>
        </div>     
    </div>
    <div class="container">
        <div class="inline-block">
            <p>Nom Client : {{ $sale['clientName'] }}</p>
            <p>Information supplémentaire : {{ $sale['description'] }}</p>
        </div>
    
        <div class="inline-block">
            <p>Date de la vente : {{ $sale['saleDate'] }}</p>
            <p>Date de paiement prévue : {{ $sale['playmentDatePrevueAt'] }}</p>
        </div>
    
        <div class="inline-block">
            <p>Montant payer : {{ $sale['salePayed'] }} Ar</p>
            <p>Reste à payer : {{ $sale['amount_remaining'] }} Ar</p>
        </div>
    </div>

    <div class="margin-top">
        <table class="x">
            <tr>
                <th>Référence</th>
                <th>Désignation</th>
                <th>Q/té vendu (gellule)</th>
                <th>Q/té vendu (plaquette)</th>
                <th>Q/té vendu (boite)</th>
                <th>P/ de vente (gellule)</th>
                <th>P/ de vente (plaquette)</th>
                <th>P/ de vente (boite)</th>
                <th>Remise</th>
                <th>M/ de la vente</th>
            </tr>
            <tr class="items">
               
            </tr>
        </table>
    </div>
    
    </body>
</html>
