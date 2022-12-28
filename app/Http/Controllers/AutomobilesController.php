<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutomobileCategoryCreateRequest;
use App\Http\Requests\AutomobileCategoryUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Models\AutomobileCategory;
use Exception;
use Illuminate\Http\Request;

class AutomobilesController extends Controller
{
    use ErrorUtil;
   /**
        *
     * @OA\Post(
     *      path="/v1.0/automobile-categories",
     *      operationId="createAutomobileCategory",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store automobile category",
     *      description="This method is to store automobile category",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name"},
     *             @OA\Property(property="name", type="string", format="string",example="car"),
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

    public function createAutomobileCategory(AutomobileCategoryCreateRequest $request)
    {

        try{
            if(!$request->user()->hasPermissionTo('automobile_create')){
                 return response()->json([
                    "message" => "You can not perform this action"
                 ],401);
            }

            $insertableData = $request->validated();


            $automobile =  AutomobileCategory::create($insertableData);


            return response($automobile, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }

     /**
        *
     * @OA\Put(
     *      path="/v1.0/automobile-categories",
     *      operationId="updateAutomobileCategory",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update automobile category",
     *      description="This method is to update automobile category",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name"},
     *             @OA\Property(property="id", type="number", format="number",example="1"),
     *             @OA\Property(property="name", type="string", format="string",example="car"),
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

    public function updateAutomobileCategory(AutomobileCategoryUpdateRequest $request)
    {

        try{
            if(!$request->user()->hasPermissionTo('automobile_update')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            $updatableData = $request->validated();


            $automobile  =  tap(AutomobileCategory::where(["id" => $updatableData["id"]]))->update()
                // ->with("somthing")
                ->first();


            return response($automobile, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }
     /**
        *
     * @OA\Get(
     *      path="/v1.0/automobile-categories/{perPage}",
     *      operationId="getAutomobileCategories",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to get automobile categories",
     *      description="This method is to get automobile categories",
     *

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

    public function getAutomobileCategories($perPage,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('automobile_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $automobilesQuery = AutomobileCategory::with("");

            if(!empty($request->search_key)) {
                $automobilesQuery = $automobilesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("first_Name", "like", "%" . $term . "%");
                    $query->orWhere("last_Name", "like", "%" . $term . "%");
                    $query->orWhere("email", "like", "%" . $term . "%");
                    $query->orWhere("phone", "like", "%" . $term . "%");
                });

            }

            if(!empty($request->start_date) && !empty($request->end_date)) {

                $automobilesQuery = $automobilesQuery->whereBetween('created_at', [

                    $request->start_date,
                    $request->end_date
                ]);

            }

            $users = $automobilesQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($users, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }


/**
        *
     * @OA\Delete(
     *      path="/v1.0/automobile-categories/{id}",
     *      operationId="deleteAutomobileCategoryById",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to delete automobile category by id",
     *      description="This method is to delete automobile category by id",
     *

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

    public function deleteAutomobileCategoryById($id,Request $request) {

        try{
            if(!$request->user()->hasPermissionTo('automobile_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           AutomobileCategory::where([
            "id" => $id
           ])
           ->delete();

            return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }

}
