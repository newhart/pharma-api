<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function cartList(): JsonResponse
    {
        $cartItems = \Cart::getContent();
        return response()->json($cartItems);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $cart =  \Cart::add([
            'id' => $request->id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => 1
        ]);
        if ($cart) {
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => true]);
    }

    public function removeCart(Request $request): JsonResponse
    {
        \Cart::remove($request->id);
        return response()->json(['success' => true]);
    }
}
