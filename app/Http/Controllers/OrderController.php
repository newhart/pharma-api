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
    // public function store(int $product_id, Request $request): JsonResponse
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json(['error' => 'User not authenticated'], 401);
    //     }

    //     $order = Order::create([
    //         'dateCommande' => now(),
    //         'user_id' => $user->id
    //     ]);
    //     $product = Product::findOrFail($product_id);
    //     $this->createOrder($request, $order->id, $product->id);

    //     return response()->json(['success' => true, 'order_id' => $order->id]);
    // }

    public function addToCart(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Ajoutez les produits au panier (ex : dans une table 'carts')
        $cart = DB::table('carts')->updateOrInsert(
            ['user_id' => $user->id],
            ['products' => json_encode($request->products)]
        );

        return response()->json(['success' => true, 'message' => 'Products added to cart']);
    }

    public function finalizeOrder(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Récupérer les produits du panier
        $cart = DB::table('carts')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Créez la commande
        $order = Order::create([
            'dateCommande' => now(),
            'user_id' => $user->id
        ]);

        // Parcourez les produits et ajoutez-les à la commande
        foreach (json_decode($cart->products) as $product) {
            $this->createOrder($request, $order->id, $product->id);
        }

        // Optionnel : supprimer les produits du panier après finalisation
        DB::table('carts')->where('user_id', $user->id)->delete();

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

    private function createOrder(Request $request,  int $order_id, int $product_id): void
    {
            $quantityForOrder = $request->quantityBoite;
        if (is_null($quantityForOrder)) {
            throw new \Exception('Quantity for order cannot be null');
        }
        
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
        $user = Auth::user();
        $order = Order::where('id', $order_id)->where('user_id', $user->id)->firstOrFail();
        try {
            DB::table('order_product')->where('order_id', $order_id)
                ->where('product_id', $product_id)
                ->delete();
            $order->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
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
