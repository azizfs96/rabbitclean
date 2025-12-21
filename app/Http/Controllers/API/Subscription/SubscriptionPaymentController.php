<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\PaymentGateway;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayTabsService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentController extends Controller
{
    protected PayTabsService $payTabsService;
    protected SubscriptionService $subscriptionService;

    public function __construct(PayTabsService $payTabsService, SubscriptionService $subscriptionService)
    {
        $this->payTabsService = $payTabsService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Initiate subscription payment via PayTabs
     */
    public function initiatePayment(Request $request, Subscription $subscription): JsonResponse
    {
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

        // Check if customer already has an active subscription
        if ($this->subscriptionService->hasActiveSubscription($customer)) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription',
            ], 400);
        }

        // Create pending subscription
        $customerSubscription = $this->subscriptionService->purchaseSubscription(
            $customer,
            $subscription,
            'paytabs'
        );

        // Get PayTabs configuration
        $paymentGateway = PaymentGateway::where('name', 'paytabs')->first();
        
        if (!$paymentGateway || !$paymentGateway->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not available',
            ], 400);
        }

        $config = (object) ($paymentGateway->config ?? []);
        $user = auth()->user();

        // Prepare payment request
        $paymentRequest = new Request([
            'paid_amount' => $subscription->price,
            'order_id' => 'SUB-' . $customerSubscription->id,
            'description' => "Subscription: {$subscription->name}",
            'customer_name' => $user->name ?? 'Customer',
            'customer_email' => $user->email ?? 'customer@example.com',
            'customer_phone' => $user->mobile ?? '0000000000',
            'customer_address' => $customer->addresses()->first()?->address ?? 'Address',
            'customer_city' => $customer->addresses()->first()?->city ?? 'Riyadh',
            'customer_state' => 'Riyadh',
            'customer_country' => 'SA',
            'customer_zip' => $customer->addresses()->first()?->postcode ?? '00000',
            'return_url' => route('subscription.payment.return'),
            'callback_url' => route('subscription.payment.callback'),
            'language' => app()->getLocale() == 'ar' ? 'ar' : 'en',
        ]);

        // Process payment
        $paymentResult = $this->payTabsService->paymentProcess($paymentRequest, $config);

        if ($paymentResult['success']) {
            // Update payment record with transaction reference
            SubscriptionPayment::where('customer_subscription_id', $customerSubscription->id)
                ->update([
                    'transaction_ref' => $paymentResult['tran_ref'] ?? null,
                    'gateway_response' => $paymentResult,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated',
                'data' => [
                    'subscription_id' => $customerSubscription->id,
                    'redirect_url' => $paymentResult['redirect_url'],
                    'tran_ref' => $paymentResult['tran_ref'] ?? null,
                ],
            ]);
        }

        // Payment initiation failed - cancel the pending subscription
        $customerSubscription->delete();

        return response()->json([
            'success' => false,
            'message' => $paymentResult['message'] ?? 'Payment initiation failed',
        ], 400);
    }

    /**
     * Handle PayTabs payment callback (IPN)
     */
    public function handleCallback(Request $request): JsonResponse
    {
        Log::info('Subscription Payment Callback', $request->all());

        $response = $request->all();
        $callbackResult = $this->payTabsService->handleCallback($response);

        if ($callbackResult['success']) {
            $cartId = $callbackResult['cart_id'] ?? null;
            
            // Extract subscription ID from cart_id (format: SUB-123)
            if ($cartId && str_starts_with($cartId, 'SUB-')) {
                $subscriptionId = (int) str_replace('SUB-', '', $cartId);
                $customerSubscription = CustomerSubscription::find($subscriptionId);

                if ($customerSubscription && $customerSubscription->status === CustomerSubscription::STATUS_PENDING) {
                    // Activate the subscription
                    $this->subscriptionService->activateSubscription(
                        $customerSubscription,
                        $callbackResult['tran_ref'],
                        $response
                    );

                    Log::info('Subscription activated via callback', [
                        'subscription_id' => $subscriptionId,
                        'tran_ref' => $callbackResult['tran_ref'],
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Subscription activated',
                    ]);
                }
            }
        }

        Log::warning('Subscription payment callback failed', [
            'response' => $response,
            'result' => $callbackResult,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed',
        ], 400);
    }

    /**
     * Handle payment return (redirect from PayTabs)
     */
    public function handleReturn(Request $request)
    {
        $tranRef = $request->input('tranRef');
        $cartId = $request->input('cartId');
        $respStatus = $request->input('respStatus');
        $respMessage = $request->input('respMessage');

        // Check if payment was successful
        if (strtolower($respStatus) === 'a' || strtolower($respStatus) === 'approved') {
            // Extract subscription ID
            if ($cartId && str_starts_with($cartId, 'SUB-')) {
                $subscriptionId = (int) str_replace('SUB-', '', $cartId);
                $customerSubscription = CustomerSubscription::find($subscriptionId);

                if ($customerSubscription && $customerSubscription->status === CustomerSubscription::STATUS_PENDING) {
                    // Activate the subscription
                    $this->subscriptionService->activateSubscription(
                        $customerSubscription,
                        $tranRef,
                        $request->all()
                    );
                }
            }

            // Return success view or redirect
            return view('subscription-payment.success', [
                'message' => 'Your subscription has been activated successfully!',
                'tran_ref' => $tranRef,
            ]);
        }

        // Payment failed
        return view('subscription-payment.failed', [
            'message' => $respMessage ?? 'Payment was not successful',
        ]);
    }

    /**
     * Verify payment status (for mobile app polling)
     */
    public function verifyPayment(Request $request, CustomerSubscription $customerSubscription): JsonResponse
    {
        $customer = auth()->user()->customer;

        if ($customerSubscription->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Refresh from database
        $customerSubscription->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $customerSubscription->status,
                'is_active' => $customerSubscription->isActive(),
            ],
        ]);
    }
}
