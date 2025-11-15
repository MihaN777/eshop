<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    // Очистка модели от старых записей
    use Prunable;

    // Массовая очистка модели от старых записей
    // use MassPrunable;

    protected $fillable = [
        'storage_id',
        'user_id',
    ];

    // Отношения

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Функции модели

    public function prunable(): Builder
    {
        // Запрос для очистка модели
        return static::query()->where('created_at', '<=', now()->subDay());
    }

    // protected function pruning(): void
    // {
    //     // Метод вызываемый перед очистком модели (не вызывается при массовой очистке)
    //     // $this->deleteFile;
    // }
}
