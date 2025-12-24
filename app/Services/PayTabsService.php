<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayTabsService
{
    private string $baseUrl;
    private string $profileId;
    private string $serverKey;
    private string $currency;

    public function __construct()
    {
        // Load from config (paytabs.php) or from payment_gateways table
        $this->baseUrl = 'https://secure.paytabs.sa'; // SAU region
        $this->profileId = config('paytabs.profile_id', '');
        $this->serverKey = config('paytabs.server_key', '');
        $this->currency = config('paytabs.currency', 'SAR');
    }

    /**
     * Set credentials from database config
     */
    public function setCredentials(object $config): self
    {
        $this->profileId = $config->profile_id ?? $this->profileId;
        $this->serverKey = $config->server_key ?? $this->serverKey;
        $this->currency = $config->currency ?? $this->currency;
        
        // Set base URL based on region
        $region = $config->region ?? 'SAU';
        $this->baseUrl = match($region) {
            'SAU' => 'https://secure.paytabs.sa',
            'ARE' => 'https://secure.paytabs.com',
            'EGY' => 'https://secure-egypt.paytabs.com',
            'OMN' => 'https://secure-oman.paytabs.com',
            'JOR' => 'https://secure-jordan.paytabs.com',
            default => 'https://secure.paytabs.com',
        };
        
        return $this;
    }

    /**
     * Process payment through PayTabs gateway
     *
     * @param object $request Payment request data
     * @param object $config PayTabs configuration
     * @return array Payment response
     */
    public function paymentProcess($request, $config): array
    {
        try {
            // Set credentials from config
            $this->setCredentials($config);

            // Validate required credentials
            if (empty($this->profileId) || empty($this->serverKey)) {
                Log::error('PayTabs credentials missing', [
                    'has_profile_id' => !empty($this->profileId),
                    'has_server_key' => !empty($this->serverKey),
                ]);
                return [
                    'success' => false,
                    'message' => 'PayTabs credentials are not configured. Please configure profile_id and server_key.'
                ];
            }

            // Get customer details from request
            $customerName = $request->customer_name ?? 'Customer';
            $customerEmail = $request->customer_email ?? 'customer@example.com';
            $customerPhone = $request->customer_phone ?? '0000000000';
            $customerAddress = $request->customer_address ?? 'Address';
            $customerCity = $request->customer_city ?? 'Riyadh';
            $customerState = $request->customer_state ?? 'Riyadh';
            $customerCountry = $request->customer_country ?? 'SA';
            $customerZip = $request->customer_zip ?? '00000';
            $customerIP = $request->ip() ?? request()->ip() ?? '127.0.0.1';

            // Cart details
            $amount = (float) $request->paid_amount;
            $description = $request->description ?? 'Order Payment';
            $orderId = $request->order_id ?? uniqid('order_');

            // Build request payload
            $payload = [
                'profile_id' => $this->profileId,
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => $orderId,
                'cart_description' => $description,
                'cart_currency' => $this->currency,
                'cart_amount' => number_format($amount, 2, '.', ''),
                'callback' => $request->callback_url ?? route('subscription.payment.callback'),
                'return' => $request->return_url ?? route('subscription.payment.return'),
                'customer_details' => [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                    'street1' => $customerAddress,
                    'city' => $customerCity,
                    'state' => $customerState,
                    'country' => $customerCountry,
                    'zip' => $customerZip,
                    'ip' => $customerIP,
                ],
                'shipping_details' => [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                    'street1' => $customerAddress,
                    'city' => $customerCity,
                    'state' => $customerState,
                    'country' => $customerCountry,
                    'zip' => $customerZip,
                ],
                'hide_shipping' => true,
                'lang' => $request->language ?? 'en',
            ];

            Log::info('PayTabs API Request', [
                'url' => $this->baseUrl . '/payment/request',
                'cart_id' => $orderId,
                'amount' => $amount,
            ]);

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/request', $payload);

            $result = $response->json();

            Log::info('PayTabs API Response', [
                'status' => $response->status(),
                'response' => $result,
            ]);

            // Check if payment page was created successfully
            if ($response->successful() && isset($result['redirect_url'])) {
                return [
                    'success' => true,
                    'redirect_url' => $result['redirect_url'],
                    'tran_ref' => $result['tran_ref'] ?? null,
                    'message' => 'Payment page created successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to create payment page',
                'error' => $result
            ];

        } catch (Exception $ex) {
            Log::error('PayTabs Payment Error: ' . $ex->getMessage(), [
                'trace' => $ex->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $ex->getMessage()
            ];
        }
    }

    /**
     * Handle payment callback/IPN
     *
     * @param array $response PayTabs callback response
     * @return array Processed response
     */
    public function handleCallback(array $response): array
    {
        try {
            Log::info('PayTabs Callback Received', $response);

            // Validate payment response
            if (isset($response['payment_result'])) {
                $status = strtolower($response['payment_result']['response_status'] ?? '');
                
                if ($status === 'a' || $status === 'approved') {
                    return [
                        'success' => true,
                        'tran_ref' => $response['tran_ref'] ?? null,
                        'cart_id' => $response['cart_id'] ?? null,
                        'amount' => $response['cart_amount'] ?? 0,
                        'currency' => $response['cart_currency'] ?? 'SAR',
                        'message' => 'Payment completed successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment was not successful',
                'response' => $response
            ];

        } catch (Exception $ex) {
            Log::error('PayTabs Callback Error: ' . $ex->getMessage());
            
            return [
                'success' => false,
                'message' => 'Callback processing failed: ' . $ex->getMessage()
            ];
        }
    }

    /**
     * Refund a transaction
     *
     * @param string $tranRef Transaction reference
     * @param string $orderId Order ID
     * @param float $amount Refund amount
     * @param string $reason Refund reason
     * @return array Refund response
     */
    public function refund(string $tranRef, string $orderId, float $amount, string $reason = 'Refund'): array
    {
        try {
            $payload = [
                'profile_id' => $this->profileId,
                'tran_type' => 'refund',
                'tran_class' => 'ecom',
                'cart_id' => $orderId,
                'cart_currency' => $this->currency,
                'cart_amount' => number_format($amount, 2, '.', ''),
                'cart_description' => $reason,
                'tran_ref' => $tranRef,
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/request', $payload);

            $result = $response->json();
            
            if ($response->successful() && isset($result['payment_result']) && $result['payment_result']['response_status'] === 'A') {
                return [
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'refund_data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => 'Refund failed',
                'error' => $result
            ];

        } catch (Exception $ex) {
            Log::error('PayTabs Refund Error: ' . $ex->getMessage());
            
            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $ex->getMessage()
            ];
        }
    }

    /**
     * Query transaction details
     *
     * @param string $tranRef Transaction reference
     * @return array Transaction details
     */
    public function queryTransaction(string $tranRef): array
    {
        try {
            $payload = [
                'profile_id' => $this->profileId,
                'tran_ref' => $tranRef,
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/query', $payload);

            $result = $response->json();
            
            return [
                'success' => $response->successful(),
                'transaction' => $result
            ];

        } catch (Exception $ex) {
            Log::error('PayTabs Query Error: ' . $ex->getMessage());
            
            return [
                'success' => false,
                'message' => 'Transaction query failed: ' . $ex->getMessage()
            ];
        }
    }
}
