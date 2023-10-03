<?php

namespace App\Http\Requests;

use App\Rules\DayValidation;
use App\Rules\SomeTimes;
use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class GarageCreateRequest extends FormRequest
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

            'garage.owner_id' => 'required|numeric',



            'garage.name' => 'required|string|max:255',
            'garage.about' => 'nullable|string',
            'garage.web_page' => 'nullable|string',
            'garage.phone' => 'nullable|string',
            // 'garage.email' => 'required|string|email|indisposable|max:255|unique:garages,email',
            'garage.email' => 'required|string|email|max:255|unique:garages,email',
            'garage.additional_information' => 'nullable|string',

            'garage.lat' => 'required|string',
            'garage.long' => 'required|string',
            'garage.country' => 'required|string',
            'garage.city' => 'required|string',

            'garage.currency' => 'nullable|string',

            'garage.postcode' => 'required|string',
            'garage.address_line_1' => 'required|string',
            'garage.address_line_2' => 'nullable|string',


            'garage.logo' => 'nullable|string',

            'garage.image' => 'nullable|string',

            'garage.images' => 'nullable|array',
            'garage.images.*' => 'nullable|string',

            'garage.is_mobile_garage' => 'required|boolean',
            'garage.wifi_available' => 'required|boolean',
            'garage.labour_rate' => 'nullable|numeric',

            'times' => 'required|array|min:1',
            "times.*.day" => ["numeric",new DayValidation],
            "times.*.opening_time" => ['required','date_format:H:i', new TimeValidation
           ],
            "times.*.closing_time" => ['required','date_format:H:i', new TimeValidation
           ],
           "times.*.is_closed" => ['required',"boolean"],



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

        return "The ".$property." must be required";
    }

    public function messages()
    {


        $defaultMessages = Validator::getDefaultMessages();

        $requiredMessage = Validator::getDefaultMessages()['required'];
        $requiredMessage = str_replace('garage.name', 'name', $requiredMessage);

        $defaultMessages['garage.name.required'] = $requiredMessage;
        // Edit the default messages as needed
        $defaultMessages['garage.name.required'] = 'The garage name is required.';
        $defaultMessages['garage.email.required'] = 'The garage email is required.';
        $defaultMessages['garage.country.required'] = 'The garage country is required.';
        $defaultMessages['garage.city.required'] = 'The garage city is required.';
        $defaultMessages['garage.address_line_1.required'] = 'The garage address line 1 is required.';

        return $defaultMessages;



        return [





            'garage.name.required' => $this->customRequiredMessage("garage name"),
            'garage.email.required' => $this->customRequiredMessage("garage email"),
              'garage.email.required' => $this->customRequiredMessage("garage email"),
            'garage.country.required' => $this->customRequiredMessage("garage country"),
            'garage.city.required' => $this->customRequiredMessage("garage city"),

            'garage.address_line_1.required' => $this->customRequiredMessage("garage address line 1"),

        ];
    }


}
