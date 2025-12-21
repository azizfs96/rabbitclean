<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_subscription_id',
        'credit_type',
        'amount',
        'transaction_type',
        'reference_type',
        'reference_id',
        'balance_before',
        'balance_after',
        'notes',
        'created_by',
    ];

    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    const CREDIT_LAUNDRY = 'laundry';
    const CREDIT_CLOTHING = 'clothing';
    const CREDIT_DELIVERY = 'delivery';
    const CREDIT_TOWEL = 'towel';
    const CREDIT_SPECIAL = 'special';

    const REF_ORDER = 'order';
    const REF_SUBSCRIPTION = 'subscription';
    const REF_ADMIN_ADJUSTMENT = 'admin_adjustment';
    const REF_TOPUP = 'topup';
    const REF_EXPIRY = 'expiry';
    const REF_REFUND = 'refund';

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerSubscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCredits($query)
    {
        return $query->where('transaction_type', self::TYPE_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('transaction_type', self::TYPE_DEBIT);
    }

    public function scopeOfType($query, string $creditType)
    {
        return $query->where('credit_type', $creditType);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Helpers
    public function isCredit(): bool
    {
        return $this->transaction_type === self::TYPE_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->transaction_type === self::TYPE_DEBIT;
    }

    public function getSignedAmount(): int
    {
        return $this->isCredit() ? $this->amount : -$this->amount;
    }

    public static function getCreditTypes(): array
    {
        return [
            self::CREDIT_LAUNDRY => 'Laundry',
            self::CREDIT_CLOTHING => 'Clothing',
            self::CREDIT_DELIVERY => 'Delivery',
            self::CREDIT_TOWEL => 'Towel',
            self::CREDIT_SPECIAL => 'Special',
        ];
    }

    public static function getReferenceTypes(): array
    {
        return [
            self::REF_ORDER => 'Order',
            self::REF_SUBSCRIPTION => 'Subscription',
            self::REF_ADMIN_ADJUSTMENT => 'Admin Adjustment',
            self::REF_TOPUP => 'Top Up',
            self::REF_EXPIRY => 'Expiry',
            self::REF_REFUND => 'Refund',
        ];
    }
}
