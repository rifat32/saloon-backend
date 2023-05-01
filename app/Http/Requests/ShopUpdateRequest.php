<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopUpdateRequest extends FormRequest
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
            'user.id' => 'required|numeric',
            'user.first_Name' => 'required|string|max:255',
            'user.last_Name' => 'required|string|max:255',
            // 'user.email' => 'required|string|email|indisposable|max:255',
            'user.email' => 'required|string|email|max:255',
            'user.password' => 'nullable|confirmed|string|min:6',
            'user.phone' => 'required|string',
            'user.image' => 'nullable',
            'user.address_line_1' => 'nullable|string',
            'user.address_line_2' => 'nullable|string',
            'user.country' => 'nullable|string',
            'user.city' => 'nullable|string',
            'user.postcode' => 'nullable|string',

            'shop.id' => 'required|numeric',
            'shop.name' => 'required|string|max:255',
            'shop.about' => 'nullable|string',
            'shop.web_page' => 'nullable|string',
            'shop.phone' => 'nullable|string',
            // 'shop.email' => 'required|string|email|indisposable|max:255',
            'shop.email' => 'required|string|email|max:255',
            'shop.additional_information' => 'nullable|string',


            'shop.lat' => 'nullable|string',
            'shop.long' => 'nullable|string',
            'shop.country' => 'required|string',
            'shop.city' => 'required|string',
            'shop.postcode' => 'required|string',
            'shop.address_line_1' => 'required|string',
            'shop.address_line_2' => 'nullable|string',


            'shop.logo' => 'nullable|string',
            'shop.is_mobile_shop' => 'required|boolean',
            'shop.wifi_available' => 'required|boolean',
            'shop.labour_rate' => 'nullable|numeric',







        ];


    }

    public function customRequiredMessage($property) {

        return "The ".$property." must be required";
    }

    public function messages()
    {

        return [
            'user.first_Name.required' => $this->customRequiredMessage("first name"),
            'user.last_Name.required' => $this->customRequiredMessage("last name"),
            'user.email.required' => $this->customRequiredMessage("email"),
            // 'user.password.confirmed' => $this->customRequiredMessage("password"),




            'shop.name.required' => $this->customRequiredMessage("shop name"),
            'shop.email.required' => $this->customRequiredMessage("shop email"),
            'shop.country.required' => $this->customRequiredMessage("shop country"),
            'shop.city.required' => $this->customRequiredMessage("shop city"),
            'shop.postcode.required' => $this->customRequiredMessage("shop postcode"),
            'shop.address_line_1.required' => $this->customRequiredMessage("shop address line 1"),












        ];
    }
}
