<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function getUsers($perPage,Request $request) {
        $users = User::with("roles")
        ->whereHas('roles', function ($query) {
            return $query->where('name','!=', 'customer');
        })
        ->orderByDesc("id")->paginate($perPage);
        return response()->json($users, 200);
    }
}
