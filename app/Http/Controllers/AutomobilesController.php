<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutomobileCategoryCreateRequest;
use App\Http\Requests\AutomobileCategoryUpdateRequest;
use App\Http\Requests\AutomobileMakeCreateRequest;
use App\Http\Requests\AutomobileMakeUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Models\AutomobileCategory;
use App\Models\AutomobileMake;
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



                $automobile  =  tap(AutomobileCategory::where(["id" => $updatableData["id"]]))->update(collect($updatableData)->only([
                    'name',
                ])->toArray()
                )
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

            $automobilesQuery = AutomobileCategory::with("makes");

            if(!empty($request->search_key)) {
                $automobilesQuery = $automobilesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
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
     * @OA\Get(
     *      path="/v1.0/automobile-categories/single/{id}",
     *      operationId="getAutomobileCategoryById",
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
     *      summary="This method is to get automobile category by id",
     *      description="This method is to get automobile category by id",
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

    public function getAutomobileCategoryById($id,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('automobile_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $automobileCategory = AutomobileCategory::with("makes")
            ->where([
                "id" => $id
            ])
            ->first()
            ;

            return response()->json($automobileCategory, 200);
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


/**
        *
     * @OA\Post(
     *      path="/v1.0/automobile-makes",
     *      operationId="createAutomobileMake",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store automobile make",
     *      description="This method is to store automobile make",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","description","automobile_category_id"},
     *             @OA\Property(property="name", type="string", format="string",example="car"),
     *              @OA\Property(property="description", type="string", format="string",example="car"),
     *  *              @OA\Property(property="automobile_category_id", type="string", format="number",example="1"),
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

    public function createAutomobileMake(AutomobileMakeCreateRequest $request)
    {
        try{
            if(!$request->user()->hasPermissionTo('automobile_create')){
                 return response()->json([
                    "message" => "You can not perform this action"
                 ],401);
            }

            $insertableData = $request->validated();


            $automobile =  AutomobileMake::create($insertableData);


            return response($automobile, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }
 /**
        *
     * @OA\Put(
     *      path="/v1.0/automobile-makes",
     *      operationId="updateAutomobileMake",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update automobile make",
     *      description="This method is to update automobile make",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name","description"},
     *             @OA\Property(property="id", type="number", format="number",example="1"),
     *             @OA\Property(property="name", type="string", format="string",example="car"),
     *             @OA\Property(property="description", type="string", format="string",example="description"),
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

    public function updateAutomobileMake(AutomobileMakeUpdateRequest $request)
    {

        try{
            if(!$request->user()->hasPermissionTo('automobile_update')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            $updatableData = $request->validated();



                $automobile  =  tap(AutomobileMake::where(["id" => $updatableData["id"]]))->update(collect($updatableData)->only([
                    'name',
                    "description"
                ])->toArray()
                )
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
     *      path="/v1.0/automobile-makes/{categoryId}/{perPage}",
     *      operationId="getAutomobileMakes",
     *      tags={"automobile_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),
     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to get automobile makes by category id",
     *      description="This method is to get automobile makes by category id",
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

    public function getAutomobileMakes($categoryId,$perPage,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('automobile_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            // $automobilesQuery = AutomobileMake::with("makes");

            $automobilesQuery = AutomobileMake::with("category")
            ->where([
                "automobile_category_id" => $categoryId
            ]);

            if(!empty($request->search_key)) {
                $automobilesQuery = $automobilesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                });

            }

            if(!empty($request->start_date) && !empty($request->end_date)) {
                $automobilesQuery = $automobilesQuery->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);

            }

            $makes = $automobilesQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($makes, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }

/**
        *
     * @OA\Delete(
     *      path="/v1.0/automobile-makes/{id}",
     *      operationId="deleteAutomobileMakeById",
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
     *      summary="This method is to delete automobile make by id",
     *      description="This method is to delete automobile make by id",
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

    public function deleteAutomobileMakeById($id,Request $request) {

        try{
            if(!$request->user()->hasPermissionTo('automobile_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           AutomobileMake::where([
            "id" => $id
           ])
           ->delete();

            return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }


}
