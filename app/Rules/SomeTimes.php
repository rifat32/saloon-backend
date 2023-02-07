<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SomeTimes implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */

    public function passes($attribute, $value)
    {
        error_log(json_encode($attribute));
        error_log(json_encode($value));
      return  collect($value)->contains(function ($data, $key) {
            return ($data["checked"] == true || $data["checked"] == 1);
              error_log(($data["checked"] == true || $data["checked"] == 1));
              error_log(($data["checked"] == true || $data["checked"] == 1));
              error_log(($data["checked"] == true || $data["checked"] == 1));
              if(($data["checked"] == true || $data["checked"] == 1)){
                error_log("trueeeeeee");
                  return false;
              }
              error_log("falseeeeee");
            return false;
        });
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please select at least one';
    }
}
