<?php

namespace App\Models;

use App\Enum\PaymentGateway as EnumPaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;


class PaymentGateway extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get media
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    protected $casts = [
        'name' => EnumPaymentGateway::class,
        'config' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    /**
     * Get logo
     */
    public function logo(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->media && Storage::disk('public')->exists($this->media->src)) {
                    return Storage::disk('public')->url($this->media->src);
                }
                
                $alias = $this->alias ?? 'default';
                return asset('assets/payment-gateway') . '/' . $alias . '.png';
            }
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            Cache::forget('payment_gateway');
        });

        static::updated(function () {
            Cache::forget('payment_gateway');
        });

        static::deleted(function () {
            Cache::forget('payment_gateway');
        });
    }
}
