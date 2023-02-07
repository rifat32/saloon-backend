<?php

namespace App\Http\Requests;

use App\Rules\SomeTimes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

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
            'user.address_line_1' => 'nullable|string',
            'user.address_line_2' => 'nullable|string',
            'user.country' => 'nullable|string',
            'user.city' => 'nullable|string',
            'user.postcode' => 'nullable|string',


            'garage.name' => 'required|string|max:255',
            'garage.about' => 'nullable|string',
            'garage.web_page' => 'nullable|string',
            'garage.phone' => 'nullable|string',
            'garage.email' => 'required|string|email|indisposable|max:255|unique:garages,email',
            'garage.additional_information' => 'nullable|string',

            'garage.country' => 'required|string',
            'garage.city' => 'required|string',
            'garage.postcode' => 'required|string',
            'garage.address_line_1' => 'required|string',
            'garage.address_line_2' => 'nullable|string',


            'garage.logo' => 'nullable|string',
            'garage.is_mobile_garage' => 'required|boolean',
            'garage.wifi_available' => 'required|boolean',
            'garage.labour_rate' => 'nullable|numeric',
            'garage.average_time_slot' => 'nullable|numeric',




            'service' => "array|required",
            'service.*.automobile_category_id' => "required|numeric",

            'service.*.services' => ["required","array",new SomeTimes],
            'service.*.services.*.id' => "required|numeric",
            'service.*.services.*.checked' => "required|boolean",

            'service.*.automobile_makes' => ["required","array",new SomeTimes],
            'service.*.automobile_makes.*.id' => "required|numeric",
            'service.*.automobile_makes.*.checked' => ["required","boolean"],
            // 'service.automobile_categories' => "array|required",


        ];


    }

    public function customRequiredMessage($property) {

        return "The ".$property."field is required";
    }

    public function messages()
    {

        return [
            'user.first_Name.required' => $this->customRequiredMessage("first name"),
            'user.last_Name.required' => $this->customRequiredMessage("last name"),
            'user.email.required' => $this->customRequiredMessage("email"),
            'user.password.required' => $this->customRequiredMessage("password"),




            'garage.name.required' => $this->customRequiredMessage("garage name"),
            'garage.email.required' => $this->customRequiredMessage("garage email"),
            'garage.country.required' => $this->customRequiredMessage("garage country"),
            'garage.city.required' => $this->customRequiredMessage("garage city"),
            'garage.postcode.required' => $this->customRequiredMessage("garage postcode"),
            'garage.address_line_1.required' => $this->customRequiredMessage("garage address line 1"),












        ];
    }
    public function validate()
{

    $validator = Validator::make($this->all(), $this->rules(), $this->messages());

    $validator->sometimes('service.*.automobile_makes.*.checked', 'required|in:1', function ($input) {
        return collect($input->service)->pluck('automobile_makes.*.checked')->contains(1);
    });

    $validator->validate();

}


}
