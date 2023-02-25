<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
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
            // "customer_id",
            "garage_id" => "required|numeric",
            "automobile_make_id" => "required|numeric",
            "automobile_model_id" =>"required|numeric",
            "car_registration_no" => "required|string",

            "additional_information" => "nullable|string",

            "status"=>"required|string|in:pending,confirmed,rejected_by_garage_owner",
            "job_start_date" => "required_if:status,confirmed|date",
            "job_start_time" => ['required_if:status,confirmed','date_format:H:i', new TimeValidation
        ],
            "job_end_time" => ['required_if:status,confirmed','date_format:H:i', new TimeValidation
        ],





    'booking_sub_service_ids' => 'required|array',
    'booking_sub_service_ids.*' => 'required|numeric',


        ];
    }

    public function messages()
    {

        return [
       "status.in" => 'The :attribute field must be one of  pending,confirmed,rejected_by_garage_owner',

        ];
    }
}
