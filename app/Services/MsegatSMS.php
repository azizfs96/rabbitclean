<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MsegatSMS
{
    protected $apiKey;
    protected $userSender;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('app.msegat_api_key');
        $this->userSender = config('app.msegat_user_sender');
        $this->baseUrl = config('app.msegat_base_url', 'https://www.msegat.com/gw');
    }

    /**
     * Send SMS via Msegat API
     *
     * @param string $mobile Mobile number (international format)
     * @param string $message Message content
     * @return array Response from API
     * @throws Exception
     */
    public function sendSms($mobile, $message)
    {
        try {
            // Clean and format mobile number
            $mobile = $this->formatMobileNumber($mobile);
            
            // Prepare request data
            $requestData = [
                'apiKey' => $this->apiKey,
                'userSender' => $this->userSender,
                'numbers' => $mobile,
                'msg' => $message,
                'msgEncoding' => 'UTF8', // Support Arabic text
            ];

            // Make HTTP request to Msegat API
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($this->baseUrl . '/sendsms.php', $requestData);

            // Check if request was successful
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Log successful response
                Log::info('Msegat SMS sent successfully', [
                    'mobile' => $mobile,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'response' => $responseData
                ];
            } else {
                // Log failed response
                Log::error('Msegat SMS API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'mobile' => $mobile
                ]);

                throw new Exception('SMS API request failed: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Msegat SMS sending failed', [
                'error' => $e->getMessage(),
                'mobile' => $mobile,
                'message' => $message
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk SMS to multiple numbers
     *
     * @param array $numbers Array of mobile numbers
     * @param string $message Message content
     * @return array Response from API
     */
    public function sendBulkSms(array $numbers, $message)
    {
        try {
            // Format numbers
            $formattedNumbers = array_map([$this, 'formatMobileNumber'], $numbers);
            $numbersString = implode(',', $formattedNumbers);

            // Prepare request data
            $requestData = [
                'apiKey' => $this->apiKey,
                'userSender' => $this->userSender,
                'numbers' => $numbersString,
                'msg' => $message,
                'msgEncoding' => 'UTF8',
            ];

            // Make HTTP request
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($this->baseUrl . '/sendsms.php', $requestData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Msegat Bulk SMS sent successfully', [
                    'numbers_count' => count($numbers),
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'Bulk SMS sent successfully',
                    'response' => $responseData
                ];
            } else {
                throw new Exception('Bulk SMS API request failed: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Msegat Bulk SMS sending failed', [
                'error' => $e->getMessage(),
                'numbers_count' => count($numbers)
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send bulk SMS: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check account balance
     *
     * @return array Balance information
     */
    public function checkBalance()
    {
        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($this->baseUrl . '/credits.php', [
                    'apiKey' => $this->apiKey
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'balance' => $responseData
                ];
            } else {
                throw new Exception('Balance check failed: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Msegat balance check failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to check balance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format mobile number to international format
     *
     * @param string $mobile Mobile number
     * @return string Formatted mobile number
     */
    protected function formatMobileNumber($mobile)
    {
        // Remove any non-numeric characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // If number starts with 05, replace with 9665 (Saudi Arabia format)
        if (substr($mobile, 0, 2) === '05') {
            $mobile = '9665' . substr($mobile, 2);
        }
        // If number starts with 5, add 966 prefix
        elseif (substr($mobile, 0, 1) === '5' && strlen($mobile) === 9) {
            $mobile = '966' . $mobile;
        }
        // If no country code, assume Saudi Arabia and add 966
        elseif (strlen($mobile) === 9 && substr($mobile, 0, 1) === '5') {
            $mobile = '966' . $mobile;
        }

        return $mobile;
    }

    /**
     * Validate API configuration
     *
     * @return bool True if configuration is valid
     */
    public function isConfigured()
    {
        return !empty($this->apiKey) && !empty($this->userSender);
    }

    /**
     * Test API connection
     *
     * @return array Test result
     */
    public function testConnection()
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Msegat API not configured properly'
            ];
        }

        // Test with balance check
        return $this->checkBalance();
    }
}
