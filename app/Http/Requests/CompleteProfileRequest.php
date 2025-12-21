<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'city' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'device_key' => 'nullable|string',
            'device_type' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'contact.required' => 'Contact is required',
            'first_name.required' => 'First name is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'date_of_birth.date' => 'The date of birth is not a valid date',
        ];
    }
}
