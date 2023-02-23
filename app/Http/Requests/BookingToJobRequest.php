<?php

namespace App\Http\Requests;

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
            "discount" => "required_if:discount_type,!=,null|numeric|min:0",
            "price" => "required|numeric"
        ];
    }
}
