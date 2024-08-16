<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Http\Services\PriceService;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\Logo;
// use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
        public function store(SaleRequest $request): JsonResponse
    {
        // Validation des quantité disponible
        foreach ($request->cartProducts as $data) {
            $product = Product::find($data['id']);
            if ($product) {
                if ($product->quantityBoite < $data['quantityBoite'] ||
                    $product->quantityPlaquette < $data['quantityPlaquette'] ||
                    $product->quantityGellule < $data['quantityGellule']) {
                    // Retourner un avertissement pour quantité insuffisante
                    return response()->json([
                        'warning' => 'Quantité en stock insuffisante !'
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => 'Produit non trouvé pour : ' . $data['id']
                ], 404);
            }
        }

        // Traitement de la vente
        try {
            $sale = Sale::create([
                'saleDate' => now(),
                'playmentDatePrevueAt' => now(),
                'playmentMode' => 'espece',
                'estACredit' => 'test',
                'saleAmout' => $request->total,
                'salePayed' => $request->total,
                'stateSale' => 'En cours',
                'user_id' => $request->user()->id,
                'saleStay' => 0.00,
                'remise' => $request->remise ?? 0.00,
            ]);
            if (($sale->saleAmount === $sale->salePayed) && $sale->saleStay === 0) {
                $sale->stateSale = 'Valider';
                $sale->save();
            }
            foreach ($request->cartProducts as $data) {
                $product = Product::findOrFail($data['id']);
                if ($product) {
                    $this->additionalQuantityProduct($data, $product);
                    DB::table('product_sale')->insert([
                        'product_id' => $product->id,
                        'sale_id' => $sale->id,
                        'amount' => $data['montant'],
                        'user' =>  $request->user()->name,
                        'quantityBoite' => $data['quantityBoite'] ?? 0,
                        'quantityGellule' => $data['quantityGellule'] ?? 0,
                        'quantityPlaquette' => $data['quantityPlaquette'] ?? 0,
                        'priceSaleBoite' => PriceService::changePriceValidation($data['priceBoite']),
                        'priceSaleGellule' => PriceService::changePriceValidation($data['priceGellule']),
                        'priceSalePlaquette' => PriceService::changePriceValidation($data['pricePlaquette']),
                    ]);
                }
            }
                
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de l’ajout de la vente.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    private function convertToPercentage(array $data, $maxValue): array
    {
        $transformFunction = function ($value) use ($maxValue) {
            return ($maxValue > 0) ? ($value / $maxValue) * 100 : 0;
        };

        return array_map($transformFunction, $data);
    }

    public function lastWeekSales()
    {
        // Définir la locale de Carbon en français
        Carbon::setLocale('fr');
        // Get the start and end dates of the current week
        $startDate = Carbon::now()->subWeek()->startOfWeek();
        $endDate = Carbon::now()->subWeek()->endOfWeek();

        // Retrieve sales data for this week

        // Retrieve sales data for one year and update the values for each day
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYOFWEEK(created_at) as day, SUM(saleAmout) as ca')
            ->groupBy('day')
            ->get();

        // Create an associative array to store the results with initial values set to zero
        $salesArray = [
            'Lun' => 0, // Monday
            'Mar' => 0, // Tuesday
            'Mer' => 0, // Wednesday
            'Jeu' => 0, // Thursday
            'Ven' => 0, // Friday
            'Sam' => 0, // Saturday
            'Dim' => 0, // Sunday
        ];

        // Initialize a variable to store the maximum result
        $maxResult = 0;

        // Iterate through the sales data and put the results in the array
        foreach ($sales as $key =>  $sale) {
            $dayNumber = intval($sale->day);
            if ($dayNumber >= 1 && $dayNumber <= 7) {
                $dayName = $this->getDayName($dayNumber);
                $salesArray[$dayName] = $sale->ca;

                // Update the maximum result if the current CA is greater
                if ($sale->ca > $maxResult) {
                    $maxResult = $sale->ca;
                }
            }
        }

        // Return the sales data as a JSON response
        return response()->json(['sales' => $this->convertToPercentage($salesArray, $maxResult), 'maxResult' => 150]);
    }

    private function getDayName($dayNumber)
    {
        $days = [
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
            7 => 'Dim',
        ];

        return $days[$dayNumber];
    }

    public function salesForOneYear()
    {
        // Get the start and end dates of the current year
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        // Retrieve sales data for one year
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('MONTH(created_at) as month, SUM(saleAmout) as ca')
            ->groupBy('month')
            ->get();

        // Create an associative array to store the results
        $salesArray = [
            Carbon::create()->month(1)->formatLocalized('%b') => 0, // Jan
            Carbon::create()->month(2)->formatLocalized('%b') => 0, // Fév
            Carbon::create()->month(3)->formatLocalized('%b') => 0, // Mar
            Carbon::create()->month(4)->formatLocalized('%b') => 0, // Avr
            Carbon::create()->month(5)->formatLocalized('%b') => 0, // Mai
            Carbon::create()->month(6)->formatLocalized('%b') => 0, // Juin
            Carbon::create()->month(7)->formatLocalized('%b') => 0, // Juil
            Carbon::create()->month(8)->formatLocalized('%b') => 0, // Aoû
            Carbon::create()->month(9)->formatLocalized('%b') => 0, // Sep
            Carbon::create()->month(10)->formatLocalized('%b') => 0, // Oct
            Carbon::create()->month(11)->formatLocalized('%b') => 0, // Nov
            Carbon::create()->month(12)->formatLocalized('%b') => 0, // Déc
        ];
        // Initialize a variable to store the maximum result
        $maxResult = 0;

        // Iterate through the sales data and put the results in the array
        foreach ($sales as $key =>  $sale) {
            $monthName = Carbon::create()->month($sale->month)->formatLocalized('%b');
            $salesArray[$monthName] = $sale->ca;
            // Update the maximum result if the current CA is greater
            if ($sale->ca > $maxResult) {
                $maxResult = $sale->ca;
            }
        }

        // Return the sales data as a JSON response
        return response()->json(['sales' => $this->convertToPercentage($salesArray, $maxResult), 'maxResult' => 150]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Sale::query();
        // filter with the user
        if ($request->get('user') && $request->get('user') !== "null") {
            $user = $request->get('user');
            $user = json_decode($user);
            $query = $query->whereHas('user', function ($query) use ($user) {
                $query->where('id', $user->id);
            });
        }
        // filter with the status
        if ($request->get('status')) {
            $query = $query->where('stateSale', $request->get('status'));
        }
        // filter with date
        if ($request->get('date')) {
            $query = $query->where('saleDate', $request->get('date'));
        }
        $sales = $query
        ->with('products')
        ->latest()
        ->paginate(10);
        $sales->map(function ($sale) {
            $sale->reference = 'PRDT-' . $sale->id;
            if ($sale->saleAmout) {
                $sale->saleAmout =  PriceService::formatPrice($sale->saleAmout);
            }
            if ($sale->salePayed) {
                $sale->salePayed =  PriceService::formatPrice($sale->salePayed);
            }
            if ($sale->saleStay) {
                $sale->saleStay =  PriceService::formatPrice($sale->saleStay);
            }
        });
        return response()->json($sales);
    }

    private function additionalQuantityProduct(array  $data, Product $product): void
    {
        // traitement if the sale  type is boite
        if ($data['quantityBoite'] != "" && $product->quantityBoite) {
            $product->quantityBoite = (int) $product->quantityBoite -   (int) $data['quantityBoite'];
            $quantityPlaqutteForBoite = (int) $data['quantityBoite'] * (int) $product->numberPlaquette;
            $product->quantityPlaquette = (int) $product->quantityPlaquette - (int) $quantityPlaqutteForBoite;
            $quantityGelluleForPlaquette = (int) $product->numberGellule * $quantityPlaqutteForBoite;
            $product->quantityGellule = (int) $product->quantityGellule -  $quantityGelluleForPlaquette;
        }
        if ($data['quantityPlaquette'] != "" && $product->quantityPlaquette) {
            $countRestPlaquetQuantity  = (int) ($product->quantityPlaquette -  (int) $data['quantityPlaquette']);
            $product->quantityPlaquette = $countRestPlaquetQuantity;
            $product->quantityBoite =  ($countRestPlaquetQuantity / $product->numberPlaquette);
            $product->quantityGellule = ($product->numberGellule * $countRestPlaquetQuantity);
        }
        if ($data['quantityGellule'] != "" && $product->quantityGellule) {
            $countRestQuantityGellule = (int) $product->quantityGellule -  (int) $data['quantityGellule'];
            $product->quantityGellule = $countRestQuantityGellule;
            $product->quantityPlaquette = $countRestQuantityGellule / $product->numberGellule;
            $product->quantityBoite = $product->quantityPlaquette / $product->numberPlaquette;
        }

        // update the product in the stock
        $product->save();
    }

    public function salesForLastMonth()
    {
        // Définir la locale de Carbon en français
        Carbon::setLocale('fr');

        // Get the start and end dates of the last month
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        // Calculate the number of weeks in the last month
        $numberOfWeeks = $startDate->diffInWeeks($endDate);

        // Create an associative array to store the sales for each week
        $weeklySales = [];
        // Initialize a variable to store the maximum sales amount
        $maxSales = 0;

        // Iterate through the weeks and calculate the sales for each week
        for ($week = 0; $week < $numberOfWeeks; $week++) {
            $startOfWeek = $startDate->copy()->addWeeks($week);
            $endOfWeek = $startDate->copy()->addWeeks($week + 1)->subDay();

            // Retrieve sales data for the current week
            $sales = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->sum('saleAmout');

            // Format the week range (e.g., '1-7 Mar' for the first week of March)
            $weekRange = $startOfWeek->format('j') . '-' . $endOfWeek->formatLocalized('%a');

            // Store the sales for the current week in the associative array
            $weeklySales[$weekRange] = $sales;

            // Update the maximum sales amount if the current week's sales are higher
            if ($sales > $maxSales) {
                $maxSales = $sales;
            }
        }

        // Return the weekly sales as a JSON response
        return response()->json(['sales' => $weeklySales, 'max' => $maxSales]);
    }   

    // Ajouter cette méthode dans votre contrôleur
private function imageToBase64($path)
{
    $imagePath = storage_path('app/public/' . $path); // Ajustez le chemin selon l'emplacement de stockage de vos images
    if (!file_exists($imagePath)) {
        return null;
    }

    $imageData = file_get_contents($imagePath);
    return base64_encode($imageData);
}


        // Validation
        public function checkValidation(Request $request)
    {
        // Obtenir la liste des IDs de ventes depuis la requête
        $saleIds = $request->input('sale_ids');
        // Valider que la liste des IDs est bien fournie
        if (!is_array($saleIds) || empty($saleIds)) {
            return response()->json(['success' => false, 'message' => 'No sales IDs provided'], 400);
        }

        // Récupérer toutes les ventes avec les IDs fournis
        $sales = Sale::whereIn('id', $saleIds)->get();

         // Initialiser un tableau pour stocker les ventes finalisées
        $finalizedSales = [];

        // Mettre à jour chaque vente
        foreach ($sales as $sale) {
            $stay = $request->input('stay'); 
            $sale->saleStay = $sale->saleStay - (float) $stay;

            // Vérifier si la vente est maintenant validée
            if ($sale->saleStay <= 0.0) {
                $sale->stateSale = 'Valider';
                $finalizedSales[] = $sale; 
            }
            $sale->save();
        }

        $formattedSales = collect($finalizedSales)->map(function ($sale) {
            // Formater les montants de la vente
            $sale->saleAmout = PriceService::formatPrice($sale->saleAmout);
            $sale->salePayed = PriceService::formatPrice($sale->salePayed);
            $sale->saleStay = PriceService::formatPrice($sale->saleStay);
            
            // Construire la structure du panier
            $cartProducts = $sale->products->map(function ($product) use ($sale) {
                return [
                    'sale_id' => $sale->id,
                    'id' => $product->id,
                    'name' => $product->name,
                    'montant' => $this->calculateProductAmount($product, $sale),
                    'quantityBoite' => $product->pivot->quantityBoite,
                    'quantityGellule' => $product->pivot->quantityGellule,
                    'quantityPlaquette' => $product->pivot->quantityPlaquette,
                    'priceBoite' => $product->pivot->priceSaleBoite,
                    'priceGellule' => $product->pivot->priceSaleGellule,
                    'pricePlaquette' => $product->pivot->priceSalePlaquette,
                ];
            });
    
            // Calculer le montant total de la vente
            $totalAmount = $cartProducts->sum('montant');
    
            return [
                'cartProducts' => $cartProducts,
                'total' => $sale->saleAmout,
                'remise' => $sale->remise,
                'totalAmount' => $totalAmount,
            ];
        });
    
        $grandTotal = $formattedSales->sum('totalAmount');


               
                 $setting = Setting::first();
                 $logoBase64 = null;
                    
                 
                // Générer le PDF avec les données récupérées
                if ($setting && $setting->logo) {
                    $logoBase64 = $this->imageToBase64($setting->logo);
                }

                $pdf = Pdf::loadView('pdf.sales', [
                    'sales' => $formattedSales,
                    'grandTotal' => $grandTotal,
                    'setting' => $setting, 
                    'currentDateTime' => now()->format('d/m/y'),
                    'logoBase64' => $logoBase64,
                ]);

                // Spécifier les en-têtes pour le téléchargement
                return $pdf->download('sales.pdf', [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="sales.pdf"',
                ]);

        // Retourner les ventes validées formatées en réponse JSON
        return response()->json([
            'sales' => $formattedSales,
            'grandTotal' => $grandTotal, 
        ]);

    }



    public function getCountInvalidSale(): JsonResponse
    {
        $sale = Sale::where('stateSale', 'Annuler')->sum('SaleAmout');
        return response()->json(['sale' => $sale]);
    }
    public function getCaNow(): JsonResponse
    {
        $totalSalesAmount = Sale::whereDate('created_at', now())
            ->sum('saleAmout');
        return response()->json($totalSalesAmount);
    }


    private function updateProductQuantities(array $data, Product $product): void
    {
        // Mise à jour des quantités disponibles
        if ($data['quantityBoite']) {
            $product->quantityBoite -= (int) $data['quantityBoite'];
            $quantityPlaquetteForBoite = (int) $data['quantityBoite'] * (int) $product->numberPlaquette;
            $product->quantityPlaquette -= (int) $quantityPlaquetteForBoite;
            $quantityGelluleForPlaquette = (int) $product->numberGellule * (int) $quantityPlaquetteForBoite;
            $product->quantityGellule -= (int) $quantityGelluleForPlaquette;
        }
        if ($data['quantityPlaquette']) {
            $countRestPlaquetteQuantity = (int) ($product->quantityPlaquette - (int) $data['quantityPlaquette']);
            $product->quantityPlaquette = $countRestPlaquetteQuantity;
            $product->quantityBoite = $countRestPlaquetteQuantity / $product->numberPlaquette;
            $product->quantityGellule = $product->numberGellule * $countRestPlaquetteQuantity;
        }
        if ($data['quantityGellule']) {
            $countRestQuantityGellule = (int) $product->quantityGellule - (int) $data['quantityGellule'];
            $product->quantityGellule = $countRestQuantityGellule;
            $product->quantityPlaquette = $countRestQuantityGellule / $product->numberGellule;
            $product->quantityBoite = $product->quantityPlaquette / $product->numberPlaquette;
        }

        // Mise à jour du produit dans le stock
        $product->save();
    }

        // methode count Sales in progress
        public function countSalesInProgress(): JsonResponse
    { 
        $count = Sale::where('stateSale', 'En cours')->count();  

         $totalSalePayed = Sale::where('stateSale', 'En cours')->sum('salePayed');  

        return response()->json([ // Retourner la réponse avec le nombre de ventes en cours
            'count' => $count,
            'totalSalePayed' => $totalSalePayed
        ]);
    }

        public function listInProgress(): JsonResponse
    {
        // Récupérer toutes les ventes avec le statut 'En cours' et inclure les produits associés
        $salesInProgress = Sale::where('stateSale', 'En cours')
            ->with('products') // Charger les produits associés
            ->get();

        // Formater les ventes et les produits
        $formattedSales = $salesInProgress->map(function ($sale) {
            // Formater les montants de la vente
            $sale->saleAmout = PriceService::formatPrice($sale->saleAmout);
            $sale->salePayed = PriceService::formatPrice($sale->salePayed);
            $sale->saleStay = PriceService::formatPrice($sale->saleStay);
            
            // Construire la structure du panier
            $cartProducts = $sale->products->map(function ($product) use ($sale) {
                return [
                    'sale_id' => $sale->id , 
                    'id' => $product->id,
                    'name' => $product->name,
                    'montant' => $this->calculateProductAmount($product, $sale),
                    'quantityBoite' => $product->pivot->quantityBoite,
                    'quantityGellule' => $product->pivot->quantityGellule,
                    'quantityPlaquette' => $product->pivot->quantityPlaquette,
                    'priceBoite' => $product->pivot->priceSaleBoite,
                    'priceGellule' => $product->pivot->priceSaleGellule,
                    'pricePlaquette' => $product->pivot->priceSalePlaquette,
                ];
            });

             // Calculer le montant total de la vente
            $totalAmount = $cartProducts->sum('montant');

                return [
                
                    'cartProducts' => $cartProducts,
                    'total' => $sale->saleAmout,
                    'remise' => $sale->remise,
                    'totalAmount' => $totalAmount,
                ];
        });

        $grandTotal = $formattedSales->sum('totalAmount');
        // Retourner les ventes en cours formatées en réponse JSON
        return response()->json([
            'sales' => $formattedSales,
            'grandTotal' => $grandTotal, 
        ]);
    }

        // supprimer panier en cours
        public function clearCurrentCart(int $saleId): JsonResponse
        {
            // Trouver la vente en cours
            $sale = Sale::find($saleId);
    
            if (!$sale) {
                return response()->json([
                    'error' => 'Vente non trouvée.'
                ], 404);
            }
    
            // Supprimer les détails associés à la vente
            $sale->products()->detach();
    
            // Supprimer la vente elle-même
            $sale->delete();
    
            return response()->json([
                'success' => 'Panier supprimé avec succès.'
            ]);
        }

       
     
    // Méthode pour calculer le montant d'un produit
    private function calculateProductAmount($product, $sale)
    {
        return ($product->pivot->priceSaleBoite * $product->pivot->quantityBoite) +
            ($product->pivot->priceSaleGellule * $product->pivot->quantityGellule) +
            ($product->pivot->priceSalePlaquette * $product->pivot->quantityPlaquette);
    }


}
