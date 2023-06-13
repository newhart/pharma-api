<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogoutUserController extends Controller
{
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
