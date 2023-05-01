<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCreateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\ShopUtil;
use App\Models\Product;
use App\Models\ProductVariation;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ErrorUtil, ShopUtil;
     /**
     *
  * @OA\Post(
  *      path="/v1.0/products",
  *      operationId="createProduct",
  *      tags={"shop_section.product_management"},
 *       security={
  *           {"bearerAuth": {}}
  *       },
  *      summary="This method is to store product",
  *      description="This method is to store product",
  *
  *  @OA\RequestBody(
  *         required=true,
  *         @OA\JsonContent(
  *            required={"name","icon","description"},
  *    @OA\Property(property="name", type="string", format="string",example="car"),
  *  *    @OA\Property(property="icon", type="string", format="string",example="fa fa tui halua kha"),
  *    @OA\Property(property="description", type="string", format="string",example="car"),


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

 public function createProduct(ProductCreateRequest $request)
 {
     try{


         if(!$request->user()->hasPermissionTo('product_create')){
              return response()->json([
                 "message" => "You can not perform this action"
              ],401);
         }
         $insertableData = $request->validated();
         $sku_prefix = "";
// shop id is required if user is not super admin od data collector. and shop id means it is spacific to shop. so it is not default
         if(!empty($insertableData["shop_id"])) {
            $insertableData["is_default"] = false;
        $shop =   $this->shopOwnerCheck($insertableData["shop_id"]);
            if (!$shop) {
                return response()->json([
                    "message" => "you are not the owner of the shop or the requested shop does not exist."
                ], 401);
            }
            $sku_prefix = $shop->sku_prefix;
         } else {
            $insertableData["is_default"] = true;
         }




         $product =  Product::create($insertableData);
         if(!$product->sku){
            $product->sku = $sku_prefix .  str_pad($product->id, 4, '0', STR_PAD_LEFT);
        }
        $product->save();

         if($product->type = "single"){

            $product->product_variations()->create([

                    "sub_sku" => $product->sku,
                    "quantity" => $insertableData["quantity"],
                    "price" => $insertableData["price"],
                    "automobile_make_id" => NULL,


            ]);
         } else {
            foreach($insertableData["product_variations"] as $product_variation) {
                $c = ProductVariation::withTrashed()
                ->where('product_id', $product->id)
                ->count() + 1;

                $product->product_variations()->create([

                    "sub_sku" => $product->sku . "-" . $c,
                    "quantity" => $product_variation["quantity"],
                    "price" => $product_variation["price"],
                    "automobile_make_id" => $product_variation["automobile_make_id"],


            ]);

            }




         }



         return response($product, 201);
     } catch(Exception $e){
         error_log($e->getMessage());
     return $this->sendError($e,500);
     }
 }
}
