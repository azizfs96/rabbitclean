<?php

namespace App\Http\Controllers\API\Order;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\OrderRepository;
use App\Events\OrderNotificationEvent;
use App\Services\NotificationServices;
use App\Http\Resources\ScheduleResource;
use App\Models\NotificationManage;
use App\Repositories\ScheduleRepository;
use App\Repositories\TransationRepository;
use App\Models\PaymentGateway;

class OrderController extends Controller
{
    /**
     * Extract hour from time string (handles Arabic/English formats)
     */
    private function extractHour($timeString)
    {
        if (!$timeString) {
            return null;
        }
        
        // Remove Arabic AM/PM characters and other non-ASCII, keep only digits and colon
        $cleaned = preg_replace('/[^\d:]/u', '', $timeString);
        
        // Extract the first number (hour)
        if (preg_match('/^(\d{1,2})/', $cleaned, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }

    public function index()
    {
        $status = config('enums.order_status.' . request('status'));
        $orders = (new OrderRepository())->orderListByStatus($status);

        return $this->json('customer order list', [
            'orders' => OrderResource::collection($orders)
        ]);
    }

    public function store(OrderRequest $request)
    {
        // For simplified workflow: customer sends only service, date/time, address
        // Admin will add products later
        $pickTime = (new OrderRepository())->setPickOrDeliveryTime($request->pick_date, $request->pick_hour);
        $deliveryTime = (new OrderRepository())->setPickOrDeliveryTime($request->delivery_date, $request->delivery_hour, 'delivery');

        // Check if both pickup and delivery time slots are available
        if ($pickTime != null && $deliveryTime != null) {

            $order = (new OrderRepository())->storeByRequest($request);
            // No transaction yet - will be created when admin completes the order
            
            if ($request->has('additional_service_id')) {
                $order->additionals()->sync($request->additional_service_id);
            }

            $message  = 'New order add from ' . $order->customer->name;
            OrderNotificationEvent::dispatch($message);

            // Return order without payment - admin needs to add products first
            return $this->json('Order Successfully', [
                'message' => 'Order is added successfully and waiting for admin to add products',
                'payment_url' => null,
                'payment_type' => null,
                'orders' => OrderResource::make($order),
            ]);
        }

        return $this->json('pick up slot or delivery slot not available', [], Response::HTTP_BAD_REQUEST);
    }

    public function show($id)
    {
        $order = (new OrderRepository())->findById($id);
        return $this->json('order details', [
            'order' => OrderResource::make($order)
        ]);
    }

    public function pickSchedule($date)
    {
        $day = Carbon::parse($date)->format('l');
        $schedule = (new ScheduleRepository())->findByDay($day, 'pickup');

        if (!$schedule) {
            return $this->json('Sorry, Our service is not abailable', [
                'schedules' => [],
            ]);
        }

        $orders = (new OrderRepository())->getByDatePickOrDelivery($date);
        $hours = [];

        $today = date('Y-m-d');
        if ($today == $date) {
            $time = date('G');
            $i = $time % 2 == 0  ? $time : $time + 1;
        } else {
            $i = $schedule->start_time;
        }

        for ($i; $i < ($schedule->end_time - 1); $i += 2) {
            $per = 0;
            foreach ($orders as $order) {
                // Skip orders without pick_hour
                if (!$order->pick_hour) {
                    continue;
                }
                
                $hour = $this->extractHour($order->pick_hour);
                if ($i == $hour) {
                    $per++;
                }
            }
            if ($per < ($schedule->per_hour * 2)) {
                $hours[] = [
                    'hour' => (string) $i . '-' . (string) ($i + 1),
                    'title' => sprintf('%02s', $i) . ':00' . ' - ' . sprintf('%02s', $i + 1) . ':59',
                ];
            }
        }
        $hours = collect($hours);
        return $this->json('picked scheduls', [
            'schedules' => ScheduleResource::collection($hours)
        ]);
    }

    public function deliverySchedule($date)
    {
        $day = Carbon::parse($date)->format('l');
        $schedule = (new ScheduleRepository())->findByDay($day, 'delivery');

        if (!$schedule) {
            return $this->json('Sorry, Our service is not abailable', [
                'schedules' => [],
            ]);
        }

        $orders = (new OrderRepository())->getByDatePickOrDelivery($date, 'delivery');

        $hours = [];
        for ($i = $schedule->start_time; $i < ($schedule->end_time - 1); $i += 2) {
            $per = 0;
            foreach ($orders as $order) {
                // Skip orders without delivery_hour
                if (!$order->delivery_hour) {
                    continue;
                }
                
                $hour = $this->extractHour($order->delivery_hour);
                if ($i == $hour) {
                    $per++;
                }
            }

            if ($per < ($schedule->per_hour * 2)) {
                $hours[] = [
                    'hour' => (string) $i . '-' . (string) ($i + 1),
                    'title' => sprintf('%02s', $i) . ':00' . ' - ' . sprintf('%02s', $i + 1) . ':59'
                ];
            }
        }

        $hours = collect($hours);
        return $this->json('Delivery scheduls', [
            'schedules' => ScheduleResource::collection($hours)
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'products' => 'required|array'
        ]);

        if ($order->order_status == 'pickup') {
            (new OrderRepository())->updateByRequest($request, $order);
            return $this->json('Order is edited successful.');
        }

        return $this->json('Sorry, You can\'t edit this order');
    }

    public function newOrder()
    {
        $orders = (new OrderRepository())->query()->where('is_show', false)->latest('id')->get();
        return $this->json('New Order', [
            'orders' => OrderResource::collection($orders)
        ]);
    }
}
