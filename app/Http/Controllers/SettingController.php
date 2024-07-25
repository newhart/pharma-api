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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $setting = Setting::first(); // Supposer qu'il y a un seul enregistrement de paramètres

        if (!$setting) {
            $setting = new Setting();
        }

        $user = User::findOrFail($request->user()->id);

        $setting->limit = $request->limit;
        $setting->type = $request->type;
        $setting->color = $request->color;
        $setting->user_id = $user->id;

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $setting->logo = $logoPath;
        }

        $setting->save();

        return response()->json(['success' => true, 'data' => $setting], 200);
    }

    public function updateLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $setting = Setting::first(); // Supposer qu'il y a un seul enregistrement de paramètres

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($setting && $setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            if ($setting) {
                $setting->logo = $logoPath;
                $setting->save();
            } else {
                // Créer un nouvel enregistrement si aucun n'existe
                Setting::create(['logo' => $logoPath]);
            }
        }

        return response()->json(['message' => 'Logo mis à jour avec succès', 'logo' => $logoPath], 200);
    }

    public function deleteLogo(): JsonResponse
    {
        $setting = Setting::first(); // Supposer qu'il y a un seul enregistrement de paramètres

        if ($setting && $setting->logo) {
            Storage::disk('public')->delete($setting->logo);
            $setting->logo = null;
            $setting->save();
            return response()->json(['message' => 'Logo supprimé avec succès'], 200);
        }

        return response()->json(['message' => 'No logo found to delete'], 404);
    }

    public function listLogos(): JsonResponse
    {
        $setting = Setting::first(); // Supposer qu'il y a un seul enregistrement de paramètres

        if ($setting && $setting->logo) {
            $logoUrl = Storage::url($setting->logo);
            return response()->json(['logo_url' => $logoUrl], 200);
        }

        return response()->json(['message' => 'No logo found'], 404);
    }
}
