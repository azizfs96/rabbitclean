<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'laundry_credits_remaining',
        'clothing_credits_remaining',
        'delivery_credits_remaining',
        'towel_credits_remaining',
        'special_credits_remaining',
        'credit_balance',       // New: simplified credit balance
        'total_credits_used',   // New: total credits used
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'amount_paid',
        'payment_reference',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'amount_paid' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'total_credits_used' => 'decimal:2',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING = 'pending';

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('end_date', '>=', now()->toDateString());
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereBetween('end_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now()->toDateString())
                     ->where('status', '!=', self::STATUS_EXPIRED);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               $this->end_date >= now()->toDateString();
    }

    public function isExpired(): bool
    {
        return $this->end_date < now()->toDateString();
    }

    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return now()->diffInDays($this->end_date, false);
    }

    public function hasCredits(string $type, int $amount = 1): bool
    {
        $field = $this->getCreditField($type);
        return $this->{$field} >= $amount;
    }

    public function getCredits(string $type): int
    {
        $field = $this->getCreditField($type);
        return $this->{$field} ?? 0;
    }

    public function deductCredits(string $type, int $amount): bool
    {
        $field = $this->getCreditField($type);
        
        if ($this->{$field} < $amount) {
            return false;
        }

        $this->{$field} -= $amount;
        $this->save();
        
        return true;
    }

    public function addCredits(string $type, int $amount): void
    {
        $field = $this->getCreditField($type);
        $this->{$field} += $amount;
        $this->save();
    }

    public function getTotalCreditsRemaining(): int
    {
        return $this->laundry_credits_remaining + 
               $this->clothing_credits_remaining + 
               $this->delivery_credits_remaining + 
               $this->towel_credits_remaining + 
               $this->special_credits_remaining;
    }

    public function getCreditsSummary(): array
    {
        return [
            'laundry' => $this->laundry_credits_remaining,
            'clothing' => $this->clothing_credits_remaining,
            'delivery' => $this->delivery_credits_remaining,
            'towel' => $this->towel_credits_remaining,
            'special' => $this->special_credits_remaining,
            'total' => $this->getTotalCreditsRemaining(),
        ];
    }

    // ===== NEW: Simplified Credit System Methods =====

    /**
     * Get simplified credit balance
     */
    public function getCreditBalance(): float
    {
        return (float) ($this->credit_balance ?? 0);
    }

    /**
     * Check if customer has enough credits for an amount
     */
    public function hasEnoughCredits(float $amount): bool
    {
        return $this->getCreditBalance() >= $amount;
    }

    /**
     * Deduct credit amount (simplified)
     */
    public function deductCreditBalance(float $amount): bool
    {
        if (!$this->hasEnoughCredits($amount)) {
            return false;
        }

        $this->credit_balance -= $amount;
        $this->total_credits_used += $amount;
        $this->save();

        return true;
    }

    /**
     * Add credit amount (simplified)
     */
    public function addCreditBalance(float $amount): void
    {
        $this->credit_balance += $amount;
        $this->save();
    }

    protected function getCreditField(string $type): string
    {
        return match($type) {
            'laundry' => 'laundry_credits_remaining',
            'clothing' => 'clothing_credits_remaining',
            'delivery' => 'delivery_credits_remaining',
            'towel' => 'towel_credits_remaining',
            'special' => 'special_credits_remaining',
            default => throw new \InvalidArgumentException("Invalid credit type: {$type}"),
        };
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    public function markExpired(): void
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();
    }
}
