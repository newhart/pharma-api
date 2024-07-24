<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Product;
use Carbon\Carbon;

class PdfController extends Controller
{
    public function generateProductList(Request $request)
    {
        // Récupérer toutes les données des produits
        $products = Product::all();

         // Calculer le nombre total de produits
        $totalProducts = $products->count();
        // date actulles
        $dateToday = Carbon::now()->format('d/m/Y');


        // Générer le PDF avec les données récupérées
        $pdf = Pdf::loadView('pdf.product', ['products' => $products, 'totalProducts' => $totalProducts,  'dateToday' => $dateToday]);

        return $pdf->download('product_list.pdf');
    }
}
