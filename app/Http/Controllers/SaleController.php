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

class SaleController extends Controller
{
    public function store(SaleRequest $request): JsonResponse
    {
        // find the product sale
        foreach ($request->cartProducts as $data) {
            $product = Product::findOrFail($data['id']);
            if ($product) {
                try {
                    $this->additionalQuantityProduct($data, $product);
                    if ($data['remise']) {
                        $amountRemise = $data['montant'] *  ((int) $data['remise'] / 100);
                        $data['montant'] -= $amountRemise;
                    }
                    $sale = Sale::create([
                        'saleDate' => now(),
                        'playmentDatePrevueAt' => now(),
                        'playmentMode' => 'espece',
                        'estACredit' => 'test',
                        'saleAmout' => $data['montant'],
                        'salePayed' => $data['montant'],
                        'stateSale' => 'En cours',
                        'user_id' => $request->user()->id,
                        'saleStay' => 0.00
                    ]);
                    // update if the sale is completed
                    if (($sale->saleAmount === $sale->salePayed) && $sale->saleStay === 0) {
                        $sale->stateSale = 'Valider';
                        $sale->save();
                    }

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

                    return response()->json(['success' => true]);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['success' => true]);
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
        return response()->json(['sales' => $salesArray, 'maxResult' => $maxResult]);
    }

    // Function to get the day name in French based on the day number (1 for Monday, 2 for Tuesday, etc.)
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
        return response()->json(['sales' => $salesArray, 'maxResult' => $maxResult]);
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
        $sales = $query->latest()->paginate(10);
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

    public function checkValidation(Request $request, Sale $sale): JsonResponse
    {
        $sale->saleStay = $sale->saleStay - (float) $request->stay;
        if ($sale->saleStay === 0.0) {
            $sale->stateSale = 'Valider';
        }
        $sale->save();
        return response()->json(['success' => true]);
    }
}
