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
        'type' => 'required|string|in:detailler,semiDetailler,nonDetailler',
        'color' => 'required|string|size:7',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

    ]);

    // Supprimer les anciens paramètres avec le même type
    Setting::where('type', $request->type)->delete();

    $user = User::findOrFail($request->user()->id);

    $setting = new Setting();
    $setting->limit = $request->limit;
    $setting->type = $request->type;
    $setting->color = $request->color;
    $setting->user_id = $user->id;
    // $setting->nomEntreprise = $user->nomEntreprise;
    // $setting->nif = $user->nif;
    // $setting->stat = $user->stat;
    // $setting->mail = $user->mail;
    // $setting->tel = $user->tel;


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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nomEntreprise' => 'nullable|string',
            'nif' => 'nullable|string',
            'stat' => 'nullable|string',
            'mail' => 'nullable|email',
            'tel' => 'nullable|string'
        ]);

        $setting = Setting::first(); 

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($setting && $setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $setting->logo = $logoPath;
        }

        // Mettre à jour les autres champs
        if ($request->has('nomEntreprise')) {
            $setting->nomEntreprise = $request->input('nomEntreprise');
        }
        if ($request->has('nif')) {
            $setting->nif = $request->input('nif');
        }
        if ($request->has('stat')) {
            $setting->stat = $request->input('stat');
        }
        if ($request->has('mail')) {
            $setting->mail = $request->input('mail');
        }
        if ($request->has('tel')) {
            $setting->tel = $request->input('tel');
        }

        if ($setting) {
            $setting->save();
        } else {
            // Créer un nouvel enregistrement si aucun n'existe
            Setting::create($request->all());
        }

        return response()->json(['message' => 'Logoo et autres paramètres mis à jour avec succès', 
        'logo' => $logoPath ?? $setting->logo, 
        'nomEntreprise' =>$setting->nomEntreprise,
        'nif' => $setting->nif,
        'stat' => $setting->stat,
        'mail' => $setting->mail,
        'tel' => $setting->tel
     ], 200);
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
            return response()->json(['logo_url' => $logoUrl, 
                                    'nomEntreprise' =>$setting->nomEntreprise,
                                    'nif' => $setting->nif,
                                    'stat' => $setting->stat,
                                    'mail' => $setting->mail,
                                    'tel' => $setting->tel
                                    ], 200);
        }

        return response()->json(['message' => 'No logo found'], 404);
    }

    public function updateColor(Request $request): JsonResponse
{
    $request->validate([
        'type' => 'required|string|in:detailler,semiDetailler,nonDetailler',
        'color' => 'required|string|size:7',
    ]);

    $type = $request->input('type');
    $color = $request->input('color');

    // Chercher l'entrée existante avec le même type
    $existingSetting = Setting::where('type', $type)->first();

    if ($existingSetting) {
        // Si l'entrée existe, mettre à jour la couleur
        $existingSetting->color = $color;
        $existingSetting->save();

        // Supprimer les autres enregistrements avec le même type
        Setting::where('type', $type)->where('id', '!=', $existingSetting->id)->delete();
    } else {
        // Si l'entrée n'existe pas, en créer une nouvelle
        $setting = new Setting();
        $setting->type = $type;
        $setting->color = $color;
        $setting->user_id = $request->user()->id; // Assurez-vous que l'utilisateur est authentifié
        $setting->save();
    }

    return response()->json(['message' => 'Color setting updated successfully', 'data' => $existingSetting ?? $setting], 200);
}


    public function deleteColor(): JsonResponse
    {
        $setting = Setting::whereNotNull('color')->first(); // Trouver un enregistrement avec une couleur

        if ($setting) {
            $setting->color = null;
            $setting->type = null;
            $setting->save();

            return response()->json(['message' => 'Color and type deleted successfully'], 200);
        }

        return response()->json(['message' => 'No color setting found to delete'], 404);
    }

    public function listSettings(): JsonResponse
    {
        $settings = Setting::all(); // Récupère tous les enregistrements de paramètres

        return response()->json($settings, 200);
    }
}
