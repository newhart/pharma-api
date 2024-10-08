<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Product;
use App\Models\Setting;
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
    
        $products = Product::all();

        $totalProducts = $products->count();

        // heur madagascar
        $now = Carbon::now('Indian/Antananarivo');

        // date actuelle
        $dateToday = Carbon::now()->format('d/m/Y');

        // Récupérer le logo en base64
        $setting = Setting::first();
        $logoBase64 = null;
        $nomEntreprise = $setting ? $setting->nomEntreprise : 'Nom Entreprise';
        $nif = $setting ? $setting->nif : '';
        $stat = $setting ? $setting->stat : '';
        $mail = $setting ? $setting->mail : '';
        $tel = $setting ? $setting->tel : '';

        if ($setting && $setting->logo) {
            $logoBase64 = $this->imageToBase64($setting->logo);
        }

        // Générer le PDF avec les données récupérées
        $pdf = Pdf::loadView('pdf.product', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'dateToday' => $dateToday,
            'now' => $now,
            'logoBase64' => $logoBase64,
            'nomEntreprise' => $nomEntreprise,
            'nif' => $nif,
            'stat' => $stat,
            'mail' => $mail,
            'tel' => $tel,
        ]);

        return $pdf->download('product_list.pdf');
    }   
}
