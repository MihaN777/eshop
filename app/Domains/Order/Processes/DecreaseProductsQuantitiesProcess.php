<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;
use App\Models\Product;

class DecreaseProductsQuantitiesProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        // Агрегируем требуемое количество по товару
        $required = [];
        foreach (cart()->items() as $item) {
            $required[$item->product_id] = ($required[$item->product_id] ?? 0) + $item->quantity;
        }

        foreach ($required as $productId => $needed) {
            // Атомарное условное списание — единственная точка, гарантирующая отсутствие oversell.
            // Один SQL UPDATE: SET quantity = quantity - N WHERE id = ? AND quantity >= N.
            // Списываем ТОЛЬКО если остатка хватает; иначе affected = 0.
            $affected = Product::query()
                ->whereKey($productId)
                ->where('quantity', '>=', $needed)
                ->decrement('quantity', $needed);

            if ($affected === 0) {
                // Откатит всю транзакцию заказа (Transaction::run -> rollBack).
                throw new OrderProcessException(
                    "Недостаточно товара на складе (id: {$productId}). Остаток изменился, оформите заказ заново."
                );
            }
        }

        return $next($order);
    }
}
