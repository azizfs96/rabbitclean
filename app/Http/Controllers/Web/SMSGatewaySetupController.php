<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MsegatSMS;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SMSGatewaySetupController extends Controller
{
    public function index()
    {
        return view('sms-gateway.index');
    }

    public function update(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'user_sender' => 'required|string',
            'base_url' => 'nullable|url',
        ]);

        if(
            $this->setEnv('MSEGAT_API_KEY', $request->api_key) &&
            $this->setEnv('MSEGAT_USER_SENDER', $request->user_sender) &&
            $this->setEnv('MSEGAT_BASE_URL', $request->base_url ?? 'https://www.msegat.com/gw')
        ){
            Artisan::call('config:cache');
            Artisan::call('config:clear');
            
            // Test the configuration
            $smsService = new MsegatSMS();
            $testResult = $smsService->testConnection();
            
            if ($testResult['success']) {
                return back()->with('success', 'Msegat SMS configuration setup successful and connection tested!');
            } else {
                return back()->with('warning', 'Configuration saved but connection test failed: ' . $testResult['message']);
            }
        }

        return back()->with('error' ,'Failed to update configuration. Permission denied.');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|min:10',
            'message' => 'required|string|max:160'
        ]);

        try {
            $smsService = new MsegatSMS();
            $result = $smsService->sendSms($request->mobile, $request->message);

            if ($result['success']) {
                return back()->with('success', 'Test SMS sent successfully to ' . $request->mobile);
            } else {
                return back()->with('error', 'Failed to send test SMS: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'SMS sending failed: ' . $e->getMessage());
        }
    }

    public function checkBalance()
    {
        try {
            $smsService = new MsegatSMS();
            $balance = $smsService->checkBalance();

            if ($balance['success']) {
                return response()->json([
                    'success' => true,
                    'balance' => $balance['balance']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $balance['message']
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check balance: ' . $e->getMessage()
            ]);
        }
    }

    function setEnv($key, $value): bool
    {
        try{
            $envFile = app()->environmentFilePath();
            $str = file_get_contents($envFile);

            $keyPosition = strpos($str, "{$key}=");
            $endOfLinePosition = strpos($str, "\n", $keyPosition);
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

            $str = str_replace($oldLine, "{$key}={$value}", $str);

            $str = substr($str, 0, -1);
            $str .= "\n";

            file_put_contents($envFile, $str);
            return true;
        }catch(Exception $e){
            return false;
        }
    }
}
