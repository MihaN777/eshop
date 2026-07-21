<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CartAddRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Потолок берём у товара из маршрута: он может переопределять общий лимит.
        // Это быстрая отсечка абсурдных значений; суммарное по товару
        // (с учётом уже лежащего в корзине) проверяет CartManager.
        $max = $this->route('product')->maxOrderQuantity();

        return [
            'quantity' => ['nullable', 'integer', 'min:1', "max:{$max}"],
            'options' => ['nullable', 'array'],
        ];
    }
}
