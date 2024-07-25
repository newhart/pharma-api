<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $setting = Setting::where('id', 1)->first();
        return response()->json($setting);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => ['required', 'integer'],
        ]);

        if ($request->id) {
            $setting = Setting::find($request->id);
        } else {
            $setting = new Setting();
        }

        $user = User::findOrFail($request->user()->id);

        $setting->limit = $request->limit;
        $setting->type = $request->type;
        $setting->color = $request->color;
        $setting->user_id =  $user->id;
        $setting->save();

        return response()->json(['success' => true, 'data' => $setting]);
    }

    // Méthode pour ajouter ou mettre à jour le logo
    public function updateLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $setting = Setting::first(); // Suppose qu'il y a un seul enregistrement de paramètres

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($setting->logo) {
                Storage::delete($setting->logo);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $setting->logo = $logoPath;
            $setting->save();
        }

        return response()->json(['message' => 'Logo mis à jour avec succès', 'logo' => $setting->logo], 200);
    }

    // Méthode pour supprimer le logo
    public function deleteLogo(): JsonResponse
    {
        $setting = Setting::first(); // Suppose qu'il y a un seul enregistrement de paramètres

        if ($setting->logo) {
            Storage::delete($setting->logo);
            $setting->logo = null;
            $setting->save();
        }

        return response()->json(['message' => 'Logo supprimé avec succès'], 200);
    }

    // Méthode pour récupérer la liste des logos
    public function listLogos(): JsonResponse
{
    $logos = \Storage::disk('public')->files('logos');

    return response()->json([
        'logos' => $logos
    ]);
}
}
