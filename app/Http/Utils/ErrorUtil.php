<?php

namespace App\Http\Utils;

use App\Models\ErrorLog;
use Exception;

trait ErrorUtil
{
    // this function do all the task and returns transaction id or -1
    public function sendError(Exception $e,$statusCode,$apiUrl = "")
    {

        // first return 422 custom error

        if($e->getCode() == 422) {
            return response()->json(json_decode($e->getMessage()),422);
        }


        if (env("APP_DEBUG") === false) {
            $data["message"] = "something went wrong";
        } else {
            $data["message"] = $e->getMessage();
        }

        $data["status_code"] = $statusCode;
        $data["line"] = $e->getLine();
        $data["file"] = $e->getFile();
        $data["api_url"] = $apiUrl;


        // ErrorLog::create([
        //     "api_url" => $data["api_url"],
        //     "user"=> auth()->user(),
        //     "message"=> $data["message"],
        //     "status_code"=> $data["status_code"],
        //     "line"=> $data["line"],
        //     "file"=> $data["file"],

        // ]);
return response()->json($data,$statusCode);

    }
}
