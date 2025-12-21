<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Order;
use Carbon\Carbon;

class TransactionRepository extends Repository
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
            'payment_method' => $order->payment_type,
            'transaction_id' => \Str::random(64),
            'payment_status' => false,
        ]);
    }


    public function updateWhenComplatePay(int $orderId): void
    {
        $order = Order::where('id',$orderId)->first();

        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            throw new \Exception('Transaction not found for Order ID: ' . $orderId);
        }

        $transaction->update([
            'payment_status' => true,
        ]);
        $order->update([
            'payment_status' => 'Paid',
        ]);
    }

    public function monthlyTotalTransction()
    {
        $wallet = auth()->user()->wallet;

        $transctions = $wallet->transactions()->whereBetween('created_at', [Carbon::now()->startOfMonth(), now()])->where('wallet_id', $wallet->id)->get();

        $totalTransctions = [];
        foreach ($transctions as $transction) {
            $sum = $wallet->transactions()->whereDate('created_at', '=', $transction->created_at)->sum('amount');
            $totalTransctions[$transction->created_at->format('d-M')] = $sum;
        }

        return $totalTransctions;
    }
}
