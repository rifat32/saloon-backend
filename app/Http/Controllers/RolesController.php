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
        if( !$request->user()->hasPermissionTo('role_create'))
         {

            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }
        $insertableData = $request->validated();
        $role = Role::create(["name"=>$insertableData["name"]]);

        $role->syncPermissions($insertableData["permissions"]);

        return response()->json([
            "role" =>  $role,
        ], 201);
    }

    public function updateRole(RoleUpdateRequest $request) {
        if( !$request->user()->hasPermissionTo('role_update') )
        {

           return response()->json([
              "message" => "You can not perform this action"
           ],401);
      }
        $updatableData = $request->validated();
        $role = Role::where(["id" => $updatableData["id"]])->first();
        if($role->name == "superadmin" )
        {

           return response()->json([
              "message" => "You can not perform this action"
           ],401);
      }

        $role->syncPermissions($updatableData["permissions"]);


        return response()->json([
            "role" =>  $role,
        ], 201);
    }
    public function getRoles($perPage,Request $request)
    {
        if(!$request->user()->hasPermissionTo('role_view')){
            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }

        $rolesQuery =   Role::with('permissions:name,id');

        if(!empty($request->search_key)) {
            $rolesQuery = $rolesQuery->where(function($query) use ($request){
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });

        }



        $roles = $rolesQuery->orderByDesc("id")->paginate($perPage);
        return response()->json($roles, 200);

    }
    public function getRolesAll(Request $request)
    {
        $roles = Role::with('permissions:name,id')->select("name", "id")->get();
        return response()->json([
            "roles" => $roles,
        ], 200);
    }
    public function getRoleById($id,Request $request) {

        $role = Role::with('permissions:name,id')
        ->where(["id" => $id])
        ->select("name", "id")->get();
        return response()->json($role, 200);
    }
    public function deleteRoleById($id,Request $request) {

        $initial_roles = config("setup-config.roles");

       $role = Role::where([
            "id" => $id
           ])->first();

        if(
            !$request->user()->hasPermissionTo('role_delete')
            ||
            in_array($role->name, $initial_roles)
            )
            {

            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }

       Role::where([
        "id" => $id
       ])
       ->delete();

        return response()->json(["ok" => true], 200);
    }



    public function getInitialRolePermissions (Request $request) {
        if(!$request->user()->hasPermissionTo('role_view')){
            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }

       $role_permissions = config("setup-config.roles_permission");

       return response()->json($role_permissions,200);
    }

}
