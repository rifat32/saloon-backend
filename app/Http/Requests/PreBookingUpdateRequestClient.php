<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreBookingUpdateRequestClient extends FormRequest
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
            "garage_id" => "required|numeric",
            // "customer_id",
            "automobile_make_id" => "required|numeric",
            "automobile_model_id" =>"required|numeric",
            "car_registration_no" => "required|string",
             "additional_information" => "nullable|string",
            // "status",
            "coupon_code" => "nullable|string",
    'pre_booking_sub_service_ids' => 'required|array',
    'pre_booking_sub_service_ids.*' => 'required|numeric',

    'country' => 'required|string',
    'city' => 'required|string',
    'postcode' => 'required|string',
    'address' => 'required|string',

        ];
    }
}
