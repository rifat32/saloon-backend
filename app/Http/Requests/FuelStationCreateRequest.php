<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;

class FuelStationCreateRequest extends FormRequest
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
            "name" => "required|string",
            "address" => "required|string",
            "opening_time"=> ['required','date_format:H:i:s?', // Ensure that the input is in the H:i:s format
            function ($attribute, $value, $fail) {
                $timeParts = explode(':', $value);
                $hour = $timeParts[0];
                $minute = $timeParts[1];
                $second = !empty($timeParts[2])?$timeParts[2]:0;
                if (!checkdate(1, 1, 1) || !checkdate(1, 1, 1970) || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59) {
                    $fail('The '.$attribute.' field must be a valid time value.');
                }
            }],
            "closing_time" => ['required','date_format:H:i:s?', new TimeValidation
            ],
            "description" => "nullable|string",
        ];
    }
}
