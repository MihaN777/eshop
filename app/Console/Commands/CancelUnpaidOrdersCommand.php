<?php

namespace App\Console\Commands;

use App\Domains\Order\Enums\OrderStatuses;
use App\Domains\Order\States\CancelledOrderState;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CancelUnpaidOrdersCommand extends Command
{
    protected $signature = 'eshop:cancel-unpaid-orders {--minutes=30 : Возраст неоплаченного заказа в минутах}';

    protected $description = 'Отменяет просроченные неоплаченные заказы и возвращает остатки товаров';

    public function handle(): int
    {
        $threshold = now()->subMinutes((int) $this->option('minutes'));

        $orderIds = Order::query()
            ->where('status', OrderStatuses::Pending->value)
            ->where('created_at', '<=', $threshold)
            ->whereDoesntHave('payments', fn ($query) => $query->where('status', 'paid'))
            ->pluck('id');

        $cancelled = 0;

        foreach ($orderIds as $orderId) {
            try {
                DB::transaction(function () use ($orderId, &$cancelled) {
                    $order = Order::query()
                        ->with('orderItems')
                        ->lockForUpdate()
                        ->find($orderId);

                    // Повторная проверка под блокировкой: мог оплатиться параллельно
                    if (!$order || $order->status->value() !== OrderStatuses::Pending->value) {
                        return;
                    }

                    // Возврат остатков
                    foreach ($order->orderItems as $item) {
                        Product::query()
                            ->whereKey($item->product_id)
                            ->increment('quantity', $item->quantity);
                    }

                    $order->status->transitionTo(new CancelledOrderState($order));

                    $cancelled++;
                });
            } catch (Throwable $e) {
                report($e);
                $this->error("Заказ #{$orderId}: {$e->getMessage()}");
            }
        }

        $this->info("Отменено заказов: {$cancelled}");

        return self::SUCCESS;
    }
}
