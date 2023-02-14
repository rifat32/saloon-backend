<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;

class FuelStationUpdateRequest extends FormRequest
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
            'id' => 'required|numeric',
            "name" => "required|string",
            "address" => "required|string",
            "opening_time"=>['required','date_format:H:i:s?', new TimeValidation
        ],
            "closing_time" => ['required','date_format:H:i:s?', new TimeValidation
        ],
            "description" => "nullable|string",
        ];
    }
}
