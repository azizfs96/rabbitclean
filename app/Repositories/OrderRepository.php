<?php


namespace App\Repositories;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\Additional;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Enum\PaymentType;
use App\Models\Product;
use App\Models\WebSetting;
use Carbon\Carbon;

class OrderRepository extends Repository
{
    public function model()
    {
        return Order::class;
    }

    public function getByStatus($status)
    {
        return $this->query()->where('order_status', $status)
            ->where('payment_status', config('enums.payment_status.paid'))
            ->get();
    }
    public function getByTodays()
    {
        return $this->model()::whereDate('created_at', Carbon::today())->get();
    }

    public function storeByRequest(OrderRequest $request): Order
    {

        $lastOrder = $this->query()->latest('id')->first();
        $customer = auth()->user()->customer;
        
        // Check if order review mode is enabled
        $webSetting = WebSetting::first();
        $reviewMode = $webSetting?->order_review_mode ?? false;

        // For simplified workflow: products are optional (admin adds them later)
        $hasProducts = $request->has('products') && is_array($request->products) && count($request->products) > 0;
        
        if ($hasProducts) {
            $getAmount = $this->getAmount($request);
        } else {
            $getAmount = [
                'subTotal' => 0,
                'total' => 0,
                'deliveryCharge' => 0,
                'discount' => 0,
            ];
        }

        // Handle service_id - support both single value (backward compatibility) and array
        $firstServiceId = null;
        if ($request->has('service_id')) {
            if (is_array($request->service_id)) {
                $firstServiceId = !empty($request->service_id) ? $request->service_id[0] : null;
            } else {
                $firstServiceId = $request->service_id;
            }
        }

        $order = $this->create([
            'customer_id' => $customer->id,
            'order_code' => str_pad($lastOrder ? $lastOrder->id + 1 : 1, 6, "0", STR_PAD_LEFT),
            'prefix' => 'LM',
            'service_id' => $firstServiceId,
            'coupon_id' => $request->coupon_id,
            'discount' => $getAmount['discount'],
            'pick_date' => $request->pick_date,
            'delivery_date' => $request->delivery_date,
            'pick_hour' => $this->setPickOrDeliveryTime($request->pick_date, $request->pick_hour),
            'delivery_hour' => $this->setPickOrDeliveryTime($request->delivery_date, $request->delivery_hour, 'delivery'),
            'amount' => $hasProducts ? $getAmount['subTotal'] : null,
            'total_amount' => $hasProducts ? $getAmount['total'] : null,
            'delivery_charge' => $getAmount['deliveryCharge'],
            'payment_status' => config('enums.payment_status.pending'),
            'payment_type' => $request->payment_type ?? 'cash',
            'order_status' => config('enums.order_status.pickup'),
            'address_id' => $request->address_id,
            'instruction' => $request->note ?? $request->instruction,
            'admin_completed' => $hasProducts,
            'sent_to_customer' => false,
            'review_status' => $reviewMode
                ? config('enums.review_status.pending_review.value')
                : config('enums.review_status.not_required.value'),
        ]);

        // Attach multiple services if provided as array
        if ($request->has('service_id') && is_array($request->service_id) && !empty($request->service_id)) {
            $order->services()->sync($request->service_id);
        }

        // Only attach products if they exist
        if ($hasProducts) {
            foreach ($request->products as $product) {
                $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);
            }
        }

        // Handle subscription payment - deduct from credit balance
        if ($request->payment_type === 'subscription') {
            $this->applySubscriptionCreditsToOrder($order, $customer);
        }

