<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = UserResource::collection(User::all());
        return response()->json($users);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] =  Hash::make($data['password']);
        $user = User::create($data);
        if ($user) {
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => true]);
    }

    public function destroy(Request $request,  User $user)
    {
        if ($request->user()->can('delete', $user)) {
            if ($user) $user->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => true]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->user()->can('update', $user)) {
            $user->update($request->all());
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => true]);
    }
}
