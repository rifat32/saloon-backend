<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\GuestUser;
use App\Models\Question;
use App\Models\QusetionStar;
use App\Models\ReviewNew;
use App\Models\ReviewValueNew;
use App\Models\Star;
use App\Models\StarTag;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
      /**
        *
     * @OA\Post(
     *      path="/review-new/create/questions",
     *      operationId="storeQuestion",
     *      tags={"review.setting.question"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store question",
     *      description="This method is to store question",
     *
     *  @OA\RequestBody(
     *  * description="supported value is of type is 'star','emoji','numbers','heart'",
     *         required=true,
     *         @OA\JsonContent(
     *            required={"question","garage_id","is_active"},
     *            @OA\Property(property="question", type="string", format="string",example="How was this?"),
     *  @OA\Property(property="garage_id", type="number", format="number",example="1"),
     * *  @OA\Property(property="is_active", type="boolean", format="boolean",example="1"),
     * * *  @OA\Property(property="type", type="string", format="string",example="star"),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */


    public function storeQuestion(Request $request)
    {

        $question = [
            'question' => $request->question,
            'garage_id' => $request->garage_id,
            'is_active' => $request->is_active,
            'type' => !empty($request->type)?$request->type:"star"
        ];
        if ($request->user()->hasRole("superadmin")) {
            $question["is_default"] = true;
            $question["garage_id"] = NULL;
        } else {

            $garage =    Garage::where(["id" => $request->garage_id,"owner_id" => $request->user()->id])->first();

            if(!$garage){
                return response()->json(["message" => "No garage Found"],400);
            }
            if ($garage->enable_question == true) {
                return response()->json(["message" => "question is enabled"],400);
            }
        }



        $createdQuestion =    Question::create($question);
        $createdQuestion->info = "supported value is of type is 'star','emoji','numbers','heart'";

        return response($createdQuestion, 201);
    }


      /**
        *
     * @OA\Put(
     *      path="/review-new/update/questions",
     *      operationId="updateQuestion",
     *      tags={"review.setting.question"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update question",
     *      description="This method is to update question",
     *
     *  @OA\RequestBody(
     * description="supported value is of type is 'star','emoji','numbers','heart'",
     *         required=true,
     *         @OA\JsonContent(
     *            required={"question","is_active","id"},
      *  @OA\Property(property="id", type="number", format="number",example="1"),
     *            @OA\Property(property="question", type="string", format="string",example="was it good?"),
     *  *            @OA\Property(property="type", type="string", format="string",example="star"),
     *

     *   @OA\Property(property="is_active", type="boolean", format="boolean",example="1"),
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function updateQuestion(Request $request)
    {
        $question = [
            'type' => $request->type,
            'question' => $request->question,
            "is_active"=>$request->is_active,
        ];
        $checkQuestion =    Question::where(["id" => $request->id])->first();
        if ($checkQuestion->is_default == true && !$request->user()->hasRole("superadmin")) {
            return response()->json(["message" => "you can not update the question. you are not a super admin"]);
        }
        $updatedQuestion =    tap(Question::where(["id" => $request->id]))->update(
            $question
        )
            // ->with("somthing")

            ->first();
            $updatedQuestion->info = "supported value is of type is 'star','emoji','numbers','heart'";

        return response($updatedQuestion, 200);
    }

/**
        *
     * @OA\Put(
     *      path="/review-new/update/active_state/questions",
     *      operationId="updateQuestionActiveState",
     *      tags={"review.setting.question"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update question's active state",
     *      description="This method is to update question's active state",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"is_active","id"},
      *  @OA\Property(property="id", type="number", format="number",example="1"),
     *   @OA\Property(property="is_active", type="boolean", format="boolean",example="1"),
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function updateQuestionActiveState(Request $request)
    {
        $question = [
            "is_active"=>$request->is_active,
        ];
        $checkQuestion =    Question::where(["id" => $request->id])->first();
        if ($checkQuestion->is_default == true && !$request->user()->hasRole("superadmin")) {
            return response()->json(["message" => "you can not update the question. you are not a super admin"]);
        }
        $updatedQuestion =    tap(Question::where(["id" => $request->id]))->update(
            $question
        )
            // ->with("somthing")

            ->first();


        return response($updatedQuestion, 200);
    }

    /**
        *
     * @OA\Get(
     *      path="/review-new/get/questions",
     *      operationId="getQuestion",
     *      tags={"review.setting.question"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get question",
     *      description="This method is to get question",
     *
 *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   getQuestion(Request $request)
    {
        $is_dafault = false;
        $garageId = !empty($request->garage_id)?$request->garage_id:NULL;
        if ($request->user()->hasRole("superadmin")) {

            $is_dafault = true;
            $garageId = NULL;

        }else{
            $garage =    Garage::where(["id" => $request->garage_id])->first();
            if(!$garage && !$request->user()->hasRole("superadmin")){
                return response("no garage found", 404);
            }
            // if ($garage->enable_question == true) {
            //     $is_dafault = true;

            // }
        }


        $query =  Question::where(["garage_id" => $garageId,"is_default" => $is_dafault]);


        $questions =  $query->get();


    return response($questions, 200);




    }

















      /**
        *
     * @OA\Get(
     *      path="/review-new/get/questions-all",
     *      operationId="getQuestionAll",
     *      tags={"review.setting.question"},

     *      summary="This method is to get all question without pagination",
     *      description="This method is to get all question without pagination",
     *       security={
     *           {"bearerAuth": {}}
     *       },
 *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   getQuestionAll(Request $request)
    {
        $is_dafault = false;
        if ($request->user()->hasRole("superadmin")) {

            $is_dafault = true;

        }else{
            $garage =    Garage::where(["id" => $request->garage_id])->first();
            if(!$garage && !$request->user()->hasRole("superadmin")){
                return response("no garage found", 404);
            }
            // if ($garage->enable_question == true) {
            //     $is_dafault = true;

            // }
        }


        $query =  Question::where(["garage_id" => $request->garage_id,"is_default" => $is_dafault]);


        $questions =  $query->get();

    $data =  json_decode(json_encode($questions), true);
    foreach($questions as $key1=>$question){

        foreach($question->question_stars as $key2=>$questionStar){
            $data[$key1]["stars"][$key2]= json_decode(json_encode($questionStar->star), true) ;


            $data[$key1]["stars"][$key2]["tags"] = [];
            foreach($questionStar->star->star_tags as $key3=>$starTag){
if($starTag->question_id == $question->id) {

    array_push($data[$key1]["stars"][$key2]["tags"],json_decode(json_encode($starTag->tag), true));


}



            }

        }

    }
    return response($data, 200);


    }

/**
        *
     * @OA\Get(
     *      path="/review-new/get/questions-all-report",
     *      operationId="getQuestionAllReport",
     *      tags={"review.setting.question"},

     *      summary="This method is to get all question report",
     *      description="This method is to get all question report",
     *       security={
     *           {"bearerAuth": {}}
     *       },
 *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
     *      ),
     *    @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="start_date",
     *         required=false,
     * * example="2023-06-29"
     *      ),
     *    @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="end_date",
     *         required=false,
     * * example="2023-06-29"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function getQuestionAllReport(Request $request) {


        $garage =    Garage::where(["id" => $request->garage_id])->first();
        if(!$garage){
            return response("no garage found", 404);
        }

    $query =  Question::where(["garage_id" => $request->garage_id,"is_default" => false]);

    $questions =  $query->get();

    $questionsCount = $query->get()->count();

$data =  json_decode(json_encode($questions), true);
foreach($questions as $key1=>$question){

    $tags_rating = [];
   $starCountTotal = 0;
   $starCountTotalTimes = 0;
    foreach($question->question_stars as $key2=>$questionStar){


        $data[$key1]["stars"][$key2]= json_decode(json_encode($questionStar->star), true) ;

        $data[$key1]["stars"][$key2]["stars_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
        ->where([
            "review_news.garage_id" => $garage->id,
            "question_id" => $question->id,
            "star_id" => $questionStar->star->id,
            "review_news.guest_id" => NULL

            ]
        );
        if(!empty($request->start_date) && !empty($request->end_date)) {

            $data[$key1]["stars"][$key2]["stars_count"] = $data[$key1]["stars"][$key2]["stars_count"]->whereBetween('review_news.created_at', [
                $request->start_date,
                $request->end_date
            ]);

        }
        $data[$key1]["stars"][$key2]["stars_count"] = $data[$key1]["stars"][$key2]["stars_count"]->get()
        ->count();

        $starCountTotal += $data[$key1]["stars"][$key2]["stars_count"] * $questionStar->star->value;

        $starCountTotalTimes += $data[$key1]["stars"][$key2]["stars_count"];
        $data[$key1]["stars"][$key2]["tag_ratings"] = [];
        if($starCountTotalTimes > 0) {
            $data[$key1]["rating"] = $starCountTotal / $starCountTotalTimes;
        }


        foreach($questionStar->star->star_tags as $key3=>$starTag){


     if($starTag->question_id == $question->id) {

        $starTag->tag->count =  ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
        ->where([
            "review_news.garage_id" => $garage->id,
            "question_id" => $question->id,
            "tag_id" => $starTag->tag->id,
            "review_news.guest_id" => NULL
            ]
        );
        if(!empty($request->start_date) && !empty($request->end_date)) {

            $starTag->tag->count = $starTag->tag->count->whereBetween('review_news.created_at', [
                $request->start_date,
                $request->end_date
            ]);

        }

        $starTag->tag->count = $starTag->tag->count->get()->count();
        if($starTag->tag->count > 0) {
            array_push($tags_rating,json_decode(json_encode($starTag->tag)));
                       }


        $starTag->tag->total =  ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
        ->where([
            "review_news.garage_id" => $garage->id,
            "question_id" => $question->id,
            "star_id" => $questionStar->star->id,
            "tag_id" => $starTag->tag->id,
            "review_news.guest_id" => NULL
            ]
        );
        if(!empty($request->start_date) && !empty($request->end_date)) {

            $starTag->tag->total = $starTag->tag->total->whereBetween('review_news.created_at', [
                $request->start_date,
                $request->end_date
            ]);

        }
        $starTag->tag->total = $starTag->tag->total->get()->count();

            if($starTag->tag->total > 0) {
                unset($starTag->tag->count);
                array_push($data[$key1]["stars"][$key2]["tag_ratings"],json_decode(json_encode($starTag->tag)));
            }


      }



        }

    }


    $data[$key1]["tags_rating"] = array_values(collect($tags_rating)->unique()->toArray());
}





$totalCount = 0;
$ttotalRating = 0;

foreach(Star::get() as $star) {

$data2["star_" . $star->value . "_selected_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
->where([
    "review_news.garage_id" => $garage->id,
    "star_id" => $star->id,
    "review_news.guest_id" => NULL
])
->distinct("review_value_news.review_id","review_value_news.question_id");
if(!empty($request->start_date) && !empty($request->end_date)) {

    $data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->whereBetween('review_news.created_at', [
        $request->start_date,
        $request->end_date
    ]);

}
$data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->count();

$totalCount += $data2["star_" . $star->value . "_selected_count"] * $star->value;

$ttotalRating += $data2["star_" . $star->value . "_selected_count"];

}
if($totalCount > 0) {
$data2["total_rating"] = $totalCount / $ttotalRating;

}
else {
$data2["total_rating"] = 0;

}

$data2["total_comment"] = ReviewNew::where([
"garage_id" => $garage->id,
"guest_id" => NULL,
])
->whereNotNull("comment");
if(!empty($request->start_date) && !empty($request->end_date)) {

$data2["total_comment"] = $data2["total_comment"]->whereBetween('review_news.created_at', [
    $request->start_date,
    $request->end_date
]);

}
$data2["total_comment"] = $data2["total_comment"]->count();

return response([
    "part1" =>  $data2,
    "part2" =>  $data
], 200);
}






    /**
        *
     * @OA\Get(
     *      path="/review-new/get/questions/{id}",
     *      operationId="getQuestionById",
     *      tags={"review.setting.question"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get question by id",
     *      description="This method is to get question by id",
     *
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="question Id",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function   getQuestionById($id, Request $request)
    {
        $questions =    Question::where(["id" => $id])
            ->first();


            if(!$questions) {
                return response([
                    "message" => "No question found"
                ], 404);
            }
            $data =  json_decode(json_encode($questions), true);

            foreach($questions->question_stars as $key2=>$questionStar){
                $data["stars"][$key2]= json_decode(json_encode($questionStar->star), true) ;


                $data["stars"][$key2]["tags"] = [];
                foreach($questionStar->star->star_tags as $key3=>$starTag){

    if($starTag->question_id == $questions->id) {

        array_push($data["stars"][$key2]["tags"],json_decode(json_encode($starTag->tag), true));

    }



                }

            }
        return response($data, 200);
    }







        /**
        *
     * @OA\Get(
     *      path="/review-new/get/questions/{id}/{garageId}",
     *      operationId="getQuestionById2",
     *      tags={"review.setting.question"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get question by id",
     *      description="This method is to get question by id",
     *
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="question Id",
     *         required=false,
     *      ),
     *   *         @OA\Parameter(
     *         name="garageId",
     *         in="path",
     *         description="garageId",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function   getQuestionById2($id,$garageId, Request $request)
    {
        $questions =    Question::where(["id" => $id,"garage_id"=>$garageId])
            ->first();


            if(!$questions) {
                return response([
                    "message" => "No question found"
                ], 404);
            }
            $data =  json_decode(json_encode($questions), true);

            foreach($questions->question_stars as $key2=>$questionStar){
                $data["stars"][$key2]= json_decode(json_encode($questionStar->star), true) ;


                $data["stars"][$key2]["tags"] = [];
                foreach($questionStar->star->star_tags as $key3=>$starTag){

    if($starTag->question_id == $questions->id) {

        array_push($data["stars"][$key2]["tags"],json_decode(json_encode($starTag->tag), true));

    }



                }

            }
        return response($data, 200);
    }




  /**
        *
     * @OA\Delete(
     *      path="/review-new/delete/questions/{id}",
     *      operationId="deleteQuestionById",
     *      tags={"review.setting.question"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to delete question by id",
     *      description="This method is to delete question by id",
     *        @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="question Id",
     *         required=false,
     *      ),

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   deleteQuestionById($id, Request $request)
    {
        $questions =    Question::where(["id" => $id])
            ->delete();

        return response(["message" => "ok"], 200);
    }

 /**
        *
     * @OA\Get(
     *      path="/review-new/get/questions-all-report/guest",
     *      operationId="getQuestionAllReportGuest",
     *      tags={"review.setting.question"},

     *      summary="This method is to get all question report guest",
     *      description="This method is to get all question report guest",
     *       security={
     *           {"bearerAuth": {}}
     *       },
 *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
     *      ),
         *    @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="start_date",
     *         required=false,
     * * example="2023-06-29"
     *      ),
     *    @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="end_date",
     *         required=false,
     * * example="2023-06-29"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function getQuestionAllReportGuest(Request $request) {


        $garage =    Garage::where(["id" => $request->garage_id])->first();
        if(!$garage){
            return response("no garage found", 404);
        }

    $query =  Question::where(["garage_id" => $request->garage_id,"is_default" => false]);

    $questions =  $query->get();

    $questionsCount = $query->get()->count();

$data =  json_decode(json_encode($questions), true);
foreach($questions as $key1=>$question){

    $tags_rating = [];
   $starCountTotal = 0;
   $starCountTotalTimes = 0;
    foreach($question->question_stars as $key2=>$questionStar){


        $data[$key1]["stars"][$key2]= json_decode(json_encode($questionStar->star), true) ;

        $data[$key1]["stars"][$key2]["stars_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
        ->where([
            "review_news.garage_id" => $garage->id,
            "question_id" => $question->id,
            "star_id" => $questionStar->star->id,
            "review_news.user_id" => NULL,

            ]
        );
        if(!empty($request->start_date) && !empty($request->end_date)) {

            $data[$key1]["stars"][$key2]["stars_count"]  = $data[$key1]["stars"][$key2]["stars_count"]->whereBetween('review_news.created_at', [
                $request->start_date,
                $request->end_date
            ]);

        }
        $data[$key1]["stars"][$key2]["stars_count"] = $data[$key1]["stars"][$key2]["stars_count"]
        ->get()
        ->count();

        $starCountTotal += $data[$key1]["stars"][$key2]["stars_count"] * $questionStar->star->value;

        $starCountTotalTimes += $data[$key1]["stars"][$key2]["stars_count"];
        $data[$key1]["stars"][$key2]["tag_ratings"] = [];
        if($starCountTotalTimes > 0) {
            $data[$key1]["rating"] = $starCountTotal / $starCountTotalTimes;
        }



        foreach($questionStar->star->star_tags as $key3=>$starTag){





     if($starTag->question_id == $question->id) {




        $starTag->tag->count =  ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
        ->where([
            "review_news.garage_id" => $garage->id,
            "question_id" => $question->id,
            "tag_id" => $starTag->tag->id,
            "review_news.user_id" => NULL
            ]
        );
        if(!empty($request->start_date) && !empty($request->end_date)) {

            $starTag->tag->count  = $starTag->tag->count->whereBetween('review_news.created_at', [
                $request->start_date,
                $request->end_date
            ]);

        }
        $starTag->tag->count = $starTag->tag->count->get()->count();

        if($starTag->tag->count > 0) {
                array_push($tags_rating,json_decode(json_encode($starTag->tag)));
        }

        $starTag->tag->total =  ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
        ->where([
            "review_news.garage_id" => $garage->id,
            "question_id" => $question->id,
            "star_id" => $questionStar->star->id,
            "tag_id" => $starTag->tag->id,
            "review_news.user_id" => NULL
            ]
        );
        if(!empty($request->start_date) && !empty($request->end_date)) {

            $starTag->tag->total = $starTag->tag->total->whereBetween('review_news.created_at', [
                $request->start_date,
                $request->end_date
            ]);

        }
        $starTag->tag->total = $starTag->tag->total->get()->count();
        if($starTag->tag->total > 0) {
            unset($starTag->tag->count);
            array_push($data[$key1]["stars"][$key2]["tag_ratings"],json_decode(json_encode($starTag->tag)));
        }




      }


        }

    }


    $data[$key1]["tags_rating"] = array_values(collect($tags_rating)->unique()->toArray());
}





$totalCount = 0;
$ttotalRating = 0;

foreach(Star::get() as $star) {

$data2["star_" . $star->value . "_selected_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
->where([
    "review_news.garage_id" => $garage->id,
    "star_id" => $star->id,
    "review_news.user_id" => NULL
])
->distinct("review_value_news.review_id","review_value_news.question_id");
if(!empty($request->start_date) && !empty($request->end_date)) {

    $data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->whereBetween('review_news.created_at', [
        $request->start_date,
        $request->end_date
    ]);

}
$data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->count();

$totalCount += $data2["star_" . $star->value . "_selected_count"] * $star->value;

$ttotalRating += $data2["star_" . $star->value . "_selected_count"];

}
if($totalCount > 0) {
$data2["total_rating"] = $totalCount / $ttotalRating;

}
else {
$data2["total_rating"] = 0;
}







$data2["total_comment"] = ReviewNew::where([
    "garage_id" => $garage->id,
    "user_id" => NULL,
])
->whereNotNull("comment");
if(!empty($request->start_date) && !empty($request->end_date)) {

    $data2["total_comment"] = $data2["total_comment"]->whereBetween('review_news.created_at', [
        $request->start_date,
        $request->end_date
    ]);

}
$data2["total_comment"] = $data2["total_comment"]->count();






return response([
    "part1" =>  $data2,
    "part2" =>  $data
], 200);
}


 /**
        *
     * @OA\Get(
     *      path="/review-new/get/questions-all-report/guest/quantum",
     *      operationId="getQuestionAllReportGuestQuantum",
     *      tags={"review.setting.question"},
     *   *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get all question report guest and only garage owner of the garage can view this ",
     *      description="This method is to get all question report guest and only garage owner of the garage can view this ",

 *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
     *      ),
     *     @OA\Parameter(
     *         name="quantum",
     *         in="query",
     *         description="quantum",
     *         required=false,
     *      ),
     *      @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="period",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function getQuestionAllReportGuestQuantum(Request $request) {


        $garage =    Garage::where(["id" => $request->garage_id,
        "OwnerID" => $request->user()->id
        ])->first();
        if(!$garage){
            return response("no garage found", 404);
        }
$data = [];

$period=0;
        for($i=0;$i<$request->quantum;$i++ ) {
            $totalCount = 0;
            $ttotalRating = 0;

            foreach(Star::get() as $star) {

            $data2["star_" . $star->value . "_selected_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
            ->where([
                "review_news.garage_id" => $garage->id,
                "star_id" => $star->id,
                "review_news.user_id" => NULL
            ])
            ->whereBetween(
                'review_news.created_at',
                [now()->subDays(($request->period + $period))->startOfDay(), now()->subDays($period)->endOfDay()]
            )
            ->distinct("review_value_news.review_id","review_value_news.question_id")
            ->count();

            $totalCount += $data2["star_" . $star->value . "_selected_count"] * $star->value;

            $ttotalRating += $data2["star_" . $star->value . "_selected_count"];

            }
            if($totalCount > 0) {
            $data2["total_rating"] = $totalCount / $ttotalRating;

            }
            else {
            $data2["total_rating"] = 0;
            }

            // $data2["total_comment"] = ReviewNew::where([
            //     "garage_id" => $garage->id,
            //     "user_id" => NULL,
            // ])
            // ->whereNotNull("comment")
            // ->count();
        array_push($data,$data2);
        $period +=  $request->period + $period;
        }







return response([
    "data" =>  $data,

], 200);
}

/**
        *
     * @OA\Get(
     *      path="/review-new/get/questions-all-report/quantum",
     *      operationId="getQuestionAllReportQuantum",
     *      tags={"review.setting.question"},
     *   *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get all question report.  and only garage owner of the garage can view this ",
     *      description="This method is to get all question report. and only garage owner of the garage can view this ",

 *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
     *      ),
     *  *         @OA\Parameter(
     *         name="quantum",
     *         in="query",
     *         description="quantum",
     *         required=false,
     *      ),
     *  *         @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="period",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *           @OA\Response(
     *          response=201,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function getQuestionAllReportQuantum(Request $request) {


        $garage =    Garage::where(["id" => $request->garage_id,
        "OwnerID" => $request->user()->id
        ])->first();
        if(!$garage){
            return response("no garage found", 404);
        }
$data = [];

$period=0;
        for($i=0;$i<$request->quantum;$i++ ) {
            $totalCount = 0;
            $ttotalRating = 0;

            foreach(Star::get() as $star) {

            $data2["star_" . $star->value . "_selected_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
            ->where([
                "review_news.garage_id" => $garage->id,
                "star_id" => $star->id,
                "review_news.guest_id" => NULL
            ])
            ->whereBetween(
                'review_news.created_at',
                [now()->subDays(($request->period + $period))->startOfDay(), now()->subDays($period)->endOfDay()]
            )
            ->distinct("review_value_news.review_id","review_value_news.question_id")
            ->count();

            $totalCount += $data2["star_" . $star->value . "_selected_count"] * $star->value;

            $ttotalRating += $data2["star_" . $star->value . "_selected_count"];

            }
            if($totalCount > 0) {
            $data2["total_rating"] = $totalCount / $ttotalRating;

            }
            else {
            $data2["total_rating"] = 0;
            }

            // $data2["total_comment"] = ReviewNew::where([
            //     "garage_id" => $garage->id,
            //     "user_id" => NULL,
            // ])
            // ->whereNotNull("comment")
            // ->count();
        array_push($data,$data2);
        $period +=  $request->period + $period;
        }







return response([
    "data" =>  $data,

], 200);
}


 /**
        *
     * @OA\Post(
     *      path="/review-new/create/tags",
     *      operationId="storeTag",
     *      tags={"review.setting.tag"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store tag",
     *      description="This method is to store tag",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"tag","garage_id"},
     *            @OA\Property(property="tag", type="string", format="string",example="How was this?"),
     *  @OA\Property(property="garage_id", type="number", format="number",example="1"),

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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function storeTag(Request $request)
    {
        $question = [
            'tag' => $request->tag,
            'garage_id' => $request->garage_id
        ];
        if ($request->user()->hasRole("superadmin")) {
            $question["is_default"] = true;
        } else {
            $garage =    Garage::where(["id" => $request->garage_id])->first();
            if(!$garage){
                return response()->json(["message" => "No garage Found"]);
            }
            if ($garage->enable_question == true) {
                return response()->json(["message" => "question is enabled"]);
            }
        }



        $createdQuestion =    Tag::create($question);


        return response($createdQuestion, 201);




        return response($createdQuestion, 201);
    }

   /**
        *
     * @OA\Post(
     *      path="/review-new/create/tags/multiple/{garageId}",
     *      operationId="storeTagMultiple",
     *      tags={"review.setting.tag"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store tag",
     *      description="This method is to store tag",
          *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"tags"},
 *  @OA\Property(property="tags", type="string", format="array",example={
 * "tag1","tag2"
     * }
     *
     * ),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function storeTagMultiple($garageId,Request $request)
    {



        $duplicate_indexes_array = [];

        $uniqueTags = collect($request->tags)->unique("tag");

        $uniqueTags->values()->all();

        foreach($uniqueTags as $index=>$tag) {
            $question = [
                'tag' => $tag,
                'garage_id' => $garageId
            ];


            if ($request->user()->hasRole("superadmin")) {


            $tag_found =    Tag::where([
                    "garage_id" => NULL,
                    "tag" => $question["tag"],
                    "is_default" => 1
                ])
                ->first();

         if($tag_found) {

            array_push($duplicate_indexes_array,$index);
        }
            } else {
                $tag_found =    Tag::where(["garage_id" => $garageId,"is_default" => 0,"tag" => $question["tag"]])

                ->first();

         if($tag_found) {

            array_push($duplicate_indexes_array,$index);
        } else {
            $tag_found =    Tag::where(["garage_id" => NULL,"is_default" => 1,"tag" => $question["tag"]])
            ->first();
            if($tag_found) {

                array_push($duplicate_indexes_array,$index);
            }
        }


            }





        }



        if(count($duplicate_indexes_array)) {

            return response([
                "message" => "duplicate data",
                "duplicate_indexes_array"=> $duplicate_indexes_array
        ], 409);

        }

        else {
 foreach($uniqueTags as $index=>$tag) {
            $question = [
                'tag' => $tag,
                'garage_id' => $garageId
            ];


            if ($request->user()->hasRole("superadmin")) {
                $question["is_default"] = true;
                $garageId = NULL;
                $question["garage_id"] = NULL;



            } else {

                $question["is_default"] = false;

                $garage =    Garage::where(["id" => $garageId])->first();
                if(!$garage){
                    return response()->json(["message" => "No garage Found"]);
                }
            }

            if(!count($duplicate_indexes_array)) {
                Tag::create($question);
            }



        }
        }






        return response(["message" => "data inserted"], 201);


    }


 /**
        *
     * @OA\Put(
     *      path="/review-new/update/tags",
     *      operationId="updateTag",
     *      tags={"review.setting.tag"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update tag",
     *      description="This method is to update tag",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"tag","id"},
     *            @OA\Property(property="tag", type="string", format="string",example="How was this?"),
     *  @OA\Property(property="id", type="number", format="number",example="1"),

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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function updateTag(Request $request)
    {

        $question = [
            'tag' => $request->tag
        ];
        $checkQuestion =    Tag::where(["id" => $request->id])->first();
        if ($checkQuestion->is_default == true && !$request->user()->hasRole("superadmin")) {
            return response()->json(["message" => "you can not update the question. you are not a super admin"]);
        }
        $updatedQuestion =    tap(Tag::where(["id" => $request->id]))->update(
            $question
        )
            // ->with("somthing")

            ->first();


        return response($updatedQuestion, 200);

    }





       /**
        *
     * @OA\Get(
     *      path="/review-new/get/tags",
     *      operationId="getTag",
     *      tags={"review.setting.tag"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get tag",
     *      description="This method is to get tag",
     *         @OA\Parameter(
     *         name="garage_id",
     *         in="query",
     *         description="garage Id",
     *         required=false,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   getTag(Request $request)
    {

        $is_dafault = false;
        $garageId = $request->garage_id;

        if ($request->user()->hasRole("superadmin")) {
            $is_dafault = true;
            $garageId = NULL;
            $query =  Tag::where(["garage_id" => NULL,"is_default" => true]);
        }
        else{
            $garage =    Garage::where(["id" => $request->garage_id])->first();
            if(!$garage && !$request->user()->hasRole("superadmin")){
                return response("no garage found", 404);
            }
            // if ($garage->enable_question == true) {
            //     $is_dafault = true;
            // }
            $query =  Tag::where(["garage_id" => $garageId,"is_default" => 0])
            ->orWhere(["garage_id" => NULL,"is_default" => 1]);
        }



        $questions =  $query->get();


        return response($questions, 200);




    }

       /**
        *
     * @OA\Get(
     *      path="/review-new/get/tags/{id}",
     *      operationId="getTagById",
     *      tags={"review.setting.tag"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get tag  by id",
     *      description="This method is to get tag  by id",
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="tag Id",
     *         required=false,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   getTagById($id, Request $request)
    {
        $questions =    Tag::where(["id" => $id])
            ->first();
            if(!$questions) {
                return response([
                    "message" => "No Tag Found"
                ], 404);
            }
        return response($questions, 200);
    }

       /**
        *
     * @OA\Get(
     *      path="/review-new/get/tags/{id}/{reataurantId}",
     *      operationId="getTagById2",
     *      tags={"review.setting.tag"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get tag  by id",
     *      description="This method is to get tag  by id",
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="tag Id",
     *         required=false,
     *      ),
     * *         @OA\Parameter(
     *         name="reataurantId",
     *         in="path",
     *         description="reataurantId",
     *         required=false,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   getTagById2($id,$reataurantId, Request $request)
    {
        $questions =    Tag::where(["id" => $id,"garage_id" => $reataurantId])
            ->first();
            if(!$questions) {
                return response([
                    "message" => "No Tag Found"
                ], 404);
            }
        return response($questions, 200);
    }

      /**
        *
     * @OA\Delete(
     *      path="/review-new/delete/tags/{id}",
     *      operationId="deleteTagById",
     *      tags={"review.setting.tag"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to delete tag  by id",
     *      description="This method is to delete tag  by id",
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="tag Id",
     *         required=false,
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function   deleteTagById($id, Request $request)
    {
        $tag =    Tag::where(["id" => $id])
        ->first();


            if ($request->user()->hasRole("superadmin") &&  $tag->is_default == 1) {
                $tag->delete();
            }
            else  if(!$request->user()->hasRole("superadmin") &&  $tag->is_default == 0){
                $tag->delete();
            }



        return response(["message" => "ok"], 200);
    }


    /**
        *
     * @OA\Post(
     *      path="/review-new/owner/create/questions",
     *      operationId="storeOwnerQuestion",
     *      tags={"review.setting.link"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store question",
     *      description="This method is to store question.",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"question_id","stars"},

     *  @OA\Property(property="question_id", type="number", format="number",example="1"),
     *  @OA\Property(property="stars", type="string", format="array",example={
     *
     * { "star_id":"2",
     *
     * "tags":{
     * {"tag_id":"2"},
     * {"tag_id":"2"}
     * }
     *
     * }
     *
     *
     * }
     *
     * ),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function storeOwnerQuestion(Request $request)
    {
        return DB::transaction(function ()use($request) {
            $question_id = $request->question_id;
            foreach($request->stars as $requestStar){


                QusetionStar::create([
                    "question_id"=>$question_id,
                    "star_id" => $requestStar["star_id"]
                         ]);


               foreach($requestStar["tags"] as $tag){


               StarTag::create([
                "question_id"=>$question_id,
                "tag_id"=>$tag["tag_id"],
                "star_id" => $requestStar["star_id"]
                     ]);

               }
            }

      return response(["message" => "ok"], 201);
        });

    }


      /**
        *
     * @OA\Post(
     *      path="/review-new/owner/update/questions",
     *      operationId="updateOwnerQuestion",
     *      tags={"review.setting.link"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update question",
     *      description="This method is to update question",
     *             @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         description="method",
     *         required=false,
     * example="PATCH"
     *      ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"question_id","stars"},

     *  @OA\Property(property="question_id", type="number", format="number",example="1"),
     *  @OA\Property(property="stars", type="string", format="array",example={
     *  {* "star_id":"2",
     * "tags":{
     * {"tag_id":"2"},
     * {"tag_id":"2"}
     *
     * }
     *
     * }
     * }
     *
     * ),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function updateOwnerQuestion(Request $request)
    {

        return DB::transaction(function ()use($request) {
            $question_id = $request->question_id;
            QusetionStar::where([
                "question_id"  => $question_id
            ])
            ->delete();

            StarTag::where([
                "question_id"  => $question_id
            ])
            ->delete();
            foreach($request->stars as $requestStar){


                QusetionStar::create([
                    "question_id"=>$question_id,
                    "star_id" => $requestStar["star_id"]
                         ]);


               foreach($requestStar["tags"] as $tag){



               StarTag::create([
                "question_id"=>$question_id,
                "tag_id"=>$tag["tag_id"],
                "star_id" => $requestStar["star_id"]
                     ]);

               }
            }

      return response(["message" => "ok"], 201);
        });

    }







   /**
        *
     * @OA\Get(
     *      path="/review-new/getavg/review/{garageId}/{start}/{end}",
     *      operationId="getAverage",
     *      tags={"review"},
     *   *       security={
     *           {"bearerAuth": {}}
     *       },
    *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),
  *  @OA\Parameter(
* name="start",
* in="path",
* description="from date",
* required=true,
* example="2019-06-29"
* ),
  *  @OA\Parameter(
* name="end",
* in="path",
* description="to date",
* required=true,
* example="2026-06-29"
* ),
     *      summary="This method is to get average",
     *      description="This method is to get average",
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */
    public function  getAverage($garageId, $start, $end, Request $request)
    {
        // with
        $reviews = ReviewNew::where([
            "garage_id" => $garageId
        ])
            ->whereBetween('created_at', [$start, $end])
            ->with("question")
            ->get();

        $data["total"]   = $reviews->count();
        $data["one"]   = 0;
        $data["two"]   = 0;
        $data["three"] = 0;
        $data["four"]  = 0;
        $data["five"]  = 0;
        foreach ($reviews as $review) {
            switch ($review->rate) {
                case 1:
                    $data[$review->question->name]["one"] += 1;
                    break;
                case 2:
                    $data["two"] += 1;
                    break;
                case 3:
                    $data["three"] += 1;
                    break;
                case 4:
                    $data["four"] += 1;
                    break;
                case 5:
                    $data[$review->question->question]["five"] += 1;
                    break;
            }
        }


        return response($data, 200);
    }


  /**
        *
     * @OA\Get(
     *      path="/review-new/getreview/{garageId}/{rate}/{start}/{end}",
     *      operationId="filterReview",
     *      tags={"review"},
     *        security={
     *           {"bearerAuth": {}}
     *       },
        *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),
    *  @OA\Parameter(
* name="rate",
* in="path",
* description="rate",
* required=true,
* example="1"
* ),
  *  @OA\Parameter(
* name="start",
* in="path",
* description="from date",
* required=true,
* example="2019-06-29"
* ),
  *  @OA\Parameter(
* name="end",
* in="path",
* description="to date",
* required=true,
* example="2026-06-29"
* ),
     *      summary="This method is to filter   Review",
     *      description="This method is to filter   Review",
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */


    public function  filterReview($garageId, $rate, $start, $end, Request $request)
    {
        // with
        $reviewValues = ReviewNew::where([
            "garage_id" => $garageId,
            "rate" => $rate
        ])
            ->with("garage","value")
            ->whereBetween('created_at', [$start, $end])
            ->get();


        return response($reviewValues, 200);
    }

     /**
        *
     * @OA\Get(
     *      path="/review-new/getreviewAll/{garageId}",
     *      operationId="getReviewByGarageId",
     *      tags={"review"},
     *        security={
     *           {"bearerAuth": {}}
     *       },
        *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),

     *      summary="This method is to get review by garage id",
     *      description="This method is to get review by garage id",
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function  getReviewByGarageId($garageId, Request $request)
    {
        // with
        $reviewValue = ReviewNew::with("value")->where([
            "garage_id" => $garageId,
        ])
            ->get();


        return response($reviewValue, 200);
    }



      /**
        *
     * @OA\Get(
     *      path="/review-new/getcustomerreview/{garageId}/{start}/{end}",
     *      operationId="getCustommerReview",
     *      tags={"review"},
     *        security={
     *           {"bearerAuth": {}}
     *       },
        *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),

  *  @OA\Parameter(
* name="start",
* in="path",
* description="from date",
* required=true,
* example="2019-06-29"
* ),
  *  @OA\Parameter(
* name="end",
* in="path",
* description="to date",
* required=true,
* example="2026-06-29"
* ),
     *      summary="This method is to get customer review",
     *      description="This method is to get customer review",
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */





    public function  getCustommerReview($garageId, $start, $end, Request $request)
    {
        // with
        $data["reviews"] = ReviewNew::where([
            "garage_id" => $garageId,
        ])
            ->whereBetween('created_at', [$start, $end])
            ->get();
        $data["total"]   = $data["reviews"]->count();
        $data["one"]   = 0;
        $data["two"]   = 0;
        $data["three"] = 0;
        $data["four"]  = 0;
        $data["five"]  = 0;
        foreach ($data["reviews"]  as $reviewValue) {
            switch ($reviewValue->rate) {
                case 1:
                    $data["one"] += 1;
                    break;
                case 2:
                    $data["two"] += 1;
                    break;
                case 3:
                    $data["three"] += 1;
                    break;
                case 4:
                    $data["four"] += 1;
                    break;
                case 5:
                    $data["five"] += 1;
                    break;
            }
        }

        return response($data, 200);
    }



     /**
        *
     * @OA\Post(
     *      path="/review-new/{garageId}",
     *      operationId="storeReview",
     *      tags={"review"},
     *    *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),
*
  *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store review",
     *      description="This method is to store review",
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"description","rate","comment","values"},
     *
     *             @OA\Property(property="description", type="string", format="string",example="test"),
     *            @OA\Property(property="rate", type="string", format="string",example="2.5"),
     *              @OA\Property(property="comment", type="string", format="string",example="not good"),
     *
     *
     *    *  @OA\Property(property="values", type="string", format="array",example={

     *  {"question_id":1,"tag_id":2,"star_id":1},
    *  {"question_id":2,"tag_id":1,"star_id":4},

     * }
     *
     * ),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function storeReview($garageId,  Request $request)
    {

            $review = [
                'description' => $request["description"],
                'garage_id' => $garageId,
                'rate' => $request["rate"],
                'user_id' => $request->user()->id,
                'comment' => $request["comment"],
            //     'question_id' => $singleReview["question_id"],
            // 'tag_id' => $request->tag_id,
            // 'star_id' => $request->star_id,
            ];

            $createdReview =   ReviewNew::create($review);

            $rate = 0;
            $questionCount = 0;
            $previousQuestionId = NULL;
            foreach ($request["values"] as $value) {
               if(!$previousQuestionId) {
                $previousQuestionId = $value["question_id"];
                $rate += $value["star_id"];
               }else {

                if($value["question_id"] != $previousQuestionId) {
                    $rate += $value["star_id"];
                    $previousQuestionId = $value["question_id"];
                    $questionCount += 1;
                }

               }

               $createdReview->rate =  $rate;
               $createdReview->save();
                $value["review_id"] = $createdReview->id;
                // $value["question_id"] = $createdReview->question_id;
                // $value["tag_id"] = $createdReview->tag_id;
                // $value["star_id"] = $createdReview->star_id;
                ReviewValueNew::create($value);
            }


        return response(["message" => "created successfully"], 201);
    }


     /**
        *
     * @OA\Post(
     *      path="/review-new-guest/{garageId}",
     *      operationId="storeReviewByGuest",
     *      tags={"review"},
     *    *  @OA\Parameter(
* name="garageId",
* in="path",
* description="garageId",
* required=true,
* example="1"
* ),
*
  *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store review by guest user",
     *      description="This method is to store review by guest user",
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"guest_full_name","guest_phone","description","rate","comment","values"},
     *
     * *             @OA\Property(property="guest_full_name", type="string", format="string",example="Rifat"),
     * *             @OA\Property(property="guest_phone", type="string", format="string",example="0177"),
     *             @OA\Property(property="description", type="string", format="string",example="test"),
     *            @OA\Property(property="rate", type="string", format="string",example="2.5"),
     *              @OA\Property(property="comment", type="string", format="string",example="not good"),
     *
     *
     *    *  @OA\Property(property="values", type="string", format="array",example={

     *  {"question_id":1,"tag_id":2,"star_id":1},
    *  {"question_id":2,"tag_id":1,"star_id":4},

     * }
     *
     * ),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function storeReviewByGuest($garageId,  Request $request)
    {

            $guestData = [
                'full_name' => $request["guest_full_name"],
                'phone' => $request["guest_phone"],
            ];

            $guest = GuestUser::create($guestData);
            $review = [
                'description' => $request["description"],
                'garage_id' => $garageId,
                'rate' => $request["rate"],
                'guest_id' => $guest->id,
                'comment' => $request["comment"],

            ];
            $createdReview =   ReviewNew::create($review);

            $rate = 0;
            $questionCount = 0;
            $previousQuestionId = NULL;
            foreach ($request["values"] as $value) {
               if(!$previousQuestionId) {
                $previousQuestionId = $value["question_id"];
                $rate += $value["star_id"];
               }else {

                if($value["question_id"] != $previousQuestionId) {
                    $rate += $value["star_id"];
                    $previousQuestionId = $value["question_id"];
                    $questionCount += 1;
                }

               }

               $createdReview->rate =  $rate;
               $createdReview->save();
                $value["review_id"] = $createdReview->id;
                // $value["question_id"] = $createdReview->question_id;
                // $value["tag_id"] = $createdReview->tag_id;
                // $value["star_id"] = $createdReview->star_id;
                ReviewValueNew::create($value);
            }


        return response(["message" => "created successfully"], 201);
    }
}
