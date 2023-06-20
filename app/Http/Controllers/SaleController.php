<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
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
                    $sale = Sale::create([
                        'saleDate' => now(),
                        'playmentDatePrevueAt' => now(),
                        'playmentMode' => 'espece',
                        'estACredit' => 'test',
                        'saleAmout' => $data['montant'],
                        'salePayed' => $data['montant'],
                        'stateSale' => 'En cours',
                        'saleStay' => 0.00
                    ]);

                    DB::table('product_sale')->insert([
                        'product_id' => $product->id,
                        'sale_id' => $sale->id,
                        'amount' => $data['montant'],
                        'user' =>  $request->user()->name,
                        'quantityBoite' => $data['quantityBoite'] ?? 0,
                        'quantityGellule' => $data['quantityGellule'] ?? 0,
                        'quantityPlaquette' => $data['quantityPlaquette'] ?? 0,
                        'priceSaleBoite' => $data['priceBoite'],
                        'priceSaleGellule' => $data['priceGellule'],
                        'priceSalePlaquette' => $data['pricePlaquette'],
                    ]);

                    return response()->json(['success' => true]);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Sale::query();
        if ($request->get('search')) {
            $query = $query->where('date', $request->get('search'));
        }
        $sales = $query->latest('saleDate')->paginate(10);
        $sales->map(function ($sale) {
            $sale->reference = 'PRDT-' . $sale->id;
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
}