        return $order;
    }

    /**
     * Apply subscription credits to an order (simplified - single credit balance)
     */
    protected function applySubscriptionCreditsToOrder(Order $order, $customer): void
    {
        $creditService = app(\App\Services\CreditService::class);
        $balance = $creditService->getBalance($customer);

        if (!$balance['has_subscription'] || $balance['credit_balance'] <= 0) {
            return;
        }

        // Use simplified credit system
        $creditsResult = $creditService->applySimplifiedCreditsToOrder($customer, $order);

        if ($creditsResult['applied']) {
            $order->update([
                'customer_subscription_id' => $creditsResult['subscription_id'],
                'subscription_credit_used' => $creditsResult['amount_used'],
                'paid_via_subscription' => true,
                'payment_status' => $creditsResult['partial'] 
                    ? config('enums.payment_status.pending') // Partial - needs additional payment
                    : config('enums.payment_status.paid'),   // Full - paid via credits
            ]);
        }
    }
    public function PosStoreByRequest(Request $request): Order
    {
        $lastOrder = $this->query()->max('id');

        $products = $request->products;

        $totalAmount =0;

        foreach($products as $product){
            $totalAmount += ($product['quantity']) * ($product['price']);
        }
        $grandTotal= ($totalAmount+$request->delivery_charge)-$request->discount;

        $order = $this->create([
            'customer_id' => $request->customer_id ?? null,
            'order_code' => str_pad($lastOrder + 1, 6, '0', STR_PAD_LEFT),
            'prefix' => 'LM',
            'pick_date' => now()->format('Y-m-d'),
            'pick_hour' => now()->format('H:00:00'),
            'delivery_date' => now()->format('Y-m-d'),
            'delivery_hour' => now()->format('H:00:00'),
            'delivery_charge'=>(float) $request->delivery_charge,
            'discount'=>(float) $request->discount,
            'amount' => (float)$totalAmount,
            'total_amount' => (float)$grandTotal,
            'payment_status' =>$request->payment_status ? $request->payment_status:config('enums.payment_status.pending'),
            'payment_type' => $request->payment_id? $request->payment_id : 'cash',
            'order_status' => OrderStatus::PICKUP->value,
            'address_id' => $request->address_id ?? 1,
            'instruction' => $request->instruction ?? null,
        ]);


        foreach ($products as $product) {
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);
        }

        return $order;
    }


    public function updateByRequest($request, Order $order)
    {
        $request['coupon_id'] = $order->coupon_id;
        $getAmount = $this->getAmount($request);

        $this->update($order, [
            'discount' => $getAmount['discount'],
            'amount' => $getAmount['subTotal'],
            'total_amount' => $getAmount['total'],
        ]);
        $order->products()->detach($order->products->pluck('id')->toArray());

        foreach ($request->products as $product) {
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);
        }

        return $order;
    }

    private function getAmount($request): array
    {


        $totalAmount = 0;
        foreach ($request->products as $item) {
                $product = Product::where('id',$item['id'])->first();
                $price = $product->discount_price ? $product->discount_price : $product->price;
                $totalAmount += (float)$item['quantity'] * $price;
        }

        $totalServiceAmount = 0;
        if ($request->has('additional_service_id')) {
            $totalServiceAmount = Additional::whereIn('id', $request->additional_service_id)->get()->sum('price');
        }

        $total = ($totalAmount + $totalServiceAmount);
        $coupon = (new CouponRepository())->findById($request->coupon_id);
        $couponDiscount = $coupon ? $coupon->calculate($total, $coupon) : 0;

        // Default delivery cost values
        $freeDelivery = 0;
        $deliveryCharge = 0;

        $total = $total <= $freeDelivery ? $total + $deliveryCharge : $total;
        $total = $total - $couponDiscount;

        return [
            'total' => $total,
            'discount' => $couponDiscount,
            'subTotal' => ($totalAmount + $totalServiceAmount),
            'deliveryCharge' => $deliveryCharge
        ];
    }

    public function getSortedByRequest(Request $request)
    {
        $status = $request->status;
        $reviewStatus = $request->review_status;
        $searchKey = $request->search;

        $orders = $this->model()::query();

        if ($status) {
            $status = config('enums.order_status.' . $status);
            $orders = $orders->where('order_status', $status);
        }

        if ($reviewStatus) {
            $reviewStatusValue = config('enums.review_status.' . $reviewStatus . '.value', $reviewStatus);
            $orders = $orders->where('review_status', $reviewStatusValue);
        }

        if ($searchKey) {
            $orders = $orders->where(function ($query) use ($searchKey) {
                $query->orWhere('order_code', 'like', "%{$searchKey}%")
                    ->orWhereHas('customer', function ($customer) use ($searchKey) {
                        $customer->whereHas('user', function ($user) use ($searchKey) {
                            $user->where('first_name', $searchKey)
                                ->orWhere('last_name', $searchKey)
                                ->orWhere('mobile', $searchKey);
                        });
                    })
                    ->orWhere('prefix', 'like', "%{$searchKey}%")
                    ->orWhere('amount', 'like', "%{$searchKey}%")
                    ->orWhere('payment_status', 'like', "%{$searchKey}%")
                    ->orWhere('order_status', 'like', "%{$searchKey}%");
            });
        }
        return $orders->latest()->get();
    }

    public function orderListByStatus($status = null)
    {
        $customer = auth()->user()->customer;
        $orders = $this->query()
            ->with(['products', 'services', 'customer.user', 'customer.addresses', 'address', 'payment', 'rating'])
            ->where('customer_id', $customer->id);

        if ($status) {
            $orders = $orders->where('order_status', $status);
        }

        return $orders->latest()->get();
    }

    public function statusUpdateByRequest(Order $order, $status): Order
    {
        $order->update([
            'order_status' => $status,
        ]);

        return $order;
    }

    public function getRevenueReportByBetweenDate($form, $to)
    {
        return  $this->model()::whereBetween('delivery_date', [$form, $to])
            ->where('order_status', config('enums.order_status.complete'))
            ->get();
    }

    public function getRevenueReport()
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        $orders = $this->model()::where('order_status', config('enums.order_status.complete'));
        if (request()->type == 'month') {

            $orders = $orders->whereMonth('delivery_date', $month)
                ->whereYear('delivery_date', $year);
        } elseif (request()->type  ==  'year') {

            $orders = $orders->whereYear('delivery_date', $year);
        } elseif (request()->type == 'week') {

            $end = now()->format('Y-m-d');
            $start = now()->subWeek()->format('Y-m-d');
            $orders = $orders->whereBetween('delivery_date', [$start, $end]);
        } else {

            $date = now()->format('Y-m-d');
            $orders = $orders->where('delivery_date', $date);
        }
        return  $orders->get();
    }

    public function getByDatePickOrDelivery($date, $type = 'picked')
    {
        $orders = $this->model()::query();

        if ($type == 'picked') {
            $orders = $orders->where('pick_date', $date);
        }

        if ($type == 'delivery') {
            $orders = $orders->where('delivery_date', $date);
        }

        return $orders->get();
    }

    public function findById($id)
    {
        return $this->model()::with(['products', 'services', 'customer.user', 'customer.addresses', 'address', 'payment', 'rating'])->find($id);
    }

    /**
     * Convert Arabic time string to 24-hour format
     * Examples: "8:00 ص" -> "08:00", "1:00 م" -> "13:00"
     */
    private function convertArabicTimeTo24Hour($time)
    {
        $time = trim($time);
        
        // Remove any extra characters and normalize
        $time = preg_replace('/\s+/', ' ', $time);
        
        // Check for Arabic AM (ص) or PM (م)s       
        $isPM = mb_strpos($time, 'م') !== false;
        $isAM = mb_strpos($time, 'ص') !== false;
        
        // Extract just the numeric hour part
        preg_match('/(\d{1,2})(?::(\d{2}))?/', $time, $matches);
        
        if (empty($matches)) {
            return '08:00'; // Default fallback
        }
        
        $hour = (int) $matches[1];
        $minutes = isset($matches[2]) ? $matches[2] : '00';
        
        // Convert to 24-hour format
        if ($isPM && $hour < 12) {
            $hour += 12;
        } elseif ($isAM && $hour == 12) {
            $hour = 0;
        }
        
        return sprintf('%02d:%s', $hour, $minutes);
    }

    public function setPickOrDeliveryTime($date, $times, $type = 'picked')
    {
        if (empty($times)) {
            return null;
        }
        
        // Split by hyphen to get time range (e.g., "8:00 ص- 12:00 م")
        $timeParts = explode('-', $times);
        $firstTime = isset($timeParts[0]) ? $this->convertArabicTimeTo24Hour($timeParts[0]) : null;

        foreach ($timeParts as $time) {
            $converted24Hour = $this->convertArabicTimeTo24Hour($time);
            $orders = $this->model()::query();
            
            if ($type == 'picked') {
                $orders = $orders->where('pick_date', $date)->where('pick_hour', 'LIKE', "$converted24Hour%");
            }

            if ($type == 'delivery') {
                $orders = $orders->where('delivery_date', $date)->where('delivery_hour', 'LIKE', "$converted24Hour%");
            }

            if ($orders->count() < 2) {
                // Return time in 24-hour format: HH:MM:SS
                return $converted24Hour . ':' . sprintf('%02d', ($orders->count() * 30));
            }
        }
        
        // If no slot is available, return the first time anyway so the field is not null
        if ($firstTime) {
            return $firstTime . ':00';
        }
        
        return '08:00:00'; // Safe fallback
    }
}
