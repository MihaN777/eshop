<?php

namespace App\Http\Requests\Client;

use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class OrderHandleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer.first_name' => ['required'],
            'customer.last_name' => ['required'],
            'customer.email' => $this->boolean('create_account')
                ? ['required', 'unique:users,email', app()->isProduction() ? 'email:dns' : 'email']
                : ['required', app()->isProduction() ? 'email:dns' : 'email'],
            'customer.phone' => ['required', new PhoneRule],
            'customer.city' => ['sometimes'],
            'customer.address' => ['sometimes'],
            'create_account' => ['boolean'],
            'password' => $this->boolean('create_account')
                ? ['required', 'confirmed', Password::defaults()]
                : ['sometimes'],
            'delivery_type_id' => ['required', 'integer', 'exists:delivery_types,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ];
    }
}
