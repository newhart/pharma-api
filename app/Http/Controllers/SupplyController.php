<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Product;
use App\Models\Enter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplyController extends Controller
{
    // public function addSupply(Request $request, $id, $differenceInYears = 1): JsonResponse
    // {
    //     $request->validate([
    //         'quantity' => 'required|integer|min:1',
    //         'datePeremption' => 'nullable|date',
    //     ]);
    //     try {
    //         $product = Product::findOrFail($id);
    
    //         $product->quantityBoite += $request->quantity;
    
    //         $warningMessage = null;
    
    //         if ($request->has('datePeremption')) {
    //             $product->datePeremption = $request->datePeremption;
    
    //             $expirationDate = Carbon::parse($product->datePeremption);
    //             $currentDate = Carbon::now();
    //             $diffInYears = $currentDate->diffInYears($expirationDate);
    
    //             if ($diffInYears <= $differenceInYears) {
    //                 $warningMessage = "Veuillez mettre directement l'article sur votre étagère car sa date de péremption est proche";
    //             }
    //         }
    
    //         $product->save();
    
    //         $enter = Enter::create([
    //             'dateEntrer' => now(),
    //             'user_id' => auth()->id()
    //         ]);
    
    //         $enter->products()->attach($product->id, [
    //             'quantityEnter' => $request->quantity,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    
    //         $response = [
    //             'data' => true,
    //             'message' => 'Approvisionnement ajouté avec succès',
    //         ];
    
    //         if ($warningMessage) {
    //             $response['warning'] = $warningMessage;
    //         }
    
    //         return response()->json($response);
    
    //     } catch (\Throwable $th) {
    //         return response()->json(['error' => $th->getMessage()], 500);
    //     }
    // }

    public function addSupply(Request $request, $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'datePeremption' => 'nullable|date',
        ]);
        try {
            $product = Product::findOrFail($id);
    
            $product->quantityBoite += $request->quantity;
    
            $warningMessage = null;

            $differenceInYears = Configuration::where('key', 'differenceInYears')->value('value') ?? 1;

            $daysThreshold = $differenceInYears * 365;
            $expirationDate = Carbon::parse($request->datePeremption);
    
            if ($request->has('datePeremption')) {
                $product->datePeremption = $request->datePeremption;
    
                $currentDate = Carbon::now();
                $diffInDays = $currentDate->diffInDays($expirationDate);
    
                if ($diffInDays <= $daysThreshold) {
                    $warningMessage = "Veuillez mettre directement l'article sur votre étagère car sa date de péremption est proche";
                }
            }
    
            $product->save();
    
            $enter = Enter::create([
                'dateEntrer' => now(),
                'user_id' => auth()->id()
            ]);
    
            $enter->products()->attach($product->id, [
                'quantityEnter' => $request->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            $response = [
                'data' => true,
                'message' => 'Approvisionnement ajouté avec succès',
            ];
    
            if ($warningMessage) {
                $response['warning'] = $warningMessage;
            }
    
            return response()->json($response);
    
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }  

    public function listSupplies(): JsonResponse
    {
        try {
            $supplies = Enter::with(['products' => function ($query) {
                $query->select(
                    'products.id as product_id',
                    'products.name',
                    'products.quantityBoite',
                    'products.quantityPlaquette',
                    'products.quantityGellule',
                    'products.priceBoite',
                    'products.pricePlaquette',
                    'products.priceGellule',
                    'products.numberPlaquette',
                    'products.numberGellule',
                    'products.datePeremption',
                    'products.reference',
                    'products.libele',
                    'products.type'
                )->withPivot('quantityEnter');
            }])->get()->map(function ($enter) {
                return [
                    'id' => $enter->id,
                    'dateEntrer' => $enter->dateEntrer,
                    'user_id' => $enter->user_id,
                    'products' => $enter->products->map(function ($product) use ($enter) {
                        return [
                            'id' => $product->product_id,
                            'name' => $product->name,
                            'quantityBoite' => $product->quantityBoite,
                            'quantityPlaquette' => $product->quantityPlaquette,
                            'quantityGellule' => $product->quantityGellule,
                            'priceBoite' => $product->priceBoite,
                            'pricePlaquette' => $product->pricePlaquette,
                            'priceGellule' => $product->priceGellule,
                            'numberPlaquette' => $product->numberPlaquette,
                            'numberGellule' => $product->numberGellule,
                            'datePeremption' => $product->datePeremption,
                            'reference' => $product->reference,
                            'libele' => $product->libele,
                            'type' => $product->type,
                            'pivot' => [
                                'enter_id' => $enter->id,
                                'product_id' => $product->product_id,
                                'quantityEnter' => $product->pivot->quantityEnter,
                            ],
                        ];
                    }),
                ];
            });

            return response()->json(['data' => $supplies, 'message' => 'Liste des approvisionnements récupérée avec succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function setDifferenceInYears(Request $request): JsonResponse
    {
        $request->validate([
            'differenceInYears' => 'required|integer|min:1'
        ]);

        try {       
            if (!auth()->check()) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }
    
            Configuration::where('key', 'differenceInYears')->delete();
    
            $config = Configuration::create([
                'key' => 'differenceInYears',
                'value' => $request->input('differenceInYears'),
                'user_id' => auth()->id()
            ]);
    
            return response()->json(['data' => $config, 'message' => 'Paramètre mis à jour avec succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function deleteSupply($id): JsonResponse
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }
    
            $enter = Enter::findOrFail($id);
    
            $enter->products()->detach();
    
            $enter->delete();
    
            return response()->json(['message' => 'Approvisionnement supprimé avec succès'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
      

}