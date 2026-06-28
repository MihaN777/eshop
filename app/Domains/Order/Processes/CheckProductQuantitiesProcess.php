<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;
use App\Models\Product;

class CheckProductQuantitiesProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        // Быстрая предпроверка.
        // Гарантию отсутствия oversell даёт не она а атомарное списание в DecreaseProductsQuantitiesProcess.

        // Суммируем по товару
        $required = [];
        foreach (cart()->items() as $item) {
            $required[$item->product_id] = ($required[$item->product_id] ?? 0) + $item->quantity;
        }

        if (empty($required)) {
            throw new OrderProcessException('Корзина пуста');
        }

        $stock = Product::query()
            ->whereIn('id', array_keys($required))
            ->pluck('quantity', 'id');

        foreach ($required as $productId => $needed) {
            $available = $stock[$productId] ?? 0;

            if ($available < $needed) {
                throw new OrderProcessException(
                    "Недостаточно товара (id: {$productId}): в наличии {$available}, требуется {$needed}"
                );
            }
        }

        return $next($order);
    }
}
