<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterGarageRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Utils\ErrorUtil;
use App\Models\Garage;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ErrorUtil;
     /**
        *
     * @OA\Post(
     *      path="/v1.0/register",
     *      operationId="register",
     *      tags={"auth"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user",
     *      description="This method is to store user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode"},
     *             @OA\Property(property="first_Name", type="string", format="string",example="How was this?"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="How was this?"),
     *            @OA\Property(property="email", type="string", format="string",example="How was this?"),

     * *  @OA\Property(property="password", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="password_confirmation", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="phone", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="address_line_1", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="address_line_2", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="country", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="city", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="postcode", type="boolean", format="boolean",example="1"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function register(AuthRegisterRequest $request)
    {
        try{
            $insertableData = $request->validated();

            $insertableData['password'] = Hash::make($request['password']);
            $insertableData['remember_token'] = Str::random(10);
            $user =  User::create($insertableData);
            // $user->assignRole("system user");

            $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $user->permissions = $user->getAllPermissions()->pluck('name');
            $user->roles = $user->roles->pluck('name');
            $user->permissions  = $user->getAllPermissions()->pluck('name');
            // $data["user"] = $user;
            // $data["permissions"]  = $user->getAllPermissions()->pluck('name');
            // $data["roles"] = $user->roles->pluck('name');
            // $data["token"] = $token;
            return response($user, 201);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }


    }




    /**
        *
     * @OA\Post(
     *      path="/v1.0/login",
     *      operationId="login",
     *      tags={"auth"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to login user",
     *      description="This method is to login user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email","password"},
     *            @OA\Property(property="email", type="string", format="string",example="admin@gmail.com"),

     * *  @OA\Property(property="password", type="boolean", format="boolean",example="12345678"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
public function login(Request $request) {


    try{
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Credentials'], 401);
        }

        $user = auth()->user();
        $user->token = auth()->user()->createToken('authToken')->accessToken;
        $user->permissions = $user->getAllPermissions()->pluck('name');
        $user->roles = $user->roles->pluck('name');

        return response()->json(['data' => $user,   "ok" => true], 200);
    } catch(Exception $e){

    return $this->sendError($e,500);
    }




}

/**
        *
     * @OA\Post(
     *      path="/v1.0/auth/user-register-with-garage",
     *      operationId="registerUserWithGarageClient",
     *      tags={"auth"},
     *      summary="This method is to store user with garage client side",
     *      description="This method is to store user with garage client side",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","garage","service"},
     *             @OA\Property(property="user", type="string", format="array",example={
     * "first_Name":"Rifat",
     * "last_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *
     * }),
     *
     *
     *
     *   *  @OA\Property(property="garage", type="string", format="array",example={
     * "name":"ABCD Garage",
     * "about":"Best Garage in Dhaka",
     * "web_page":"https://www.facebook.com/",
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  "is_mobile_garage":true,
     *  "wifi_available":true,
     *  "labour_rate":500,
     *  "average_time_slot":90
     *
     * }),
     *
     *   *  @OA\Property(property="service", type="string", format="array",example={
     *{

     *"automobile_category_id":1,
     *"services":{
     *{
         *"id":1,
        *"checked":true,
      *  "sub_services":{{"id":1,"checked":true},{"id":2,"checked":false}}
      * }
     *},
       *"automobile_makes":{
     *{
         *"id":1,
        *"checked":true,
      *  "models":{{"id":1,"checked":true},{"id":2,"checked":false}}
      * }
     *}
     *

    *}

     * }),
     *
     *
     *
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function registerUserWithGarageClient(AuthRegisterGarageRequest $request) {

        try{

return DB::transaction(function () use(&$request) {
    $insertableData = $request->validated();
        $insertableData['user']['password'] = Hash::make($insertableData['user']['password']);
        $insertableData['user']['remember_token'] = Str::random(10);
        $insertableData['user']['is_acrive'] = true;

        $user =  User::create($insertableData['user']);
        // $user->assignRole("system user");
        $user->assignRole('garage_owner');
        $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;

        
        $user->permissions = $user->getAllPermissions()->pluck('name');
        $user->roles = $user->roles->pluck('name');





        $insertableData['garage']['status'] = "pending";
        $insertableData['garage']['owner_id'] = $user->id;
        $garage =  Garage::create($insertableData['garage']);
        return response([
            "user" => $user,
            "garage" => $garage
        ], 201);
});


        } catch(Exception $e){

        return $this->sendError($e,500);
        }


    }

}
