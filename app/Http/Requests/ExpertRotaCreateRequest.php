<?php



namespace App\Http\Requests;

use App\Rules\ValidateExpert;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateExpertRotaName;

class ExpertRotaCreateRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return  bool
*/
public function authorize()
{
return true;
}

/**
* Get the validation rules that apply to the request.
*
* @return  array
*/
public function rules()
{

$rules = [

        'expert_id' => [
        'required',
        'numeric',
         new ValidateExpert(NULL)
    ],

        'data' => [
        'required',
        'date',
    ],

   'busy_slots' => [
    'required',
    'array',
],
'busy_slots.*' => [
    'required',
    'date_format:g:i A',
],


];



return $rules;
}
}


