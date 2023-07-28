<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role === "Admin") {
            $menus = Menu::with('subMenu')->get();
        } else {
            $menus = Menu::whereHas('roles', function ($q) use ($request) {
                $q->whereHas('user', function ($q) use ($request) {
                    $q->where('id', $request->user()->id);
                });
            })
                ->with('subMenu')->get();
        }

        return response()->json($menus);
    }
}
