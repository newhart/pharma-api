<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
