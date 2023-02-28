<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;

class BookingToJobRequest extends FormRequest
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
            "booking_id" => "required|numeric",
            "garage_id" => "required|numeric",

            "discount_type" => "nullable|string|in:fixed,percentage",

            "discount_amount" => "required_if:discount_type,!=,null|numeric|min:0",

            "price" => "required|numeric",

            "coupon_code" => "nullable|string",

            "job_start_date" => "required|date",
            "job_start_time" => ['required','date_format:H:i', new TimeValidation
        ],
            "job_end_time" => ['required','date_format:H:i', new TimeValidation
        ],
        "status" => "required|string|in:pending,active",
        ];
    }

    public function messages()
    {

        return [
       "status.in" => 'The :attribute field must be of pending,active',

        ];
    }
}
