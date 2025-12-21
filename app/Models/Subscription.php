<?php

namespace App\Models;

use App\Enum\ValidityTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'validity',
        'validity_type',
        'laundry_credits',
        'clothing_credits',
        'delivery_credits',
        'towel_credits',
        'special_credits',
        'features',
        'is_active',
        'is_featured',
        'sort_order',
        'color',
    ];

    protected $casts = [
        'validity_type' => ValidityTypeEnum::class,
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function customerSubscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // Helpers
    public function getLocalizedName(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    public function getLocalizedDescription(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->description_ar ? $this->description_ar : $this->description;
    }

    public function getTotalCredits(): int
    {
        return $this->laundry_credits + $this->clothing_credits + $this->delivery_credits + 
               $this->towel_credits + $this->special_credits;
    }

    public function getValidityInDays(): int
    {
        return match($this->validity_type) {
            ValidityTypeEnum::DAY => $this->validity,
            ValidityTypeEnum::MONTH => $this->validity * 30,
            ValidityTypeEnum::YEAR => $this->validity * 365,
            default => $this->validity,
        };
    }
}
