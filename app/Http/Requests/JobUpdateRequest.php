<?php

namespace App\Http\Requests;

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
            "status"=>"required|string",
            "additional_information" => "nullable|string",

            "job_start_time" => "required|date",
            "job_end_time" => "required|date",

            'booking_sub_service_ids' => 'required|array',
            'booking_sub_service_ids.*' => 'required|numeric',


             "discount_type" => "nullable|string|in:fixed,percentage",
             "discount_amount" => "required_if:discount_type,!=,null|numeric|min:0",
             "price" => "required|numeric"


        ];
    }
}
