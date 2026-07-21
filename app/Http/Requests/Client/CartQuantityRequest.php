<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CartQuantityRequest extends FormRequest
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
        // Потолок количества здесь сознательно не проверяем: правило из route-модели
        // сработало бы ДО проверки владельца строки и по коду ответа выдавало бы
        // существование чужой корзины. Лимит проверяет CartManager — уже после checkOwner().

        // $max = $this->route('cart_item')->product->maxOrderQuantity();

        return [
            'quantity' => ['required', 'integer', 'min:1', /* "max:{$max}" */],
        ];
    }
}
