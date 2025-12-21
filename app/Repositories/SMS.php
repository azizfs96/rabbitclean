<?php

namespace App\Repositories;

use App\Services\MsegatSMS;
use Illuminate\Support\Facades\Log;

/**
 * SMS Repository - Now uses Msegat SMS Service
 * 
 * This class maintains backward compatibility while using the new Msegat SMS service
 */
class SMS
{
    protected $msegatSMS;

    public function __construct()
    {
        $this->msegatSMS = new MsegatSMS();
    }

    /**
     * Send SMS via Msegat
     *
     * @param string $mobile Mobile number
     * @param string $message SMS message content
     * @return bool Success status
     */
    public function sendSms($mobile, $message)
    {
        try {
            $result = $this->msegatSMS->sendSms($mobile, $message);
            
            if ($result['success']) {
                Log::info('SMS sent successfully via Msegat', [
                    'mobile' => $mobile,
                    'message_length' => strlen($message)
                ]);
                return true;
            } else {
                Log::error('SMS sending failed via Msegat', [
                    'mobile' => $mobile,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send bulk SMS via Msegat
     *
     * @param array $numbers Array of mobile numbers
     * @param string $message SMS message content
     * @return bool Success status
     */
    public function sendBulkSms(array $numbers, $message)
    {
        try {
            $result = $this->msegatSMS->sendBulkSms($numbers, $message);
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Bulk SMS sending exception', [
                'numbers_count' => count($numbers),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check SMS service balance
     *
     * @return array Balance information
     */
    public function checkBalance()
    {
        return $this->msegatSMS->checkBalance();
    }

    /**
     * Test SMS service connection
     *
     * @return array Test result
     */
    public function testConnection()
    {
        return $this->msegatSMS->testConnection();
    }

    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use sendSms instead
     */
    private function callApi($params)
    {
        Log::warning('Legacy callApi method called - consider updating to use sendSms');
        return null;
    }
}
