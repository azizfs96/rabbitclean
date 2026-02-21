<?php

namespace App\Http\Controllers\Web\Products;

use PDF;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use App\Events\UserMailEvent;
use App\Models\InvoiceManage;
use App\Events\OrderMailEvent;
use App\Models\NotificationManage;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Services\NotificationServices;
use App\Repositories\DeviceKeyRepository;
use App\Repositories\TransationRepository;
use App\Repositories\NotificationRepository;
use App\Enum\OrderStatus;

class OrderController extends Controller
{
    private $orderRepo;
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepo = $orderRepository;
    }

    public function index(Request $request)
    {
        $orders = $this->orderRepo->getSortedByRequest($request);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $quantity = 0;
        foreach ($order->products as $product) {
            $quantity += $product->pivot->quantity;
        }

        $order->update([
            'is_show' => true
        ]);
        
        // Get products and services for adding to order if admin hasn't completed
        $products = Product::with('variant')->get();
        $services = Service::all();
        
        return view('orders.show', compact('order', 'quantity', 'products', 'services'));
    }

    public function statusUpdate(Order $order)
    {
        $status = config('enums.order_status.' . request('status'));

        if (!in_array($status, config('enums.order_status'))) {
            return back()->with('error', 'Invalid status');
        }
        $order = $this->orderRepo->StatusUpdateByRequest($order, $status);

        // تحميل العميل وأجهزته لضمان إرسال الإشعار
        $order->load(['customer.devices', 'customer.user']);

        $messageTemplate = config('enums.order_status_notifications_ar.' . $status);
        $title = config('enums.order_status_titles_ar.' . $status, 'تحديث حالة الطلب');
        $message = str_replace(
            [':name', ':order_code', ':amount'],
            [
                $order->customer?->user?->first_name ?? $order->customer?->name ?? 'عميلنا الكريم',
                $order->prefix . $order->order_code,
                number_format($order->total_amount ?? 0, 2)
            ],
            $messageTemplate ?? 'تم تحديث حالة طلبك'
        );

        // حفظ الإشعار في قاعدة البيانات دائماً (يظهر في التطبيق حتى لو فشل FCM)
        if ($order->customer) {
            (new NotificationRepository())->storeByRequest($order->customer->id, $message, $title);
        }

        // إرسال push عبر FCM إن وُجدت أجهزة مسجلة
        $devices = $order->customer?->devices ?? collect();
        $tokens = collect($devices)->pluck('key')->filter()->values()->toArray();
        if (!empty($tokens)) {
            try {
                (new NotificationServices())->sendNotification($message, $tokens, $title);
            } catch (\Throwable $e) {
                \Log::warning('Order status FCM notification failed: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                    'status' => $status,
                ]);
            }
        }

        return back()->with('success', 'Status updated successfully');
    }

    public function orderPaid(Order $order)
    {
        $order->update([
            'payment_status' => config('enums.payment_status.paid')
        ]);
        return back()->with('success', 'Order payment paid successfully');
    }

    public function printLabels(Order $order)
    {
        $productLabels = collect([]);
        $t = 1;
        foreach ($order->products as $key => $product) {
            for ($i = 0; $i < $product->pivot->quantity; $i++) {
                $productLabels[]    = [
                    'name' => $order->customer->user->name,
                    'code' => $order->prefix . $order->order_code,
                    'date' => Carbon::parse($order->delivery_at)->format('M d, Y'),
                    'title' => $product->name,
                    'label' => $t . '/' . \request('quantity'),
                ];
                $t++;
            }
        }

        $labels = [];
        $i = 0;
        $r = 0;

        foreach ($productLabels as $key => $label) {
            if ($key + 1 == 1 || $key + 1 == $i) {
                $labels[$r] = [];
                $i = $key + 1 == 1 ? $i + 4 : $i + 3;
                $r++;
            }
            $labels[$r - 1][] = $label;
        }

        $pdf = PDF::loadView('pdf.generate-label', compact('labels'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('labels_' . now()->format('H-i-s') . '.pdf');
    }

    public function printInvioce(Order $order)
    {
        $quantity = 0;
        foreach ($order->products as $product) {
            $quantity += $product->pivot->quantity;
        }

        $deliveryCost = null; // DeliveryCost model removed
        $webSetting = WebSetting::first();
        $invoice = InvoiceManage::first();

        if (!$webSetting || !$webSetting->address) {
            return redirect()->route('webSetting.index')->with('error', 'Please fullfill the web setting');
        }

        if ($invoice?->type == 'pos') {

            return view('pdf.posIvoice', compact('quantity', 'order', 'deliveryCost', 'webSetting'));
        }

        $pdf = PDF::loadView('pdf.invoice', compact('order', 'quantity', 'deliveryCost', 'webSetting'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream($order->prefix . $order->order_code . ' - invioce.pdf');
    }

    /**
     * Update order with products (admin adds products to customer order)
     */
    public function updateProducts(Request $request, Order $order)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
        ]);

        // Detach existing products
        $order->products()->detach();

        $totalAmount = 0;
        
        // Attach new products with quantities
        foreach ($request->products as $productData) {
            $product = \App\Models\Product::find($productData['product_id']);
            
            // Use provided price or product's default price
            $price = $productData['price'] ?? $product->price ?? 0;
            $quantity = $productData['quantity'];
            
            $order->products()->attach($productData['product_id'], [
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $totalAmount += ($price * $quantity);
        }

        // Use delivery charge from request, or keep existing (e.g. رسوم حي إضافية من الطلب)
        $deliveryCharge = $request->filled('delivery_charge') ? (float) $request->delivery_charge : (float) ($order->delivery_charge ?? 0);
        // إذا كان عنوان الطلب في حي مسموح مع رسوم إضافية، نتأكد أن رسوم التوصيل تتضمنها
        if ($order->address && $order->address->area) {
            $serviceArea = ServiceArea::where('name', $order->address->area)->first();
            if ($serviceArea && !$serviceArea->is_served && $serviceArea->allow_with_extra_fee) {
                $extraFee = (float) $serviceArea->extra_delivery_fee;
                if ($deliveryCharge < $extraFee) {
                    $deliveryCharge = $extraFee;
                }
            }
        }
        $totalAmount += $deliveryCharge;

        // Update order with products, amounts, and delivery charge
        $order->update([
            'amount' => $totalAmount - $deliveryCharge,
            'total_amount' => $totalAmount,
            'delivery_charge' => $deliveryCharge,
            'admin_completed' => true,
            'admin_notes' => $request->admin_notes ?? null,
        ]);

        return back()->with('success', 'Products added successfully. You can now send this order to the customer.');
    }

    /**
     * Send completed order to customer for payment
     */
    public function sendToCustomer(Order $order)
    {
        if (!$order->admin_completed) {
            return back()->with('error', 'Please add products to the order first');
        }

        $creditMessage = null;
        
        // Apply subscription credits if customer has active subscription
        $customer = $order->customer;
        if ($customer && $order->total_amount > 0) {
            $creditService = app(\App\Services\CreditService::class);
            $balance = $creditService->getSimplifiedBalance($customer);
            
            if ($balance['has_subscription'] && $balance['credit_balance'] > 0) {
                $result = $creditService->applySimplifiedCreditsToOrder($customer, $order);
                
                if ($result['applied']) {
                    $updateData = [
                        'customer_subscription_id' => $result['subscription_id'],
                        'subscription_credit_used' => $result['amount_used'],
                    ];
                    
                    if ($result['partial']) {
                        // Partial payment - some amount still pending
                        $updateData['paid_via_subscription'] = true;
                        $updateData['payment_status'] = 'Partial';
                        $creditMessage = "تم خصم {$result['amount_used']} ريال من رصيد الاشتراك. متبقي للدفع: {$result['remaining_to_pay']} ريال";
                    } else {
                        // Full payment via subscription
                        $updateData['paid_via_subscription'] = true;
                        $updateData['payment_status'] = 'paid';
                        $creditMessage = "تم الدفع بالكامل من رصيد الاشتراك: {$result['amount_used']} ريال";
                    }
                    
                    $order->update($updateData);
                    
                    \Log::info('Subscription credits applied to order', [
                        'order_id' => $order->id,
                        'amount_used' => $result['amount_used'],
                        'partial' => $result['partial'],
                        'remaining_to_pay' => $result['remaining_to_pay'],
                        'remaining_balance' => $result['remaining_balance'],
                    ]);
                }
            } elseif ($balance['has_subscription'] && $balance['credit_balance'] <= 0) {
                $creditMessage = "رصيد الاشتراك = 0. سيتم إرسال فاتورة للعميل.";
            }
        }

        // Create transaction for the order if not exists
        if (!$order->transaction) {
            (new TransationRepository())->storeForOrder($order);
        }

        // Update order status to confirmed and set payment status (if not paid via subscription)
        $updateData = [
            'sent_to_customer' => true,
            'order_status' => OrderStatus::CREATE_INVOICE->value,
        ];
        
        // Only set payment_status to Pending if not already set
        if (!$order->paid_via_subscription) {
            $updateData['payment_status'] = 'Pending';
        }
        
        $order->update($updateData);

        // Refresh order and load customer with devices to ensure fresh data
        $order->refresh();
        $order->load(['customer.devices', 'customer.user']);

        $status = 'create_invoice';
        $messageTemplate = config('enums.order_status_notifications_ar.' . $status);
        $title = config('enums.order_status_titles_ar.' . $status, 'الفاتورة جاهزة 📄');
        $message = str_replace(
            [':name', ':order_code', ':amount'],
            [
                $order->customer?->user?->first_name ?? $order->customer?->name ?? 'عميلنا الكريم',
                $order->prefix . $order->order_code,
                number_format($order->total_amount ?? 0, 2)
            ],
            $messageTemplate ?? 'طلبك جاهز للدفع'
        );

        // حفظ الإشعار في قاعدة البيانات دائماً
        if ($order->customer) {
            (new NotificationRepository())->storeByRequest($order->customer->id, $message, $title);
        }

        // إرسال push عبر FCM إن وُجدت أجهزة مسجلة
        $devices = $order->customer?->devices ?? collect();
        $tokens = collect($devices)->pluck('key')->filter()->values()->toArray();
        if (!empty($tokens)) {
            try {
                (new NotificationServices())->sendNotification($message, $tokens, $title);
            } catch (\Throwable $e) {
                \Log::warning('Create invoice FCM notification failed: ' . $e->getMessage(), ['order_id' => $order->id]);
            }
        }

        $successMessage = 'Order sent to customer successfully';
        if ($creditMessage) {
            $successMessage .= ' - ' . $creditMessage;
        }
        
        return back()->with('success', $successMessage);
    }
}
