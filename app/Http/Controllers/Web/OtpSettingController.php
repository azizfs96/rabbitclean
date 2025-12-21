<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use Illuminate\Http\Request;

class OtpSettingController extends Controller
{
    public function index()
    {
        $settings = WebSetting::first();
        
        return view('otp-setting', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'otp_expiry_minutes' => 'required|integer|min:1|max:60',
            'otp_max_attempts' => 'required|integer|min:1|max:10',
            'otp_resend_delay_seconds' => 'required|integer|min:30|max:300',
        ]);

        $settings = WebSetting::first();
        
        if ($settings) {
            $settings->update([
                'otp_expiry_minutes' => $request->otp_expiry_minutes,
                'otp_max_attempts' => $request->otp_max_attempts,
                'otp_resend_delay_seconds' => $request->otp_resend_delay_seconds,
            ]);
        } else {
            WebSetting::create([
                'name' => 'Default',
                'otp_expiry_minutes' => $request->otp_expiry_minutes,
                'otp_max_attempts' => $request->otp_max_attempts,
                'otp_resend_delay_seconds' => $request->otp_resend_delay_seconds,
            ]);
        }

        return back()->with('success', 'OTP Settings updated successfully');
    }
}
