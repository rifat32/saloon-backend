<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomobilesController;
use App\Http\Controllers\GaragesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/v1.0/register', [AuthController::class, "register"]);
Route::post('/v1.0/login', [AuthController::class, "login"]);


Route::post('/v1.0/auth/user-register-with-garage', [AuthController::class, "registerUserWithGarageClient"]);
Route::get('/v1.0/automobile-categories/get/all', [AutomobilesController::class, "getAllAutomobileCategories"]);
Route::get('/v1.0/automobile-makes-all/{categoryId}', [AutomobilesController::class, "getAutomobileMakesAll"]);
Route::get('/v1.0/services-all/{categoryId}', [ServiceController::class, "getAllServicesByCategoryId"]);





Route::middleware('auth:api')->get('/v1.0/user', function (Request $request) {
    $user = $request->user();



    $user->token = auth()->user()->createToken('authToken')->accessToken;
    $user->permissions = $user->getAllPermissions()->pluck('name');
    $user->roles = $user->roles->pluck('name');

    return response()->json(
        $user,
        200
    );
});



// ############################################
// Protected Routes
// ############################################
Route::middleware(['auth:api'])->group(function () {


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// user management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/users', [UserManagementController::class, "createUser"]);
Route::put('/v1.0/users', [UserManagementController::class, "updateUser"]);
Route::get('/v1.0/users/{perPage}', [UserManagementController::class, "getUsers"]);
Route::delete('/v1.0/users/{id}', [UserManagementController::class, "deleteUserById"]);


Route::get('/v1.0/initial-role-permissions', [RolesController::class, "getInitialRolePermissions"]);
Route::post('/v1.0/roles', [RolesController::class, "createRole"]);
Route::put('/v1.0/roles', [RolesController::class, "updateRole"]);
Route::get('/v1.0/roles/{perPage}', [RolesController::class, "getRoles"]);
Route::get('/v1.0/roles/get/all', [RolesController::class, "getRolesAll"]);
Route::get('/v1.0/roles/get-by-id/{id}', [RolesController::class, "getRoleById"]);
Route::delete('/v1.0/roles/{id}', [RolesController::class, "deleteRoleById"]);


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// garage management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/auth/register-with-garage', [GaragesController::class, "registerUserWithGarage"]);
Route::get('/v1.0/garages/{perPage}', [GaragesController::class, "getGarages"]);
Route::delete('/v1.0/garages/{id}', [GaragesController::class, "deleteGarageById"]);





// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// automobile management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/automobile-categories', [AutomobilesController::class, "createAutomobileCategory"]);
Route::put('/v1.0/automobile-categories', [AutomobilesController::class, "updateAutomobileCategory"]);
Route::get('/v1.0/automobile-categories/{perPage}', [AutomobilesController::class, "getAutomobileCategories"]);

Route::get('/v1.0/automobile-categories/single/get/{id}', [AutomobilesController::class, "getAutomobileCategoryById"]);
Route::delete('/v1.0/automobile-categories/{id}', [AutomobilesController::class, "deleteAutomobileCategoryById"]);



Route::post('/v1.0/automobile-makes', [AutomobilesController::class, "createAutomobileMake"]);
Route::put('/v1.0/automobile-makes', [AutomobilesController::class, "updateAutomobileMake"]);
Route::get('/v1.0/automobile-makes/{categoryId}/{perPage}', [AutomobilesController::class, "getAutomobileMakes"]);



Route::get('/v1.0/automobile-makes/single/get/{id}', [AutomobilesController::class, "getAutomobileMakeById"]);
Route::delete('/v1.0/automobile-makes/{id}', [AutomobilesController::class, "deleteAutomobileMakeById"]);





Route::post('/v1.0/automobile-models', [AutomobilesController::class, "createAutomobileModel"]);
Route::put('/v1.0/automobile-models', [AutomobilesController::class, "updateAutomobileModel"]);
Route::get('/v1.0/automobile-models/{makeId}/{perPage}', [AutomobilesController::class, "getAutomobileModel"]);
Route::get('/v1.0/automobile-models/single/get/{id}', [AutomobilesController::class, "getAutomobileModelById"]);
Route::delete('/v1.0/automobile-models/{id}', [AutomobilesController::class, "deleteAutomobileModelById"]);





Route::post('/v1.0/automobile-model-variants', [AutomobilesController::class, "createAutomobileModelVariant"]);
Route::put('/v1.0/automobile-model-variants', [AutomobilesController::class, "updateAutomobileModelVariant"]);
Route::get('/v1.0/automobile-model-variants/{modelId}/{perPage}', [AutomobilesController::class, "getAutomobileModelVariant"]);
Route::get('/v1.0/automobile-model-variants/single/get/{id}', [AutomobilesController::class, "getAutomobileModelVariantById"]);
Route::delete('/v1.0/automobile-model-variants/{id}', [AutomobilesController::class, "deleteAutomobileModelVariantById"]);



Route::post('/v1.0/automobile-fuel-types', [AutomobilesController::class, "createAutomobileFuelType"]);
Route::put('/v1.0/automobile-fuel-types', [AutomobilesController::class, "updateAutomobileFuelType"]);
Route::get('/v1.0/automobile-fuel-types/{modelVariantId}/{perPage}', [AutomobilesController::class, "getAutomobileFuelType"]);
Route::get('/v1.0/automobile-fuel-types/single/get/{id}', [AutomobilesController::class, "getAutomobileFuelTypeById"]);
Route::delete('/v1.0/automobile-fuel-types/{id}', [AutomobilesController::class, "deleteAutomobileFuelTypeById"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end automobile management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// service management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/services', [ServiceController::class, "createService"]);
Route::put('/v1.0/services', [ServiceController::class, "updateService"]);
Route::get('/v1.0/services/{perPage}', [ServiceController::class, "getServices"]);

Route::delete('/v1.0/services/{id}', [ServiceController::class, "deleteServiceById"]);
Route::get('/v1.0/services/single/get/{id}', [ServiceController::class, "getServiceById"]);


Route::post('/v1.0/sub-services', [ServiceController::class, "createSubService"]);
Route::put('/v1.0/sub-services', [ServiceController::class, "updateSubService"]);
Route::get('/v1.0/sub-services/{serviceId}/{perPage}', [ServiceController::class, "getSubServicesByServiceId"]);
Route::get('/v1.0/sub-services-all/{serviceId}', [ServiceController::class, "getAllSubServicesByServiceId"]);
Route::delete('/v1.0/sub-services/{id}', [ServiceController::class, "deleteSubServiceById"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// service management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


});

