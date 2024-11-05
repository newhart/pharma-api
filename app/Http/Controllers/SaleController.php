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
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Dompdf\Dompdf;
use Dompdf\Options;

class SaleController extends Controller

{
    public function store(SaleRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Utilisateur non authentifié.'
            ], 401);
        }

        $userId = auth()->id();
        
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
            $totalVente = 0;
            foreach ($request->cartProducts as $data) {
                $totalVente += $data['montant'];
            }
    
            // Calcul de la remise
            $remisePourcentage = $request->remise ?? 0;
            $montantRemise = ($remisePourcentage / 100) * $totalVente;
            $totalApresRemise = $totalVente - $montantRemise;

            $sale = Sale::create([
                'saleDate' => now(),
                'playmentDatePrevueAt' => $request->playmentDatePrevueAt ?? now(),
                'playmentMode' => $request->paymentMode ?? 'espece',
                'estACredit' => 'test',
                'saleAmout' => $totalApresRemise,
                'salePayed' => $totalApresRemise,
                'stateSale' => 'Dans le panier',
                'user_id' => $userId,
                'saleStay' => 0.00,
                'remise' => $remisePourcentage,
            ]);
            if (($sale->saleAmount === $sale->salePayed) && $sale->saleStay === 0) {
                $sale->stateSale = 'Valider';
                $sale->save();
            }
            foreach ($request->cartProducts as $data) {
                $product = Product::findOrFail($data['id']);
                if ($product) {
                    // $this->additionalQuantityProduct($data, $product);
                    DB::table('product_sale')->insert([
                        'product_id' => $product->id,
                        'sale_id' => $sale->id,
                        'amount' => $data['montant'],
                        'user' => $request->user()->name,
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

    public function addPaymentMode(Request $request): JsonResponse
    {
        // Validation de la requête
        $request->validate([
            'paymentMode' => 'required|string',
            'saleIds' => 'required|array',
            'saleIds.*' => 'integer|exists:sales,id',
        ]);

        $saleIds = $request->input('saleIds');
        $paymentMode = $request->input('paymentMode');

        try {
            // Mettre à jour les ventes avec les IDs fournis
            Sale::whereIn('id', $saleIds)->update([
                'playmentMode' => $paymentMode,
                'salePayed' => 0,
            ]);

            // Récupérer les ventes mises à jour pour la réponse
            $updatedSales = Sale::whereIn('id', $saleIds)->get();

            // Retourner la réponse JSON
            return response()->json([
                'success' => true,
                'sales' => $updatedSales,
            ]);
        } catch (\Exception $e) {
            // Retourner une réponse d'erreur en cas d'exception
            return response()->json([
                'error' => 'Une erreur est survenue lors de la mise à jour du mode de paiement.',
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


    // total de vente la semaine derniere
    public function lastWeekSales() 
    {
        Carbon::setLocale('fr');
    
        // Définir les dates de début et de fin pour la semaine dernière
        $startDate = Carbon::now()->startOfWeek()->subWeek();
        $endDate = Carbon::now()->endOfWeek()->subWeek();
    
        // Récupérer les ventes pour la semaine dernière
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])->get();
    
        // Calculer le total des ventes et le nombre de ventes, en tenant compte des paiements partiels
        $totalSales = $sales->sum(function ($sale) {
            // Si la vente est validée, prendre le montant total de saleAmount
            if ($sale->stateSale === 'Validée') {
                return $sale->saleAmout;
            }
            return $sale->salePayed;
        });
    
        $totalSalesCount = $sales->count();
    
        return response()->json([
            'total_sales' => $totalSales,
            'total_sales_count' => $totalSalesCount
        ]);
    }
    

    // cette semaine
    public function thisWeekSales() 
    {
        Carbon::setLocale('fr');
    
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
    
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])->get();
    
        $totalSalesThisWeek = $sales->sum(function ($sale) {
            return $sale->stateSale == 'validé' ? $sale->saleAmout : $sale->salePayed;
        });
        
        $totalSalesCountThisWeek = $sales->count();
    
        return response()->json([
            'total_sales_this_week' => $totalSalesThisWeek,
            'total_sales_count_this_week' => $totalSalesCountThisWeek
        ]);
    }
    
    // vente aujourd'hui
    public function todaySales() 
    {
        Carbon::setLocale('fr');
    
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
    
        $salesToday = Sale::whereBetween('created_at', [$startDate, $endDate])->get();
    
        $totalSalesToday = $salesToday->sum(function ($sale) {
            if ($sale->stateSale === 'Validée') {
                return $sale->saleAmout;
            }
            return $sale->salePayed;
        });
        
        $totalSalesCountToday = $salesToday->count();
    
        return response()->json([
            'total_sales_today' => $totalSalesToday,
            'total_sales_count_today' => $totalSalesCountToday
        ]);
    }
    
    // vente non payer
    public function salesInProgressOrExpired() {
        Carbon::setLocale('fr');
    
        $sales = Sale::whereIn('stateSale', ['En cours', 'Date dépassée'])
            ->get();
    
        $totalSalesCount = $sales->count();
    
        $totalRemainingAmount = $sales->sum(function ($sale) {
            return $sale->saleAmout - $sale->salePayed;
        });
    
        return response()->json([
            'total_sales_count' => $totalSalesCount,
            'total_remaining_amount' => $totalRemainingAmount
        ]);
    } 

    //pourcentage
    public function getSaleStatistics()
    {
        // Total des ventes
        $totalSales = Sale::count();
    
        $validatedSales = Sale::where('stateSale', 'Valider')->count();
    
        $inProgressQuery = Sale::where('stateSale', 'En cours')
                                ->where('saleDate', '>', Carbon::now());
        $inProgressSales = $inProgressQuery->count();
    
        $expiredQuery = Sale::where('stateSale', 'Date dépassée')
                            ->where('saleDate', '<', Carbon::now());

        $expiredSales = $expiredQuery->count();
    
        // Calcul des pourcentages
        $validatedPercentage = $totalSales > 0 ? ($validatedSales / $totalSales) * 100 : 0;
        $inProgressPercentage = $totalSales > 0 ? ($inProgressSales / $totalSales) * 100 : 0;
        $expiredPercentage = $totalSales > 0 ? ($expiredSales / $totalSales) * 100 : 0;
    
        return response()->json([
            'totalSales' => $totalSales,
            'validatedSales' => $validatedSales,
            'inProgressSales' => $inProgressSales,
            'expiredSales' => $expiredSales,
            'validatedPercentage' => round($validatedPercentage, 2),
            'inProgressPercentage' => round($inProgressPercentage, 2),
            'expiredPercentage' => round($expiredPercentage, 2),
        ]);
    }

    // dans une année
    public function getMonthlySalesForYear($year)
    {
        $monthlySales = [];

        foreach (range(1, 12) as $month) {
            $sales = Sale::whereYear('saleDate', $year)
                ->whereMonth('saleDate', $month)
                ->sum('salePayed');

            $totalPrice = Sale::whereYear('saleDate', $year)
                ->whereMonth('saleDate', $month)
                ->with('products')
                ->get()
                ->sum(function ($sale) {
                    return $sale->products->sum(function ($product) {
                        return $product->pivot->priceSaleBoite * $product->pivot->quantityBoite +
                               $product->pivot->priceSalePlaquette * $product->pivot->quantityPlaquette +
                               $product->pivot->priceSaleGellule * $product->pivot->quantityGellule;
                    });
                });

            $monthlySales[] = [
                'month' => Carbon::createFromFormat('m', $month)->format('F'),
                'totalSales' => $sales,
                'totalPrice' => $totalPrice,
            ];
        }

        $totalSalesForYear = array_sum(array_column($monthlySales, 'totalSales'));
        $totalPriceForYear = array_sum(array_column($monthlySales, 'totalPrice'));

        return response()->json([
            'year' => $year,
            'monthlySales' => $monthlySales,
            'totalSalesForYear' => $totalSalesForYear,
            'totalPriceForYear' => $totalPriceForYear,
        ]);
    }

    // entre deux date
    public function getAnnualSalesTotal(Request $request)
    {
        // Validation des dates
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $years = range($startDate->year, $endDate->year);

        $totalsPerYear = [];

        foreach ($years as $year) {
            $sales = Sale::whereYear('saleDate', $year)
                         ->whereBetween('saleDate', [$startDate, $endDate])
                         ->sum('salePayed');

            $totalsPerYear[$year] = $sales;
        }

        $totalGlobal = Sale::whereBetween('saleDate', [$startDate, $endDate])
                           ->sum('salePayed');

        return response()->json([
            'totals_per_year' => $totalsPerYear,
            'total_global' => $totalGlobal,
        ]);
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
        // Définir les dates de début et de fin de l'année en cours
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        // Récupérer les données de vente pour l'année en cours
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('MONTH(created_at) as month, SUM(saleAmout) as ca')
            ->groupBy('month')
            ->get();

        // Initialiser le tableau des ventes mensuelles avec des valeurs par défaut à 0
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

        // Variables pour stocker le maximum et le total des ventes
        $maxResult = 0;
        $totalSales = 0;

        // Remplir les ventes mensuelles et calculer le total
        foreach ($sales as $sale) {
            $monthName = Carbon::create()->month($sale->month)->formatLocalized('%b');
            $salesArray[$monthName] = $sale->ca;
            $totalSales += $sale->ca;  // Ajouter chaque vente au total
            if ($sale->ca > $maxResult) {
                $maxResult = $sale->ca;
            }
        }

        // Retourner les données sous forme de réponse JSON, y compris le total et le maximum réels des ventes
        return response()->json([
            'sales' => $salesArray,
            'maxResult' => $maxResult,
            'totalSales' => $totalSales
        ]);
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
        $imagePath = storage_path('app/public/' . $path);
        if (!file_exists($imagePath)) {
            return null;
        }

        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

        // Validation
    public function checkValidation(Request $request)
    {
        $saleIds = $request->input('sale_ids');
        if (!is_array($saleIds) || empty($saleIds)) {
            return response()->json(['success' => false, 'message' => 'No sales IDs provided'], 400);
        }
        $sales = Sale::whereIn('id', $saleIds)->get();
        $finalizedSales = [];

        foreach ($sales as $sale) {
            $stay = $request->input('stay'); 
            $sale->saleStay = $sale->saleStay - (float) $stay;
        
            if ($sale->saleStay <= 0.0) {
                $sale->stateSale = 'Valider';
                $finalizedSales[] = $sale; 
            }
            $sale->save();
        }  

        foreach ($finalizedSales as $sale) {
            foreach ($sale->products as $product) {
                $this->updateProductQuantities([
                    'quantityBoite' => $product->pivot->quantityBoite,
                    'quantityPlaquette' => $product->pivot->quantityPlaquette,
                    'quantityGellule' => $product->pivot->quantityGellule,
                ], $product);
            }
        }


        $formattedSales = collect($finalizedSales)->map(function ($sale) {
            
            $sale->saleAmout = PriceService::formatPrice($sale->saleAmout);
            $sale->salePayed = PriceService::formatPrice($sale->salePayed);
            $sale->saleStay = PriceService::formatPrice($sale->saleStay);
            
           
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
    
            
            $totalAmount = $cartProducts->sum('montant');
    
            return [
                'cartProducts' => $cartProducts,
                'total' => $sale->saleAmout,
                'remise' => $sale->remise,
                'totalAmount' => $totalAmount,
                'playmentMode' => $sale->playmentMode,
                'invoice_number' => $sale->invoice_number,
            ];
        });
    
        $grandTotal = $formattedSales->sum('totalAmount');
        
               
                 $setting = Setting::first();
                 $logoBase64 = null;
                    
                 
              
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

               
                return $pdf->download('sales.pdf', [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="sales.pdf"',
                ]);

        
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
        $count = Sale::where('stateSale', 'Dans le panier')->count();  

         $totalSalePayed = Sale::where('stateSale', 'Dans le panier')->sum('salePayed');  

        return response()->json([ 
            'count' => $count,
            'totalSalePayed' => $totalSalePayed
        ]);
    }

    // methode qui recupere les vente en cours
    public function listInProgress(): JsonResponse
    {     
        $salesInProgress = Sale::where('stateSale', 'Dans le panier')
            ->with('products')
            ->get();
    
        $formattedSales = $salesInProgress->map(function ($sale) {
            
            $sale->saleAmout = PriceService::formatPrice($sale->saleAmout);
            $sale->salePayed = PriceService::formatPrice($sale->salePayed);
            $sale->saleStay = PriceService::formatPrice($sale->saleStay);
            
           
            $cartProducts = $sale->products->map(function ($product) use ($sale) {
                return [
                    // 'id_product_sale' => $pro,
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

           
            $totalAmount = $cartProducts->sum('montant');
   
            $remise = $sale->remise ? ($totalAmount * $sale->remise / 100) : 0;

            $totalAmountAvecRemise = $totalAmount - $remise;

                return [
                
                    'cartProducts' => $cartProducts,
                    'total' => $sale->saleAmout,
                    'remise' => $sale->remise,
                    'totalAmount' => $totalAmountAvecRemise,
                    'playmentMode' => $sale->playmentMode
                ];
        });

        $grandTotal = $formattedSales->sum('totalAmount');
    
        return response()->json([
            'sales' => $formattedSales,
            'grandTotal' => $grandTotal, 
        ]);
    }

     // methode qui recupere les ids de vente en cours
    public function getSalesIdsInProgress(): JsonResponse
    {
       
        $salesInProgress = Sale::where('stateSale', 'En cours')
            ->pluck('id'); 

        
        return response()->json([
            'salesIds' => $salesInProgress,
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
    
    // filtre
    public function getVentes(Request $request)
    {
        $filter = $request->input('filter', 'all');
    
        $query = Sale::query(); 
    
        $states = [
            'valider' => 'Valider',
            'enCours' => 'En cours',
            'depasser' => 'Date dépassée',
        ];
        if (array_key_exists($filter, $states)) {
            $query->where('stateSale', $states[$filter]);
        }
        $allSales = $query->get();
    
        return response()->json(['allSales' => $allSales]);
    }
    
     
    // Méthode pour calculer le montant d'un produit
    private function calculateProductAmount($product, $sale)
    {
        return ($product->pivot->priceSaleBoite * $product->pivot->quantityBoite) +
            ($product->pivot->priceSaleGellule * $product->pivot->quantityGellule) +
            ($product->pivot->priceSalePlaquette * $product->pivot->quantityPlaquette);
    }

    private function imageToBase($path) {
        $imagePath = public_path($path);
        if (!file_exists($imagePath)) {
            return null;
        }
    
        $imageData = file_get_contents($imagePath);
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    public function generateInvoicePDF($sales, $setting) {
        $logoBase64 = $this->imageToBase64($setting->logo);

        $formattedSales = $sales->map(function ($sale) {
            return [
                'invoice_number' => $sale->invoice_number,
                'clientName' => $sale->clientName,
                'playmentDatePrevueAt' => $sale->playmentDatePrevueAt,
                'saleAmout' => $sale->saleAmout,
                'salePayed' => $sale->salePayed,
                'saleStay' => $sale->saleStay,
            ];
        });
    
        $pdf = Pdf::loadView('pdf.invoice', [
        'sales' => $formattedSales,
        'grandTotal' => $sales->sum('saleAmout'),
        'logoBase64' => $logoBase64,
        'nomEntreprise' => $setting->nomEntreprise,
        'nif' => $setting->nif,
        'stat' => $setting->stat,
        'mail' => $setting->mail,
        'tel' => $setting->tel,
    ]);
    
        
        $pdfDir = public_path('invoices');
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
    
        $pdfPath = public_path('invoices/invoice.pdf');
        $pdf->save($pdfPath);
    
        return $pdfPath;
    }
    
    

    public function updateSaleDetails(Request $request): JsonResponse
    {
        $request->validate([
            'saleIds' => 'required|array',
            'saleIds.*' => 'integer|exists:sales,id',
            'salePayed' => 'required|numeric',
            'playmentDatePrevueAt' => 'nullable|date',
            'clientName' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $saleIds = $request->input('saleIds');
        $salePayed = $request->input('salePayed');
        $playmentDatePrevueAt = $request->input('playmentDatePrevueAt');
        $clientName = $request->input('clientName');
        $description = $request->input('description');

        $sales = Sale::whereIn('id', $saleIds)->get();

        if ($sales->isEmpty()) {
            return response()->json([
                'error' => 'Aucune vente trouvée pour les IDs spécifiés.'
            ], 404);
        }

        try {
            foreach ($sales as $sale) {
                $sale->salePayed = $salePayed;
                $sale->playmentDatePrevueAt = $playmentDatePrevueAt ?? $sale->playmentDatePrevueAt;
                $sale->clientName = $clientName;
                $sale->description = $description;

                $sale->amount_remaining = $sale->saleAmout - $sale->salePayed;
                

                if ($sale->playmentDatePrevueAt) {
                    $playmentDatePrevueAt = Carbon::parse($sale->playmentDatePrevueAt);
                    $saleDate = Carbon::parse($sale->saleDate);
                    $sale->saleStay = $playmentDatePrevueAt->diffInDays($saleDate);
                } else {
                    $sale->saleStay = 0;
                }

                if ($sale->amount_remaining > 0) {
                    $sale->stateSale = 'En cours';
                } else {
                    $sale->stateSale = 'Paid';
                }
                $sale->save();

                foreach ($sale->products as $product) {
                $this->updateProductQuantities([
                    'quantityBoite' => $product->pivot->quantityBoite,
                    'quantityPlaquette' => $product->pivot->quantityPlaquette,
                    'quantityGellule' => $product->pivot->quantityGellule,
                ], $product);
            }
            }

            // Récupérer toutes les ventes en cours
            $salesInProgress = Sale::where('stateSale', 'En cours')->get();

            // Calculer le grand total
            $grandTotal = $salesInProgress->sum('saleAmout');
             // Calculer le montant restant
             $amountRemaining = $grandTotal - $salePayed;

            // Récupérer les IDs des ventes en cours
            $idsInProgress = $salesInProgress->pluck('id')->toArray();

            

            // return response()->json([
            //     'success' => true,
            //     'sales' => $sales->map(function ($sale) {
            //         return [
            //             'id' => $sale->id,
            //             'saleDate' => $sale->saleDate,
            //             'saleAmout' => $sale->saleAmout,
            //             'salePayed' => $sale->salePayed,
            //             'saleStay' => $sale->saleStay,
            //             'estACredit' => $sale->estACredit,
            //             'playmentMode' => $sale->playmentMode,
            //             'playmentDatePrevueAt' => $sale->playmentDatePrevueAt,
            //             'clientName' => $sale->clientName,
            //             'description' => $sale->description,
            //             'stateSale' => $sale->stateSale,
            //             'remise' => $sale->remise,
            //             'created_at' => $sale->created_at,
            //             'updated_at' => $sale->updated_at,
            //             'user_id' => $sale->user_id,
            //             'invoice_number' => $sale->invoice_number,
            //             'amount_remaining' => $sale->saleAmout - $sale->salePayed
            //         ];
            //     }),
            //     'allSaleIds' => $idsInProgress, // Afficher tous les IDs des ventes en cours
            //     'grandTotal' => $grandTotal,
            //     'amount_remaining' => $amountRemaining,
            // ]);
        
              // Récupérer l'objet Setting
        $setting = Setting::first();

          // Générer le PDF après mise à jour
          $this->generateInvoicePDF($sales, $setting);

        // Retourner une réponse JSON avec l'URL du PDF
        return response()->json([
            'success' => true,
            'message' => 'Détails de la vente mis à jour avec succès.',
            'pdf_url' => url('invoices/invoice.pdf')
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la mise à jour des détails des ventes.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

        // listes des ventes a credit 
    public function getAllSales(Request $request): JsonResponse
    {
        $searchTerm = $request->input('searchTerm');
        $clientName = $request->input('clientName');
        $reference = $request->input('reference');
        $stateSale = $request->input('stateSale');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');    

        try {
            // Récupérer toutes les ventes avec les états 'Non validée' et 'Valider'
            $query  = Sale::whereIn('stateSale', ['En cours', 'Valider', 'Date dépassée'])
                            ->with('products');
                            // ->get();

            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('clientName', 'like', '%' . $searchTerm . '%')
                      ->orWhere('reference', 'like', '%' . $searchTerm . '%');
                    });
            }

            if ($clientName) {
                $query->where('clientName', 'like', '%' . $clientName . '%');
            }
    
            if ($reference) {
                $query->where('reference', 'like', '%' . $reference . '%');
            }
    
            if ($stateSale) {
                $query->where('stateSale', $stateSale);
            }
    
            if ($startDate && $endDate) {
                $query->whereBetween('saleDate', [$startDate, $endDate]);
            }
    

            $allSales = $query->get();

            if ($allSales->isEmpty()) {
                return response()->json([
                    'message' => 'Aucune vente trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'allSales' => $allSales->map(function ($sale) {
                    $isDateExceeded = false;
                    if ($sale->stateSale === 'En cours') {
                        $playmentDatePrevueAt = \Carbon\Carbon::parse($sale->playmentDatePrevueAt);
                        $currentDate = \Carbon\Carbon::now();
                        if ($currentDate->greaterThan($playmentDatePrevueAt)) {
                            $isDateExceeded = true;
                            $sale->stateSale = 'Date dépassée';
                            $sale->save(); 
                        }
                    }
    
                    return [
                        'id' => $sale->id,
                        'reference' => 'VNT-' . $sale->id,
                        'saleDate' => \Carbon\Carbon::parse($sale->saleDate)->format('d/m/Y'),
                        'saleAmout' => $sale->saleAmout,
                        'salePayed' => $sale->salePayed,
                        'saleStay' => $sale->saleStay,
                        'estACredit' => $sale->estACredit,
                        'playmentMode' => $sale->playmentMode,
                        'playmentDatePrevueAt' => $sale->playmentDatePrevueAt,
                        'clientName' => $sale->clientName,
                        'description' => $sale->description,
                        'stateSale' => $isDateExceeded ? 'Date dépassée' : $sale->stateSale,
                        'remise' => $sale->remise,
                        'created_at' => $sale->created_at->format('d-m-Y H:i:s'),
                        'updated_at' => $sale->updated_at->format('d/m/Y'),
                        'time' => $sale->updated_at->format('H:i'),
                        'user_id' => $sale->user_id,
                        'invoice_number' => $sale->invoice_number,
                        'amount_remaining' => $sale->saleAmout - $sale->salePayed,
                        'products' => $sale->products->map(function ($product) {
                            return [
                                'product_id' => $product->id,
                                'name' => $product->name,
                                'quantityGellule' => $product->pivot->quantityGellule,
                                'quantityPlaquette' => $product->pivot->quantityPlaquette,
                                'quantityBoite' => $product->pivot->quantityBoite,
                                'priceSaleGellule' => $product->pivot->priceSaleGellule,
                                'priceSalePlaquette' => $product->pivot->priceSalePlaquette,
                                'priceSaleBoite' => $product->pivot->priceSaleBoite,
                                'amount' => $product->pivot->amount,
                                'user' => $product->pivot->user,
                            ];
                        })
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la récupération des ventes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePayment(Request $request): JsonResponse
    {
        try {
            // Récupérer les IDs des ventes à mettre à jour
            $saleIds = $request->input('sale_ids');
            if (!is_array($saleIds) || empty($saleIds)) {
                return response()->json(['success' => false, 'message' => 'No sales IDs provided'], 400);
            }

            // Récupérer les ventes par leurs IDs
            $sales = Sale::whereIn('id', $saleIds)->get();
            $finalizedSales = [];

            foreach ($sales as $sale) {
                // Mettre à jour le montant payé pour chaque vente
                $salePayed = $request->input('salePayed');
                $sale->salePayed += (float) $salePayed;

                // Recalculer le reste à payer
                $sale->saleStay = $sale->saleAmout - $sale->salePayed;

                // Si le reste à payer est 0 ou moins, valider la vente
                if ($sale->saleStay <= 0.0) {
                    $sale->saleStay = 0;
                    $sale->stateSale = 'Valider';
                    $finalizedSales[] = $sale;
                }

                // Sauvegarder les modifications
                $sale->save();
            }

            // Optionnel : Exécuter des actions supplémentaires pour les ventes validées
            foreach ($finalizedSales as $sale) {
                // Exemple : mise à jour des quantités de produits
                foreach ($sale->products as $product) {
                    $this->updateProductQuantities([
                        'quantityBoite' => $product->pivot->quantityBoite,
                        'quantityPlaquette' => $product->pivot->quantityPlaquette,
                        'quantityGellule' => $product->pivot->quantityGellule,
                    ], $product);
                }
            }

            // Préparer la réponse avec les ventes mises à jour
            $formattedSales = collect($finalizedSales)->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'saleAmout' => $sale->saleAmout,
                    'salePayed' => $sale->salePayed,
                    'saleStay' => $sale->saleStay,
                    'stateSale' => $sale->stateSale,
                    'playmentMode' => $sale->playmentMode,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Paiements mis à jour avec succès.',
                'sales' => $formattedSales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la mise à jour des paiements.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function validateSaleState(Request $request)
    {
        // Valider la requête
        $validatedData = $request->validate([
            'sale_ids' => 'required|array',
            'sale_ids.*' => 'integer|exists:sales,id',
            'stay' => 'required|numeric'
        ]);

        // Récupérer les ventes par ID
        $sales = Sale::whereIn('id', $validatedData['sale_ids'])->get();

        foreach ($sales as $sale) {
            // Mettre à jour le stateSale à "Validée"
            $sale->stateSale = 'Validée';
            $sale->save();

        }

        // Retourner un message de succès
        return response()->json([
            'message' => 'Les ventes ont été validées avec succès.'
        ], 200);

    }

        // doanload
    public function downloadSalesReport(Request $request)
    {
        // Valider la requête
        $validatedData = $request->validate([
            'sale_ids' => 'required|array',
            'sale_ids.*' => 'integer|exists:sales,id'
        ]);

        // Récupérer les ventes par ID
        $sales = Sale::whereIn('id', $validatedData['sale_ids'])->get();

        // Générer le PDF
        $pdf = \PDF::loadView('pdf.sales_report', ['sales' => $sales]);

        // Retourner le PDF en reponse
        return response()->stream(
            function () use ($pdf) {
                $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="sales_report.pdf"',
            ]
        );
    }

    public function deleteSales(Request $request): JsonResponse
    {
        // Valider les données de la requête avec le nom de champ 'saleIds'
        $validatedData = $request->validate([
            'saleIds' => 'required|array',
            'saleIds.*' => 'integer|exists:sales,id',
        ]);
    
        // Récupérer les ventes par ID
        $sales = Sale::whereIn('id', $validatedData['saleIds'])->get();
    
        if ($sales->isEmpty()) {
            return response()->json([
                'error' => 'Aucune vente trouvée.'
            ], 404);
        }
    
        // Supprimer les détails associés à chaque vente et les ventes elles-mêmes
        foreach ($sales as $sale) {
            $sale->products()->detach();
            $sale->delete();
        }
    
        return response()->json([
            'success' => 'Les ventes ont été supprimées avec succès.'
        ]);
    }

    public function downloadPendingSalesPdf()
    {
        // Récupérer les ventes en attente
        $sales = Sale::where('stateSale', 'Non validée')
                    ->with('products')
                    ->get();
    
        if ($sales->isEmpty()) {
            // Si aucune vente en attente
            return response()->json([
                'message' => 'Aucune vente en attente.'
            ], 404);
        }
    
        // Préparer les données pour la vue
        $data = $sales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'reference' => 'VNT-' . $sale->id,
                'saleDate' => \Carbon\Carbon::parse($sale->saleDate)->format('d/m/Y'),
                'saleAmout' => $sale->saleAmout,
                'salePayed' => $sale->salePayed,
                'amount_remaining' => $sale->saleAmout - $sale->salePayed,
                'estACredit' => $sale->estACredit,
                'playmentMode' => $sale->playmentMode,
                'playmentDatePrevueAt' => $sale->playmentDatePrevueAt,
                'clientName' => $sale->clientName,
                'description' => $sale->description,
                'stateSale' => $sale->stateSale,
                'remise' => $sale->remise,
                'created_at' => $sale->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $sale->updated_at->format('d/m/Y'),
                'time' => $sale->updated_at->format('H:i'),
                'user_id' => $sale->user_id,
                'invoice_number' => $sale->invoice_number,
                'products' => $sale->products->map(function ($product) {
                    return [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'quantityGellule' => $product->pivot->quantityGellule,
                        'quantityPlaquette' => $product->pivot->quantityPlaquette,
                        'quantityBoite' => $product->pivot->quantityBoite,
                        'priceSaleGellule' => $product->pivot->priceSaleGellule,
                        'priceSalePlaquette' => $product->pivot->priceSalePlaquette,
                        'priceSaleBoite' => $product->pivot->priceSaleBoite,
                        'amount' => $product->pivot->amount,
                        'user' => $product->pivot->user,
                    ];
                })
            ];
        });


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

        // Charger la vue Blade avec les données
        $pdfView = View::make('pending_sales_pdf', [
            'sales' => $data,
            'logoBase64' => $logoBase64,
            'dateToday' => $dateToday,
            'now' => $now,
            'logoBase64' => $logoBase64,
            'nomEntreprise' => $nomEntreprise,
            'nif' => $nif,
            'stat' => $stat,
            'mail' => $mail,
            'tel' => $tel,
        ])->render();


        // Configurer dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isBase64ImageEnabled', true);
    
        // Initialiser Dompdf avec les options
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfView);
        $dompdf->setPaper('A4', 'portrait');
    
        // Rendre le PDF
        $dompdf->render();
    
        // Télécharger le PDF avec en-têtes CORS
    return response($dompdf->output(), 200)
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'attachment; filename="pending_sales.pdf"')
    ->header('Access-Control-Allow-Origin', '*')
    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
    ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
    }
        
    public function downloadSaleDetailPdf($id)
    {
        // Récupérer la vente spécifique
        $sale = Sale::where('id', $id)
                    ->with('products')
                    ->first();

        if (!$sale) {
            return response()->json(['message' => 'Vente non trouvée.'], 404);
        }

        // Préparer les données pour la vue
        $data = [
            'id' => $sale->id,
            'reference' => 'VNT-' . $sale->id,
            'saleDate' => \Carbon\Carbon::parse($sale->saleDate)->format('d/m/Y'),
            'saleAmout' => $sale->saleAmout,
            'salePayed' => $sale->salePayed,
            'amount_remaining' => $sale->saleAmout - $sale->salePayed,
            'estACredit' => $sale->estACredit,
            'playmentMode' => $sale->playmentMode,
            'playmentDatePrevueAt' => $sale->playmentDatePrevueAt,
            'clientName' => $sale->clientName,
            'description' => $sale->description,
            'stateSale' => $sale->stateSale,
            'remise' => $sale->remise,
            'created_at' => $sale->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $sale->updated_at->format('d/m/Y'),
            'time' => $sale->updated_at->format('H:i'),
            'user_id' => $sale->user_id,
            'invoice_number' => $sale->invoice_number,
            'products' => $sale->products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantityGellule' => $product->pivot->quantityGellule,
                    'quantityPlaquette' => $product->pivot->quantityPlaquette,
                    'quantityBoite' => $product->pivot->quantityBoite,
                    'priceSaleGellule' => $product->pivot->priceSaleGellule,
                    'priceSalePlaquette' => $product->pivot->priceSalePlaquette,
                    'priceSaleBoite' => $product->pivot->priceSaleBoite,
                    'amount' => $product->pivot->amount,
                    'user' => $product->pivot->user,
                ];
            })
        ];

        // Heure actuelle à Madagascar
        $now = Carbon::now('Indian/Antananarivo');

        // Date actuelle
        $dateToday = Carbon::now()->format('d/m/Y');

        // Récupérer les paramètres de l'entreprise
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

        // Charger la vue Blade avec les données
        $pdfView = View::make('sale_detail_pdf', [
            'sale' => $data,
            'logoBase64' => $logoBase64,
            'dateToday' => $dateToday,
            'now' => $now,
            'nomEntreprise' => $nomEntreprise,
            'nif' => $nif,
            'stat' => $stat,
            'mail' => $mail,
            'tel' => $tel,
        ])->render();

        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isBase64ImageEnabled', true);

        // Initialiser Dompdf avec les options
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfView);
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Télécharger le PDF avec les en-têtes CORS
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="sale_detail.pdf"')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
    }
}


