<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'customer_subscription_id',
        'amount',
        'currency',
        'payment_gateway',
        'payment_status',
        'transaction_ref',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    const GATEWAY_PAYTABS = 'paytabs';
    const GATEWAY_CASH = 'cash';
    const GATEWAY_BANK_TRANSFER = 'bank_transfer';
    const GATEWAY_WALLET = 'wallet';

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function customerSubscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', self::STATUS_FAILED);
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->payment_status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->payment_status === self::STATUS_FAILED;
    }

    public function markCompleted(string $transactionRef = null, array $gatewayResponse = null): void
    {
        $this->payment_status = self::STATUS_COMPLETED;
        
        if ($transactionRef) {
            $this->transaction_ref = $transactionRef;
        }
        
        if ($gatewayResponse) {
            $this->gateway_response = $gatewayResponse;
        }
        
        $this->save();
    }

    public function markFailed(array $gatewayResponse = null): void
    {
        $this->payment_status = self::STATUS_FAILED;
        
        if ($gatewayResponse) {
            $this->gateway_response = $gatewayResponse;
        }
        
        $this->save();
    }

    public static function getGateways(): array
    {
        return [
            self::GATEWAY_PAYTABS => 'PayTabs',
            self::GATEWAY_CASH => 'Cash',
            self::GATEWAY_BANK_TRANSFER => 'Bank Transfer',
            self::GATEWAY_WALLET => 'Wallet',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }
}
