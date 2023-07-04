<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(int $product_id, Request $request): JsonResponse
    {
        $order = Order::create(['dateCommande' => now()]);
        $product = Product::findOrFail($product_id);
        $this->createOrder($request, $order->id, $product->id);
        return response()->json(['success' => true]);
    }

    public function index(): JsonResponse
    {
        $orders = Order::with('products')->paginate(10);
        return response()->json($orders);
    }

    private function createOrder(Request $request,  int $order_id, int $product_id): void
    {
        DB::table('order_product')->insert([
            'montantOrder' => (int) $request->quantityBoite * (float) $request->price,
            'quantityForOrder' => $request->quantityBoite,
            'fournisseurPrice' => $request->price,
            'order_id' => $order_id,
            'product_id' => $product_id
        ]);
    }
    private function  updateProduct(Product $product, $order): void
    {
        $product->quantityBoite = $product->quantityBoite + $order->quantityForOrder;
        $product->quantityPlaquette = $product->quantityBoite * $product->numberPlaquette;
        $product->quantityGellule = $product->quantityPlaquette * $product->numberGellule;
        $product->save();
    }
    public function cancel(int $product_id, int $order_id): JsonResponse
    {
        try {
            DB::table('order_product')->where('order_id', $order_id)
                ->where('product_id', $product_id)
                ->delete();
            Order::where('id', $order_id)->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function validation(Request $request): JsonResponse
    {
        try {
            $order = DB::table('order_product')
                ->where('product_id', $request->product_id)
                ->where('order_id', $request->order_id)
                ->first();
            $product = Product::findOrFail($request->product_id);
            $this->updateProduct($product, $order);
            return response()->json(['data' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
