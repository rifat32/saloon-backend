<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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
     *            required={"Name","Address","PostCode","enable_question"},
     *             @OA\Property(property="Name", type="string", format="string",example="How was this?"),
     *            @OA\Property(property="Address", type="string", format="string",example="How was this?"),
     *            @OA\Property(property="PostCode", type="string", format="string",example="How was this?"),

     * *  @OA\Property(property="enable_question", type="boolean", format="boolean",example="1"),
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

    public function register(AuthRegisterRequest $request)
    {

        $insertableData = $request->validated();

        $insertableData['password'] = Hash::make($request['password']);
        $insertableData['remember_token'] = Str::random(10);
        $user =  User::create($insertableData);
        // $user->assignRole("system user");

        $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $user->permissions = $user->getAllPermissions()->pluck('name');
        $user->roles = $user->roles->pluck('name');
        // $data["user"] = $user;
        // $data["permissions"]  = $user->getAllPermissions()->pluck('name');
        // $data["roles"] = $user->roles->pluck('name');
        // $data["token"] = $token;
        return response($user, 200);
    }
}
