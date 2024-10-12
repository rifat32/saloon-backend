<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
use App\Rules\ValidateExpert;
use Illuminate\Foundation\Http\FormRequest;

class BookingUpdateRequest extends FormRequest
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


              'expert_id' => [
                'required',
                'numeric',
                 new ValidateExpert(NULL)
            ],

   'booked_slots' => [
    'required',
    'array',
],
'booked_slots.*' => [
    'required',
    'date_format:g:i A',
],




            // "customer_id",
            "garage_id" => "required|numeric",
            "automobile_make_id" => "required|numeric",
            "automobile_model_id" =>"required|numeric",
            "car_registration_no" => "required|string",
            "car_registration_year" => "nullable|date",

            // "coupon_code" => "nullable|string",
            "discount_type" => "nullable|string|in:fixed,percentage",
            "discount_amount" => "required_if:discount_type,!=,null|numeric|min:0",

            "total_price" => "nullable|numeric",
            "additional_information" => "nullable|string",

            "status"=>"required|string|in:pending,confirmed,rejected_by_garage_owner",
            "job_start_date" => "required_if:status,confirmed|date",
            "job_start_time" => ['required_if:status,confirmed','date_format:H:i', new TimeValidation
        ],
            "job_end_time" => ['required_if:status,confirmed','date_format:H:i', new TimeValidation
        ],


        "fuel" => "nullable|string",
        "transmission" => "nullable|string",

        'booking_sub_service_ids' => 'nullable|array',
        'booking_sub_service_ids.*' => 'nullable|numeric',

        'booking_garage_package_ids' => 'nullable|array',
        'booking_garage_package_ids.*' => 'nullable|numeric',


        ];
    }

    public function messages()
    {

        return [
       "status.in" => 'The :attribute field must be one of  pending,confirmed,rejected_by_garage_owner',

        ];
    }
}
