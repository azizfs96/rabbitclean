<?php

namespace App\Services;

use Paytabscom\Laravel_paytabs\Facades\paypage;
use Exception;
use Illuminate\Support\Facades\Log;

class PayTabsService
{
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
            // Get customer details from request
            $customerName = $request->customer_name ?? 'Customer';
            $customerEmail = $request->customer_email ?? 'customer@example.com';
            $customerPhone = $request->customer_phone ?? '0000000000';
            $customerAddress = $request->customer_address ?? 'Address';
            $customerCity = $request->customer_city ?? 'City';
            $customerState = $request->customer_state ?? 'State';
            $customerCountry = $request->customer_country ?? 'SA'; // Saudi Arabia default
            $customerZip = $request->customer_zip ?? '00000';
            $customerIP = $request->ip() ?? '127.0.0.1';

            // Cart details
            $amount = $request->paid_amount;
            $currency = $config->currency ?? 'SAR'; // Saudi Riyal default
            $description = $request->description ?? 'Order Payment';
            $orderId = $request->order_id ?? uniqid('order_');

            // Create payment page
            $pay = paypage::sendPaymentCode($config->payment_methods ?? 'all')
                ->sendTransaction('sale', $config->transaction_class ?? 'ecom')
                ->sendCart($orderId, $amount, $description)
                ->sendCustomerDetails(
                    $customerName,
                    $customerEmail,
                    $customerPhone,
                    $customerAddress,
                    $customerCity,
                    $customerState,
                    $customerCountry,
                    $customerZip,
                    $customerIP
                )
                ->sendShippingDetails(
                    $customerName,
                    $customerEmail,
                    $customerPhone,
                    $customerAddress,
                    $customerCity,
                    $customerState,
                    $customerCountry,
                    $customerZip,
                    $customerIP
                )
                ->sendURLs(
                    $request->return_url ?? route('payment.success'),
                    $request->callback_url ?? route('payment.callback')
                )
                ->sendLanguage($request->language ?? 'en')
                ->create_pay_page();

            // Check if payment page was created successfully
            if (isset($pay['redirect_url'])) {
                return [
                    'success' => true,
                    'redirect_url' => $pay['redirect_url'],
                    'tran_ref' => $pay['tran_ref'] ?? null,
                    'message' => 'Payment page created successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $pay['message'] ?? 'Failed to create payment page',
                'error' => $pay
            ];

        } catch (Exception $ex) {
            Log::error('PayTabs Payment Error: ' . $ex->getMessage());
            
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
            // Validate payment response
            if (isset($response['payment_result'])) {
                $status = strtolower($response['payment_result']['response_status']);
                
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
            $refund = paypage::refund($tranRef, $orderId, $amount, $reason);
            
            if (isset($refund['payment_result']) && $refund['payment_result']['response_status'] === 'A') {
                return [
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'refund_data' => $refund
                ];
            }

            return [
                'success' => false,
                'message' => 'Refund failed',
                'error' => $refund
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
            $transaction = paypage::queryTransaction($tranRef);
            
            return [
                'success' => true,
                'transaction' => $transaction
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
