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
     * Handles both subscription renewal (is_order=false) and order payment (is_order=true)
     */
    public function initiatePayment(Request $request, Subscription $subscription): JsonResponse
    {
        $isOrder = $request->query('is_order', false);
        $isOrder = filter_var($isOrder, FILTER_VALIDATE_BOOLEAN);
        
        Log::info('Subscription Payment Initiation Started', [
            'subscription_id' => $subscription->id,
            'subscription_name' => $subscription->name,
            'user_id' => auth()->id(),
            'is_order' => $isOrder,
            'request_data' => $request->all(),
        ]);

        // Check if user is authenticated
        if (!auth()->check()) {
            Log::warning('Subscription Payment Failed: User not authenticated');
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        $customer = auth()->user()->customer;

        if (!$customer) {
            Log::warning('Subscription Payment Failed: Customer not found', [
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Customer profile not found. Please complete your profile first.',
                'error_code' => 'CUSTOMER_NOT_FOUND',
            ], 404);
        }

        Log::info('Customer found', ['customer_id' => $customer->id]);

        if (!$subscription->is_active) {
            Log::warning('Subscription Payment Failed: Plan not active', [
                'subscription_id' => $subscription->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'This subscription plan is not available',
                'error_code' => 'PLAN_INACTIVE',
            ], 400);
        }

        // Handle order payment flow (is_order=true)
        if ($isOrder) {
            return $this->handleOrderPayment($request, $subscription, $customer);
        }

        // Handle subscription renewal flow (is_order=false)
        return $this->handleSubscriptionPayment($request, $subscription, $customer);
    }

    /**
     * Handle order payment using subscription credits
     */
    private function handleOrderPayment(Request $request, Subscription $subscription, $customer): JsonResponse
    {
        Log::info('Processing order payment via subscription credits', [
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
        ]);

        // Check if customer has active subscription with credits
        $activeSubscription = $this->subscriptionService->getActiveSubscription($customer);
        
        if (!$activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found. Please purchase a subscription first.',
                'error_code' => 'NO_ACTIVE_SUBSCRIPTION',
            ], 400);
        }

        if ($activeSubscription->credit_balance <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credit balance. Please renew your subscription.',
                'error_code' => 'INSUFFICIENT_CREDITS',
                'credit_balance' => $activeSubscription->credit_balance,
            ], 400);
        }

        // Return success with credit balance info for order processing
        return response()->json([
            'success' => true,
            'message' => 'Credits available for order payment',
            'data' => [
                'credit_balance' => $activeSubscription->credit_balance,
                'subscription_id' => $activeSubscription->id,
                'can_pay_with_credits' => true,
            ],
        ]);
    }

    /**
     * Handle subscription renewal/purchase payment
     */
    private function handleSubscriptionPayment(Request $request, Subscription $subscription, $customer): JsonResponse
    {
        // Check if customer already has an active subscription - allow re-subscription (add credits)
        $existingSubscription = $this->subscriptionService->getActiveSubscription($customer);
        $isResubscription = $existingSubscription !== null;
        
        if ($isResubscription) {
            Log::info('Customer has existing subscription - will add credits after payment', [
                'customer_id' => $customer->id,
                'existing_subscription_id' => $existingSubscription->id,
                'existing_balance' => $existingSubscription->credit_balance,
            ]);
        }

        try {
            // Create pending subscription (will be used to add credits after payment)
            Log::info('Creating pending subscription');
            $customerSubscription = $this->subscriptionService->purchaseSubscription(
                $customer,
                $subscription,
                'paytabs',
                $isResubscription // Pass flag to indicate this is a resubscription
            );
            
            // Store the existing subscription ID for credit addition after payment
            if ($isResubscription) {
                $customerSubscription->update([
                    'notes' => 'Resubscription - add credits to subscription #' . $existingSubscription->id,
                ]);
            }
            
            Log::info('Pending subscription created', ['customer_subscription_id' => $customerSubscription->id]);

            // Get PayTabs configuration
            $paymentGateway = PaymentGateway::where('name', 'paytabs')->first();
            
            if (!$paymentGateway) {
                Log::error('PayTabs gateway not found in database');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured',
                    'error_code' => 'GATEWAY_NOT_FOUND',
                ], 500);
            }
            
            if (!$paymentGateway->is_active) {
                Log::warning('PayTabs gateway is not active');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway is currently disabled',
                    'error_code' => 'GATEWAY_DISABLED',
                ], 400);
            }

            // Debug: Log raw config from database
            Log::info('PayTabs RAW config from database', [
                'raw_config' => $paymentGateway->config,
                'config_type' => gettype($paymentGateway->config),
                'gateway_id' => $paymentGateway->id,
                'gateway_name' => $paymentGateway->name,
                'mode' => $paymentGateway->mode,
            ]);

            // Handle config - it might be a JSON string or already an array
            $configData = $paymentGateway->config;
            if (is_string($configData)) {
                $configData = json_decode($configData, true) ?? [];
            }
            if (!is_array($configData)) {
                $configData = [];
            }
            
            // Add mode to config for PayTabsService
            $configData['mode'] = $paymentGateway->mode ?? 'test';
            
            $config = (object) $configData;
            Log::info('PayTabs config loaded', [
                'has_profile_id' => !empty($config->profile_id ?? null),
                'has_server_key' => !empty($config->server_key ?? null),
                'currency' => $config->currency ?? 'SAR',
                'mode' => $config->mode ?? 'test',
                'all_keys' => array_keys($configData),
            ]);

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

            Log::info('Payment request prepared', [
                'amount' => $subscription->price,
                'order_id' => 'SUB-' . $customerSubscription->id,
            ]);

            // Process payment
            $paymentResult = $this->payTabsService->paymentProcess($paymentRequest, $config);

            Log::info('PayTabs payment result', $paymentResult);

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
            Log::warning('Payment initiation failed, deleting pending subscription', [
                'customer_subscription_id' => $customerSubscription->id,
                'error' => $paymentResult['message'] ?? 'Unknown error',
            ]);
            $customerSubscription->delete();

            return response()->json([
                'success' => false,
                'message' => $paymentResult['message'] ?? 'Payment initiation failed',
                'error_code' => 'PAYMENT_FAILED',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Subscription payment exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment. Please try again.',
                'error_code' => 'EXCEPTION',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
