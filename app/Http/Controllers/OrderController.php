<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // create Order
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $request->validate([
            'products' => 'required|array',
            'products.*.quantityBoite' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'dateCommande' => now(),
            'user_id' => $user->id
        ]);

        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $this->createOrder($productData, $order->id, $product->id);
        }

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    private function createOrder(array $productData, int $order_id, int $product_id): void
    {
        $quantityForOrder = $productData['quantityBoite'];
        if (is_null($quantityForOrder)) {
            throw new \Exception('Quantity for order cannot be null');
        }

        DB::table('order_product')->insert([
            'montantOrder' => (int) $quantityForOrder * (float) $productData['price'],
            'quantityForOrder' => $quantityForOrder,
            'fournisseurPrice' => $productData['price'],
            'order_id' => $order_id,
            'product_id' => $product_id
        ]);
    }

    public function addProductToOrder(Request $request, int $orderId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'products' => 'required|array',
            'products.*.quantityBoite' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.product_id' => 'required|integer|exists:products,id',
        ]);

        $order = Order::findOrFail($orderId);

        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $this->createOrder($productData, $order->id, $product->id);
        }

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();

        $orders = Order::with('products')
            ->where('user_id', $user->id)
            ->paginate(10);
        return response()->json($orders);
    }

    private function updateProduct(Product $product, $order): void
    {
        $product->quantityBoite = $product->quantityBoite + $order->quantityForOrder;
        $product->quantityPlaquette = $product->quantityBoite * $product->numberPlaquette;
        $product->quantityGellule = $product->quantityPlaquette * $product->numberGellule;
        $product->save();
    }

  public function cancel(int $order_id): JsonResponse
{
    $user = Auth::user();
    $order = Order::where('id', $order_id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    try {
        DB::table('order_product')
            ->where('order_id', $order_id)
            ->delete();

        $order->delete();

        return response()->json(['success' => true]);
    } catch (\Throwable $th) {
        return response()->json(['error' => $th->getMessage()]);
    }
}


    public function deleteProductFromOrder(Request $request, int $order_id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $order = Order::find($order_id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        DB::table('order_product')
            ->where('order_id', $order_id)
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Product removed from order successfully']);
    }

    public function validation(Request $request): JsonResponse
    {
        $user = Auth::user();

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
