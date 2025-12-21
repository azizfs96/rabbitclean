<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTPVerifyRequest extends FormRequest
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
            'contact' => 'required|string',
            'otp' => 'required|numeric|digits:4',
            'device_key' => 'nullable|string',
            'device_type' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'contact.required' => 'The email or phone number field is required',
            'otp.required' => 'OTP code is required',
            'otp.digits' => 'OTP must be 4 digits',
        ];
    }
}
