<?php

namespace App\Http\Controllers\Web\Products;

use PDF;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
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

        $notificationOrder = NotificationManage::where('name', $status)->first();

        // Send Arabic notification to customer
        if ($order->customer?->devices?->count()) {
            $devices = $order->customer->devices;
            
            // Get Arabic notification message and title from config
            $messageTemplate = config('enums.order_status_notifications_ar.' . $status);
            $title = config('enums.order_status_titles_ar.' . $status, 'تحديث حالة الطلب');
            
            // Replace placeholders with actual values
            $message = str_replace(
                [':name', ':order_code', ':amount'],
                [
                    $order->customer->user->first_name ?? $order->customer->name ?? 'عميلنا الكريم',
                    $order->prefix . $order->order_code,
                    number_format($order->total_amount ?? 0, 2)
                ],
                $messageTemplate ?? 'تم تحديث حالة طلبك'
            );

            $tokens = $devices->pluck('key')->toArray();
            (new NotificationServices())->sendNotification($message, $tokens, $title);
            (new NotificationRepository())->storeByRequest($order->customer->id, $message, $title);
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

        // Use delivery charge from request or default to 0
        $deliveryCharge = $request->delivery_charge ?? 0;
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

        // Create transaction for the order if not exists
        if (!$order->transaction) {
            (new TransationRepository())->storeForOrder($order);
        }

        // Update order status to confirmed and set payment status to Pending
        $order->update([
            'sent_to_customer' => true,
            'payment_status' => 'Pending',
            'order_status' => OrderStatus::CREATE_INVOICE->value,
        ]);

        // Refresh order and load customer with devices to ensure fresh data
        $order->refresh();
        $order->load('customer.devices');

        // Send Arabic notification to customer
        if ($order->customer && $order->customer->devices && $order->customer->devices->count() > 0) {
            $devices = $order->customer->devices;
            $tokens = $devices->pluck('key')->toArray();
            
            if (!empty($tokens)) {
                // Get Arabic notification message and title from config
                $status = 'create_invoice';
                $messageTemplate = config('enums.order_status_notifications_ar.' . $status);
                $title = config('enums.order_status_titles_ar.' . $status, 'الفاتورة جاهزة 📄');
                
                // Replace placeholders with actual values
                $message = str_replace(
                    [':name', ':order_code', ':amount'],
                    [
                        $order->customer->user->first_name ?? $order->customer->name ?? 'عميلنا الكريم',
                        $order->prefix . $order->order_code,
                        number_format($order->total_amount ?? 0, 2)
                    ],
                    $messageTemplate ?? 'طلبك جاهز للدفع'
                );
                
                try {
                    (new NotificationServices())->sendNotification($message, $tokens, $title);
                    (new NotificationRepository())->storeByRequest($order->customer->id, $message, $title);
                } catch (\Exception $e) {
                    \Log::error('Failed to send order notification: ' . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Order sent to customer successfully');
    }
}
