<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "id"=>"required|numeric",
            "garage_id"=>"required|numeric",
            "name"=>"required|string",
            "code"=>"required|string|unique:coupons,code",
            "discount_type"=>"required|string",
            "discount_amount"=>"required|numeric",
            "min_total"=>"required|numeric",
            "max_total"=>"required|numeric",
            "redemptions"=>"required|numeric",
            "coupon_start_date"=>"required|date",
            "coupon_end_date"=>"required|date",
            "is_auto_apply"=>"required|boolean",
            "is_active"=>"required|boolean",
        ];
    }
}
