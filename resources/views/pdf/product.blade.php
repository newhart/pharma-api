<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste produit</title>
   
    <style>
      body {
        font-family: sans-serif;
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
        height: 50px;
      }

      .header img {
        position: absolute;
        left: 0;
        top: 0;
        max-width: 110px;
        height: auto;
      }

      .header h3:nth-of-type(1) {
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
        width: 300px; 
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
          @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 110px;">
          @else
            <h3>Pharacie Mahavatse</h3>
          @endif

        <h3>Article en stock</h3>
        <p class="date">{{ now()->format('d/m/Y H:i') }}</p>
   
      </div>
      <div class="content">
        <table>
          <thead>
            <tr>
              <th class="designation">Désignation</th>  
              <th>Quantité gellule</th>
              <th>Quantité plaquette</th>
              <th>Quantité boite</th>
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
