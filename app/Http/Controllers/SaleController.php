<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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

    private function additionalQuantityProduct(array  $data, Product $product): void
    {
        if ($data['quantityBoite'] !== "" && $product->quantityBoite) $product->quantityBoite -= (int) $data['quantityBoite'];
        if ($data['quantityGellule'] !== "" && $product->quantityGellule) $product->quantityGellule -= (int) $data['quantityGellule'];
        if ($data['quantityPlaquette'] !== "" && $product->quantityPlaquette) $product->quantityPlaquette -= (int) $data['quantityPlaquette'];
        // update the product in the stock
        $product->save();
    }
}
