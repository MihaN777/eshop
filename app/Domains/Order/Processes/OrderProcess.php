<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Support\DB\Transaction;
use App\Support\Exceptions\ProjectException;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
use Throwable;

class OrderProcess
{
    protected array $processes = [];

    public function __construct(
        protected Order $order
    )
    {
    }

    public function processes(array $processes): self
    {
        foreach ($processes as $process) {
            if (!($process instanceof OrderProcessContract))
                throw new InvalidArgumentException('Invalid order processes');
        }

        $this->processes = $processes;

        return $this;
    }

    public function run(): Order
    {
        return Transaction::run(
            callback: function () {
                // foreach ($this->processes as $processes) $this->order = $processes($this->order);
                // return $this->order;

                // through(): вызываемый метод pipeline'ом по умолчанию: handle или __invoke
                return app(Pipeline::class)
                    ->send($this->order)
                    ->through($this->processes)
                    ->thenReturn();
            },

            finished: function (Order $order) {
                flash()->info("Создаен заказ с номером {$order->id}");
                event(new OrderCreated($order));
            },

            onError: function (Throwable $e) {
                throw new ProjectException('Не удалось создать заказ', $e->getMessage());
            }
        );
    }
}
