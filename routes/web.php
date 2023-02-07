<?php

use App\Http\Controllers\SetUpController;
use App\Http\Controllers\SwaggerLoginController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/setup', [SetUpController::class, "setUp"])->name("setup");
Route::get('/setup2', [SetUpController::class, "setUp2"])->name("setup2");
Route::get('/swagger-refresh', [SetUpController::class, "swaggerRefresh"]);
Route::get('/automobile-refresh', [SetUpController::class, "automobileRefresh"]);

Route::get("/swagger-login",[SwaggerLoginController::class,"login"])->name("login.view");
Route::post("/swagger-login",[SwaggerLoginController::class,"passUser"]);






Route::get("/activate/{token}",function(Request $request,$token) {
    $user = User::where([
        "email_verify_token" => $token,
    ])
        ->where("email_verify_token_expires", ">", now())
        ->first();
    if (!$user) {
        return response()->json([
            "message" => "Invalid Url Or Url Expired"
        ], 400);
    }
    $user->email_verified_at = now();
    $user->save();
    return view("welcome-message");
});
