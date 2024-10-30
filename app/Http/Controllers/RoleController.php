<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return  response()->json(Role::with('menus')->limit(5)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['type' => 'required']);
        if ($request->id) {
            $role = Role::findOrFail($request->id);
        } else {
            $role = new Role();
        }
        // create or update the role 
        $role->type = $request->type;
        $role->save();
        $menu_ids = [];
        foreach ($request->menus as $menu) {
            $menuValid = Menu::findOrFail($menu['id']);
            if ($menuValid) {
                $menu_ids[] = $menuValid->id;
            }
        }
        $role->menus()->attach($menu_ids);
        return response()->json(['success' => true]);
    }

    public function destroy($id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $role->menus()->detach();
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
    }

}
