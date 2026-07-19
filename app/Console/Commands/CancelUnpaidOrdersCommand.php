<?php

namespace App\Console\Commands;

use App\Domains\Order\Enums\OrderStatuses;
use App\Domains\Order\Enums\PaymentStatuses;
use App\Domains\Order\States\CancelledOrderState;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CancelUnpaidOrdersCommand extends Command
{
    protected $signature = 'eshop:cancel-unpaid-orders {--minutes=30 : Возраст неоплаченного заказа в минутах}';

    protected $description = 'Отменяет просроченные неоплаченные заказы и возвращает остатки товаров';

    public function handle(): int
    {
        $threshold = now()->subMinutes((int)$this->option('minutes'));

        $orderIds = Order::query()
            ->where('status', OrderStatuses::Pending->value)
            ->where('created_at', '<=', $threshold)
            ->whereDoesntHave('payments', fn($query) => $query->where('status', 'paid'))
            ->pluck('id');

        $cancelled = 0;

        foreach ($orderIds as $orderId) {
            try {
                if ($this->attemptCancel($orderId)) {
                    $cancelled++;
                }
            } catch (Throwable $e) {
                Log::channel('payment')->error('CancelUnpaidOrdersCommand: ' . $e->getMessage());
                $this->error("Заказ #{$orderId}: {$e->getMessage()}");
            }
        }

        $this->info("Отменено заказов: {$cancelled}");

        return self::SUCCESS;
    }

    /**
     * Отменяет один заказ под блокировкой строки и возвращает остатки.
     * Возвращает true, если заказ был отменён.
     */
    public function attemptCancel(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::query()
                ->with('orderItems')
                ->lockForUpdate()
                ->find($orderId);

            // Перепроверка под локом. Статуса заказа недостаточно: вебхук мог пометить платёж
            // оплаченным между pluck и захватом лока, не переведя заказ (расхождение сумм,
            // частичная оплата). Наличие оплаченного платежа — авторитетный признак оплаты.
            if (
                !$order
                || $order->status->value() !== OrderStatuses::Pending->value
                || $order->payments()->where('status', PaymentStatuses::Paid->value)->exists()
            ) {
                return false;
            }

            // Возврат остатков
            foreach ($order->orderItems as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->increment('quantity', $item->quantity);
            }

            $order->status->transitionTo(new CancelledOrderState($order));

            return true;
        });
    }
}
