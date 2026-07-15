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

    /**
     * Чистятся только гостевые корзины: они живут ровно столько, сколько сессия.
     * Корзина, привязанная к аккаунту, переживает логаут и прунингу не подлежит.
     * Считаем по активности (updated_at), а не по дате создания, иначе корзина
     * гостя, который всё ещё ей пользуется, была бы удалена прямо под ним.
     */
    public function prunable(): Builder
    {
        return static::query()
            ->whereNull('user_id')
            ->where('updated_at', '<=', now()->subDay());
    }

    // protected function pruning(): void
    // {
    //     // Метод вызываемый перед очистком модели (не вызывается при массовой очистке)
    //     // $this->deleteFile;
    // }
}
