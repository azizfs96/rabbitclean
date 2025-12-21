<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MsegatSMS;
use App\Repositories\SMS;
use Illuminate\Http\Request;

class TestSMSController extends Controller
{
    public function index()
    {
        return view('sms-gateway.test');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|min:10',
            'message' => 'required|string|max:160'
        ]);

        try {
            $smsRepo = new SMS();
            $result = $smsRepo->sendSms($request->mobile, $request->message);

            if ($result) {
                return back()->with('success', 'Test SMS sent successfully to ' . $request->mobile);
            } else {
                return back()->with('error', 'Failed to send test SMS. Check logs for details.');
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
}
