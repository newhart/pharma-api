<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
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

        Setting::where('type', $request->type)->delete();

        $user = User::findOrFail($request->user()->id);

        $setting = new Setting();
        $setting->limit = $request->limit;
        $setting->type = $request->type;
        $setting->color = $request->color;
        $setting->user_id = $user->id;


        if ($request->hasFile('logo')) {
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

        $userId = auth()->id();

        $setting = Setting::where('user_id', $userId)->first();

        if (!$setting) {
            $setting = new Setting();
            $setting->user_id = $userId;
        }

        if ($request->hasFile('logo')) {
            if ($setting && $setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $setting->logo = $logoPath;
        }

        // Mettre à jour les autres champs
        $setting->nomEntreprise = $request->input('nomEntreprise', $setting->nomEntreprise);
        $setting->nif = $request->input('nif', $setting->nif);
        $setting->stat = $request->input('stat', $setting->stat);
        $setting->mail = $request->input('mail', $setting->mail);
        $setting->tel = $request->input('tel', $setting->tel);
    
        // Sauvegarde de l'enregistrement
        $setting->save();

        return response()->json(['message' => 'Logo et autres paramètres mis à jour avec succès', 
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
        $setting = Setting::first();

        if ($setting && $setting->logo) {
            Storage::disk('public')->delete($setting->logo);
            $setting->logo = null;
            $setting->save();
            return response()->json(['message' => 'Logo supprimé avec succès'], 200);
        }

        return response()->json(['message' => 'Aucun logo trouvé'], 404);
    }

    public function listLogos(): JsonResponse
    {
        $setting = Setting::first();

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

        return response()->json(['message' => 'Aucun logo trouvé'], 404);
    }

    public function updateColor(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:detailler,semiDetailler,nonDetailler',
            'color' => 'required|string|size:7',
        ]);

        $type = $request->input('type');
        $color = $request->input('color');

        $existingSetting = Setting::where('type', $type)->first();

        if ($existingSetting) {
            $existingSetting->color = $color;
            $existingSetting->save();

            Setting::where('type', $type)->where('id', '!=', $existingSetting->id)->delete();
        } else {
            $setting = new Setting();
            $setting->type = $type;
            $setting->color = $color;
            $setting->user_id = $request->user()->id;
            $setting->save();
        }

        return response()->json(['message' => 'Couleur mis à jour avec succès', 'data' => $existingSetting ?? $setting], 200);
    }


    public function deleteColor(): JsonResponse
    {
        $setting = Setting::whereNotNull('color')->first();

        if ($setting) {
            $setting->color = null;
            $setting->type = null;
            $setting->save();

            return response()->json(['message' => 'Couleur supprimés avec succès'], 200);
        }

        return response()->json(['message' => 'Aucun paramètre de couleur trouvé'], 404);
    }

    public function listSettings(): JsonResponse
    {
        $settings = Setting::all();

        return response()->json($settings, 200);
    }

    public function currentDate(): JsonResponse
    {
        $currentDate = Carbon::now()->locale('fr');
        $formattedDate = $currentDate->isoFormat('dddd D MMMM YYYY');

        return response()->json([
            'formatted_date' => $formattedDate,
            'time' => $currentDate->toTimeString(),
        ], 200);
    }
}
