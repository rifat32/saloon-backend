<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Requests\RoleUpdateRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function createRole(RoleRequest $request)
    {
        $insertableData = $request->validated();
        $role = Role::create($insertableData);

        $role->syncPermissions($insertableData["permissions"]);

        return response()->json([
            "role" =>  $role,
        ], 201);
    }

    public function updateRole(RoleUpdateRequest $request) {
        $updatableData = $request->validated();
        $role = Role::where(["id" => $updatableData["id"]])->first();
        $role->syncPermissions($updatableData["permissions"]);


        return response()->json([
            "role" =>  $role,
        ], 201);
    }
    public function getRoles(Request $request)
    {
        $roles = Role::orderByDesc("id")->paginate(10);

        return response()->json([
            "roles" => $roles,
        ], 200);
    }
    public function getRolesAll(Request $request)
    {
        $roles = Role::with('permissions:name,id')->select("name", "id")->get();
        return response()->json([
            "roles" => $roles,
        ], 200);
    }
}
