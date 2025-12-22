<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $quantity = collect([]);
        if($this->products && $this->products->count() > 0) {
            foreach($this->products as $product){
                $quantity[$product->id] = (int)$product->pivot->quantity;
            }
        }

        $payment_url = null;
        if($this->payment_status === 'Pending'){
             $payment_url = route('order.payment', ['order' => $this->id, 'gateway' => $this->payment_type,'status' => $this->payment_status]);
        }

        App::setLocale('ar');

        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'driver_status' => null,
            'drivers' => false,
            'isChatAble' => false,
            'customer' => $this->customer ? (new CustomerResource($this->customer)) : null,
            'discount' => $this->discount ? (int) $this->discount : 0,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'total_amount' => $this->total_amount !== null ? (float) $this->total_amount : null,
            'admin_adjusted_amount' => $this->admin_adjusted_amount ? (float) $this->admin_adjusted_amount : null,
            'review_status' => $this->review_status ?? 'not_required',
            'admin_completed' => isset($this->admin_completed) ? (bool) $this->admin_completed : false,
            'sent_to_customer' => isset($this->sent_to_customer) ? (bool) $this->sent_to_customer : false,
            'admin_notes' => $this->admin_notes ?? null,
            'delivery_charge' => (int) ($this->delivery_charge ?? 0),
            'order_status' => $this->order_status,
            'order_status_bn' => config('enums.order_status_labels.' . $this->order_status, $this->order_status),
            'is_active' => $this->isActiveOrder(),
            'is_final' => $this->isFinalOrder(),
            'payment_status' => $this->payment_status,
            'payment_status_bn' => __($this->payment_status),
            'payment_type' => $this->payment_type,
            'payment_type_bn' => __($this->payment_type),
            'pick_date' => Carbon::parse($this->pick_date)->format('d F, Y'),
            'pick_hour' => $this->pick_hour ? $this->getTime($this->extractHour($this->pick_hour)) : null,
            'delivery_date' => Carbon::parse($this->delivery_date)->format('d F, Y'),
            'delivery_hour' => $this->delivery_hour ? $this->getTime($this->extractHour($this->delivery_hour)) : null,
            'ordered_at' => $this->created_at->format('Y-m-d h:i a'),
            'rating' => $this->rating ? $this->rating->rating : null,
            'item' => $this->products ? $this->products->count() : 0,
            'address' => $this->address ? (new AddressResource($this->address)) : null,
            'products' => $this->products ? ProductResource::collection($this->products) : [],
            'quantity' => $quantity->isEmpty() ? null : (object)$quantity->toArray(),
            'order_sub_product' => [],
            'payment' => $this->payment ? (new PaymentResource($this->payment)) : null,
            'payment_url' => $payment_url,
            'services' => $this->whenLoaded('services', function() {
                return ServiceResource::collection($this->services);
            }, [])
        ];
    }

    /**
     * Extract hour from time string (handles Arabic/English formats)
     * Examples: "8:00 ص:00:00" → "8", "12:00 PM" → "12", "8:00:00" → "8"
     */
    private function extractHour($timeString)
    {
        if (!$timeString) {
            return null;
        }
        
        // Remove Arabic AM/PM characters and other non-ASCII
        $cleaned = preg_replace('/[^\d:]/u', '', $timeString);
        
        // Extract the first number (hour)
        if (preg_match('/^(\d{1,2})/', $cleaned, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getTime($time)
    {
        if (!$time) {
            return null;
        }
        
        $times = [
            '8' => '08-09:59',
            '9' => '08-09:59',
            '10' => '10-11:59',
            '11' => '10-11:59',
            '12' => '12-13:59',
            '13' => '12-13:59',
            '14' => '14-15:59',
            '15' => '14-15:59',
            '16' => '16-17:59',
            '17' => '16-17:59',
            '18' => '18-19:59',
            '19' => '18-19:59',
            '20' => '20-21:59',
            '21' => '20-21:59',
        ];
        foreach($times as $key => $item){
            if($key == $time){
                return $item;
            }
        }
        return null;
    }

    /**
     * Check if order is active (should be shown in mobile app active orders)
     */
    private function isActiveOrder(): bool
    {
        $activeStatuses = config('enums.order_status_mobile', []);
        return in_array($this->order_status, array_values($activeStatuses));
    }

    /**
     * Check if order is in a final state (complete or cancelled)
     */
    private function isFinalOrder(): bool
    {
        $finalStatuses = config('enums.order_status_final', []);
        return in_array($this->order_status, array_values($finalStatuses));
    }
}
