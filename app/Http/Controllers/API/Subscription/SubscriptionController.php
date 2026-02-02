<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\CustomerSubscriptionResource;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;
    protected CreditService $creditService;

    public function __construct(SubscriptionService $subscriptionService, CreditService $creditService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->creditService = $creditService;
    }

    /**
     * Get all available subscription plans
     */
    public function index(): JsonResponse
    {
        $plans = $this->subscriptionService->getActivePlans();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plans retrieved successfully',
            'data' => SubscriptionResource::collection($plans),
        ]);
    }

    /**
     * Get single subscription plan details
     */
    public function show(Subscription $subscription): JsonResponse
    {
        if (!$subscription->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan retrieved successfully',
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Get customer's current subscription
     */
    public function mySubscription(): JsonResponse
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $subscription = $this->subscriptionService->getActiveSubscription($customer);

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'message' => 'No active subscription',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription retrieved successfully',
            'data' => new CustomerSubscriptionResource($subscription),
        ]);
    }

    /**
     * Purchase a subscription
     * Customer can purchase/renew if:
     * - They have no subscription
     * - Their subscription has expired (end_date passed)
     * - Their credit_balance is 0 or less
     */
    public function purchase(Request $request, Subscription $subscription): JsonResponse
    {
        $request->validate([
            'payment_gateway' => 'nullable|string|in:paytabs,cash,bank_transfer,wallet',
        ]);

        $customer = auth()->user()->customer;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        if (!$subscription->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This subscription plan is not available',
            ], 400);
        }

        // Check if customer has any subscription (active or not)
        $existingSubscription = CustomerSubscription::where('customer_id', $customer->id)
            ->where('status', CustomerSubscription::STATUS_ACTIVE)
            ->first();

        // Block only if subscription is truly active: status=active AND not expired AND has balance
        if ($existingSubscription) {
            $isExpired = $existingSubscription->end_date < now()->toDateString();
            $hasBalance = $existingSubscription->credit_balance > 0;

            // Only block if subscription is not expired AND has credit balance
            if (!$isExpired && $hasBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription with available credits',
                    'data' => [
                        'credit_balance' => $existingSubscription->credit_balance,
                        'end_date' => $existingSubscription->end_date->format('Y-m-d'),
                    ]
                ], 400);
            }
        }

        $paymentGateway = $request->input('payment_gateway', 'paytabs');

        // Create pending subscription
        $customerSubscription = $this->subscriptionService->purchaseSubscription(
            $customer,
            $subscription,
            $paymentGateway
        );

        // If payment gateway is cash, activate immediately (for admin/POS use)
        if ($paymentGateway === 'cash') {
            $this->subscriptionService->activateSubscription($customerSubscription);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully',
            'data' => [
                'subscription' => new CustomerSubscriptionResource($customerSubscription->fresh(['subscription'])),
                'payment_required' => $paymentGateway !== 'cash',
                'amount' => $subscription->price,
                'currency' => 'SAR',
                'is_renewal' => $existingSubscription !== null,
            ],
        ]);
    }

    /**
     * Activate subscription after payment
     */
    public function activate(Request $request, CustomerSubscription $customerSubscription): JsonResponse
    {
        $request->validate([
            'transaction_ref' => 'required|string',
            'gateway_response' => 'nullable|array',
        ]);

        $customer = auth()->user()->customer;

        if ($customerSubscription->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($customerSubscription->status !== CustomerSubscription::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is not pending activation',
            ], 400);
        }

        $this->subscriptionService->activateSubscription(
            $customerSubscription,
            $request->input('transaction_ref'),
            $request->input('gateway_response')
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => new CustomerSubscriptionResource($customerSubscription->fresh(['subscription'])),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(): JsonResponse
    {
        $customer = auth()->user()->customer;
        $subscription = $this->subscriptionService->getActiveSubscription($customer);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription to cancel',
            ], 400);
        }

        $this->subscriptionService->cancelSubscription($subscription);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
        ]);
    }

    /**
     * Toggle auto-renewal
     */
    public function toggleAutoRenew(): JsonResponse
    {
        $customer = auth()->user()->customer;
        $subscription = $this->subscriptionService->getActiveSubscription($customer);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription',
            ], 400);
        }

        $autoRenew = $this->subscriptionService->toggleAutoRenew($subscription);

        return response()->json([
            'success' => true,
            'message' => $autoRenew ? 'Auto-renewal enabled' : 'Auto-renewal disabled',
            'data' => [
                'auto_renew' => $autoRenew,
            ],
        ]);
    }

    /**
     * Get credit balance
     */
    public function creditBalance(): JsonResponse
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $balance = $this->creditService->getBalance($customer);

        return response()->json([
            'success' => true,
            'message' => 'Credit balance retrieved successfully',
            'data' => $balance,
        ]);
    }

    /**
     * Get credit transaction history
     */
    public function creditHistory(Request $request): JsonResponse
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $creditType = $request->input('credit_type');
        $limit = $request->input('limit', 50);

        $transactions = $this->creditService->getTransactionHistory($customer, $creditType, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Credit history retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Get orders paid via subscription
     */
    public function subscriptionOrders(): JsonResponse
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $subscription = $this->subscriptionService->getActiveSubscription($customer);

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'message' => 'No active subscription',
                'data' => [],
            ]);
        }

        $orders = \App\Models\Order::where('customer_subscription_id', $subscription->id)
            ->where('paid_via_subscription', true)
            ->with(['products', 'service'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->prefix . $order->order_code,
                    'total_amount' => $order->total_amount,
                    'credits_used' => $order->credits_used,
                    'status' => $order->order_status,
                    'created_at' => $order->created_at->toIso8601String(),
                    'products_count' => $order->products->sum('pivot.quantity'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Subscription orders retrieved successfully',
            'data' => $orders,
        ]);
    }
}

