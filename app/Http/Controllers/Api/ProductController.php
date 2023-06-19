<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();
        if ($request->get('search')) {
            $query = $query->where('name', 'LIKE', "%{$request->get('search')}%");
        }
        $products = $query->latest()->paginate(20);
        $products->map(function ($product) {
            $product->reference = 'PRDT-' .  $product->id;
        });
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data =  $request->all();
        if (isset($data['typeValidation'])) unset($data['typeValidation']); // remove type validation if exits
        $product = Product::create($data);
        if ($product) {
            return response()->json([
                'success' => true
            ]);
        }
        return response()->json([
            'error' => true
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if ($request->user()->can('update', $product)) {
            $product->update($request->all());
            return response()->json([
                'success' => true
            ]);
        }
        return response()->json(['error' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,  Product $product): JsonResponse
    {
        if ($request->user()->can('delete', $product)) {
            $product->delete();
            return response()->json([
                'success' => true
            ]);
        }

        return response()->json(['error' => true]);
    }
}
