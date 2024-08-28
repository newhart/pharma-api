<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste produit</title>
    <style>
      body {
        font-faamily: sans-serif;
        margin: 0;
        padding: 0;
        position: relative;
        min-height: 100vh;
      }

      .parent {
        max-width: 1800px;
        padding-bottom: 60px; 
        box-sizing: border-box;
      }
      
      .header {
        position: relative;
        padding: 10px;
        border-bottom: 2px solid #ddd;
        height: 180px; /* Hauteur totale de la section header */
        /* background-color: aqua; */
        box-sizing: border-box;
        overflow: hidden; /* Assurez-vous que rien ne dépasse de la section */
      }

      .logo-section {
        position: absolute; /* Permet au logo de rester en haut à gauche */
        top: 0px;
        left: 10px;
        max-width: 150px;
        text-align: center;
      }

      .logo-section img {
        max-width: 100px;
        height: auto;
        display: block; /* Assure que l'image est traitée comme un bloc pour centrer */
        margin: 0 auto;
      }

      .logo-section .company-info {
        font-size: 10px; /* Taille de police plus petite */
      }

      .logo-section .company-info p {
        margin: 0;
        padding: 2px 0;
        white-space: nowrap; /* Évite les retours à la ligne dans les paragraphes */
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .header h3 {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        margin: 0;
        top: 0;
      }

      .header .date {
        position: absolute;
        right: 0;
        top: 0;
        margin: 0;
      }

      th.designation {
        width: 400px; 
      }

      table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 10px;
      }

      th,
      td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        font-size: 10px;
        font-weight: light;
      }

      th {
        background-color: #3699ff;
        color: #fff;
        font-weight: bold;
        font-size: 12px;
      }
      
      .footer {
        display: flex;
        justify-content: space-between;
        position: absolute;
        bottom: 0;
        left: 20px;
        right: 20px;
        padding: 5px;
        border-top: 2px solid #ddd;
        text-align: center;
        background: #fff;
        box-sizing: border-box;
        max-width: 1800px;
        margin: 0 auto;
      }

      .footer p {
        position: absolute;
        margin: 0;
      }

      .footer .left {
        left: 20px;
      }

      .footer .right {
        right: 20px; 
        text-align: right; 
      }

      .bold-text {
        font-weight: bold;
      }
    </style>
</head>
<body>
<div class="parent">
      <div class="header">
            <div class="logo-section">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <h3>Pharacie</h3>
                @endif
                <div class="company-info">
                    <p>{{ $nomEntreprise }}</p>
                    <p>{{ $nif }}</p>
                    <p>{{ $stat }}</p>
                    <p>{{ $mail }}</p>
                    <p>{{ $tel }}</p>
                </div>
            </div>
            <h3>Etat en stock</h3>
            <p class="date">{{ $now->format('d/m/Y H:i') }}</p>
      </div>
      <div class="content">
        <table>
          <thead>
            <tr>
              <th class="designation">Désignation</th>  
              <th>Q/té gellule</th>
              <th>Q/té plaquette</th>
              <th>Q/té boite</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($products as $product)
            <tr>
              <td>{{ $product->name }}</td>
              <td>{{ $product->quantityGellule }}</td>
              <td>{{ $product->quantityPlaquette }}</td>
              <td>{{ $product->quantityBoite }}</td>                
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="footer">
      <p class="left">Nombre de lignes : <span class="bold-text"> {{ $totalProducts }} Total</span></p>
      <p class="right">1/1</p>
      </div>
    </div>
</body>
</html>


