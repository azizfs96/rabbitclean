<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Transaction;

class TransationRepository extends Repository
{

    public function model()
    {
        return Transaction::class;
    }


    public function storeForOrder(Order $order)
    {
        return $this->create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'payment_method' => $order->payment_type ? $order->payment_type : 'cash',
            'transation_id' => \Str::random(64),
            'payment_status' => false,
        ]);
    }


    public function updateWhenComplatePay(int $orderId): void
    {
        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            throw new \Exception('Transaction not found for Order ID: ' . $orderId);
        }

        $transaction->update([
            'payment_status' => true,
        ]);
    }

}
