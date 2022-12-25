<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetUpController extends Controller
{
    public function setUp(Request $request)
    {
        // @@@@@@@@@@@@@@@@@@@
        // clear everything
        // @@@@@@@@@@@@@@@@@@@
        Artisan::call('optimize:clear');
        Artisan::call('migrate:fresh');
        Artisan::call('passport:install');
        Artisan::call('l5-swagger:generate');


        // shell_exec('php ../artisan optimize:clear');
        // shell_exec('php ../artisan migrate:fresh');
        // shell_exec('php ../artisan passport:install');
        // shell_exec('php ../artisan l5-swagger:generate');
        // ##########################################
        // user
        // #########################################
      $admin =  User::create([
        'first_Name' => "super",
        'last_Name'=> "admin",
        'phone'=> "01771034383",
        'address_line_1',
        'address_line_2',
        'country'=> "Bangladesh",
        'city'=> "Dhaka",
        'postcode'=> "1207",
        'email'=> "admin@gmail.com",
        'password'=>Hash::make("12345678"),
        'is_active' => 1
        ]);

        // ###############################
        // permissions
        // ###############################
        $permissions =  config("setup-config.permissions");
        // setup permissions
        foreach ($permissions as $permission) {
            if(!Permission::where([
            'name' => $permission
            ])
            ->exists()){
                Permission::create(['guard_name' => 'api', 'name' => $permission]);
            }

        }
        // setup roles
        $roles = config("setup-config.roles");
        foreach ($roles as $role) {
            if(!Role::where([
            'name' => $role
            ])
            ->exists()){
             Role::create(['guard_name' => 'api', 'name' => $role]);
            }

        }

        // setup roles and permissions
        $role_permissions = config("setup-config.roles_permission");
        foreach ($role_permissions as $role_permission) {
            $role = Role::where(["name" => $role_permission["role"]])->first();
            error_log($role_permission["role"]);
            $permissions = $role_permission["permissions"];
            foreach ($permissions as $permission) {
                if(!$role->hasPermissionTo($permission)){
                    $role->givePermissionTo($permission);
                }


            }
        }
        $admin->assignRole("superadmin");

        return "You are done with setup";
    }
}
