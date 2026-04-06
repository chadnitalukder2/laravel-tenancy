<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreTenancyRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'company' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:domains',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', Password::defaults()]
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
           'domain' => $this->domain . '.' . config('tenancy.central_domains')[1]
        ]);
    }
}
