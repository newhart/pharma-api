<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Product;
use App\Models\Setting; // Assurez-vous d'importer le modèle Setting
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;         

class PdfController extends Controller
{
    // Méthode pour convertir une image en base64
    private function imageToBase64($path)
    {
        $image = Storage::disk('public')->get($path);
        return 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($image);
    }

    public function generateProductList(Request $request)
    {
        // Récupérer toutes les données des produits
        $products = Product::all();

        // Calculer le nombre total de produits
        $totalProducts = $products->count();

        // heur madagascar
        $now = Carbon::now('Indian/Antananarivo');

        // date actuelle
        $dateToday = Carbon::now()->format('d/m/Y');

        // Récupérer le logo en base64
        $setting = Setting::first();
        $logoBase64 = null;

        if ($setting && $setting->logo) {
            $logoBase64 = $this->imageToBase64($setting->logo);
        }

        // Générer le PDF avec les données récupérées
        $pdf = Pdf::loadView('pdf.product', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'dateToday' => $dateToday,
            'now' => $now,
            'logoBase64' => $logoBase64 // Passer le logo en base64 à la vue
        ]);

        return $pdf->download('product_list.pdf');
    }
}
