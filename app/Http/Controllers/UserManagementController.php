<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Utils\ErrorUtil;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    use ErrorUtil;
    /**
        *
     * @OA\Post(
     *      path="/v1.0/users",
     *      operationId="createUser",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user",
     *      description="This method is to store user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode","role"},
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
     *  *  * *  @OA\Property(property="role", type="boolean", format="boolean",example="customer"),
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

    public function createUser(UserCreateRequest $request)
    {

        try{

            $insertableData = $request->validated();

            $insertableData['password'] = Hash::make($request['password']);
            $insertableData['is_active'] = true;
            $insertableData['remember_token'] = Str::random(10);
            $user =  User::create($insertableData);

            $user->assignRole($insertableData['role']);

            // $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;


            $user->roles = $user->roles->pluck('name');

            // $user->permissions  = $user->getAllPermissions()->pluck('name');
            // error_log("cccccc");
            // $data["user"] = $user;
            // $data["permissions"]  = $user->getAllPermissions()->pluck('name');
            // $data["roles"] = $user->roles->pluck('name');
            // $data["token"] = $token;
            return response($user, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }






    public function getUsers($perPage,Request $request) {
        $usersQuery = User::with("roles")
        ->whereHas('roles', function ($query) {
            return $query->where('name','!=', 'customer');
        });

        if(!empty($request->search_key)) {
            $usersQuery = $usersQuery->where(function($query) use ($request){
                $term = $request->search_key;
                $query->where("first_Name", "like", "%" . $term . "%");
                $query->orWhere("last_Name", "like", "%" . $term . "%");
                $query->orWhere("email", "like", "%" . $term . "%");
                $query->orWhere("phone", "like", "%" . $term . "%");
            });
error_log("search......");
        }

        $users = $usersQuery->orderByDesc("id")->paginate($perPage);
        return response()->json($users, 200);
    }
}
