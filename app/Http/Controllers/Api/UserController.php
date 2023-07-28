<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return  response()->json(User::with('role')->paginate(10));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = Role::findOrFail($data['role_id']);
        if (!$role) {
            return response()->json(['error' => true]);
        }
        $data['password'] =  Hash::make($data['password']);
        $user = User::create([
            'role' => $data['role'],
            'name' => $data['name'],
            'role_id' => $role->id,
            'password' => $data['password']
        ]);
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
            $user->update([
                'name' => $request->name,
                'role' => $request->role['type'],
                'role_id' => $request->role['id']
            ]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function item()
    {
        $users = User::limit(10)->get();
        return UserResource::collection($users);
    }
}
