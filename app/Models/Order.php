<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'credits_used' => 'array',
        'paid_via_subscription' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function customerSubscription()
    {
        return $this->belongsTo(CustomerSubscription::class, 'customer_subscription_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_services', 'order_id', 'service_id')
            ->withTimestamps();
    }

    public function transaction(){
        return $this->hasOne(Transaction::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, (new OrderProduct())->getTable())
            ->withPivot('quantity')
            ->withTimestamps()->withTrashed();
    }

    public function subProducts()
    {
        return $this->belongsToMany(SubProduct::class, (new OrderSubProduct())->getTable())
            ->withPivot('quantity', 'product_id')
            ->withTimestamps();
    }

    public function additionals()
    {
        return $this->belongsToMany(Additional::class, (new AdditionalOrder())->getTable());
    }

    public function isPaidViaSubscription(): bool
    {
        return $this->paid_via_subscription && $this->customer_subscription_id !== null;
    }

    public function getCreditsUsedSummary(): string
    {
        if (!$this->credits_used) {
            return '';
        }

        $parts = [];
        foreach ($this->credits_used as $type => $amount) {
            if ($amount > 0) {
                $parts[] = "{$type}: {$amount}";
            }
        }

        return implode(', ', $parts);
    }

    public static function getTime($time)
    {
        $times = [
            '8' => '08 - 09:59',
            '9' => '08 - 09:59',
            '10' => '10 - 11:59',
            '11' => '10 - 11:59',
            '12' => '12 - 13:59',
            '13' => '12 - 13:59',
            '14' => '14 - 15:59',
            '15' => '14 -1 5:59',
            '16' => '16 - 17:59',
            '17' => '16 - 17:59',
            '18' => '18 - 19:59',
            '19' => '18 - 19:59',
            '20' => '20 - 21:59',
            '21' => '20 - 21:59',
        ];
        foreach($times as $key => $item){
            if($key == $time){
                return $item;
            }
        }
    }
}

