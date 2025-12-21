<?php

namespace App\Http\Requests;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use App\Enum\PaymentGateway;
use Illuminate\Validation\Rules\Enum;
class OrderRequest extends FormRequest


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
            'service_id' => ['nullable', 'array'],
            'service_id.*' => ['required', 'exists:services,id'],
            'products' => ['nullable', 'array'],
            'products.*.id' => 'required|exists:' . (new Product())->getTable() . ',id',
            'additional_service_id' => 'nullable|array',
            'pick_date' => ['required', 'date'],
            'pick_hour' => ['required'],
            'delivery_date' => ['required', 'date'],
            'delivery_hour' => ['required'],
            'address_id' => ['required', 'exists:addresses,id'],
            'coupon_id' => ['nullable','exists:' . (new Coupon())->getTable() . ',id'],
            'payment_type' => ['required', new Enum(PaymentGateway::class)],
        ];
    }

    public function messages()
    {
        return [
            'address_id.required' => 'The address field is required.',
            'pick_date.date' => 'The pick date is not a valid date.',
            'delivery_date.date' => 'The delivery date is not a valid date.',
        ];
    }
}
