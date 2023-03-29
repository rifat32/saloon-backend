<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;

class JobUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "id" => "required|numeric",
            // "customer_id",
            "garage_id" => "required|numeric",
            "automobile_make_id" => "required|numeric",
            "automobile_model_id" =>"required|numeric",
            "car_registration_no" => "required|string",
            "additional_information" => "nullable|string",

            "job_start_time" => "required|date",
            "job_end_time" => "required|date",

            'job_sub_service_ids' => 'required|array',
            'job_sub_service_ids.*' => 'required|numeric',

            'job_garage_package_ids' => 'required|array',
            'job_garage_package_ids.*' => 'required|numeric',


            "coupon_code" => "nullable|string",

             "discount_type" => "nullable|string|in:fixed,percentage",
             "discount_amount" => "required_if:discount_type,!=,null|numeric|min:0",
             "price" => "required|numeric",

             "job_start_date" => "required|date",
             "job_start_time" => ['required','date_format:H:i', new TimeValidation
         ],
             "job_end_time" => ['required','date_format:H:i', new TimeValidation
         ],
         "status" => "required|string|in:pending,active,completed,cancelled",

         "fuel" => "nullable|string",
         "transmission" => "nullable|string",

        ];
    }


    public function messages()
    {

        return [
       "status.in" => 'The :attribute field must be of pending,active,completed,cancelled',

        ];
    }
}
