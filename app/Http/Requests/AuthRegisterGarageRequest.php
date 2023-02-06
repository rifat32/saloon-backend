<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegisterGarageRequest extends FormRequest
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
            'user.first_Name' => 'required|string|max:255',
            'user.last_Name' => 'required|string|max:255',
            'user.email' => 'required|string|email|indisposable|max:255|unique:users,email',
            'user.password' => 'required|confirmed|string|min:6',
            'user.phone' => 'required|string',
            'user.image' => 'nullable',
            'user.address_line_1' => 'required|string',
            'user.address_line_2' => 'required|string',
            'user.country' => 'required|string',
            'user.city' => 'required|string',
            'user.postcode' => 'required|string',


            'garage.name' => 'required|string|max:255',
            'garage.about' => 'nullable|string',
            'garage.web_page' => 'nullable|string',
            'garage.phone' => 'required|string',
            'garage.email' => 'required|string|email|indisposable|max:255|unique:garages,email',
            'garage.additional_information' => 'nullable|string',

            'garage.country' => 'required|string',
            'garage.city' => 'required|string',
            'garage.postcode' => 'required|string',
            'garage.address_line_1' => 'required|string',
            'garage.address_line_2' => 'required|string',


            'garage.logo' => 'nullable|string',
            'garage.is_mobile_garage' => 'required|boolean',
            'garage.wifi_available' => 'required|boolean',
            'garage.labour_rate' => 'nullable|numeric',
            'garage.average_time_slot' => 'nullable|numeric',


            // 'service.services' => "array|required",
            // 'service.automobile_makes' => "array|required",

            'service' => "array|required",

            'service.*.automobile_category_id' => "required|numeric",

            'service.*.services' => "required|array",
            'service.*.automobile_makes' => "required|array",



            // 'service.automobile_categories' => "array|required",


        ];


    }

    public function messages()
    {
        return [
            'user.first_Name.required' => 'The first name is required',
            "service.*.automobile_category_id" => "Please select at least one automobile category",
            "service.*.services" => "Please select services",
            "service.*.automobile_makes" => "Please select makes"
            // 'model.required' => 'The car model is required',
            // 'year.required' => 'The car year is required',
            // 'year.numeric' => 'The car year must be a number',
        ];
    }
}
