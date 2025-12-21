<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\MobileAppUrl;
use App\Models\PaymentGateway;
use App\Models\WebSetting;

class masterController extends Controller
{
    public function index()
    {

        $websetting = WebSetting::first();
        $currency = $websetting?->currency ?? config('enums.currency');
        $paymentGateway = PaymentGateway::where('is_active', true)->get();

        $costFee = 0.0;
        $fee_cost = 100;
        $mini_cost = 0;

        $mobileAppLink = MobileAppUrl::first();

        return $this->json('',[
            'currency' => $currency,
            'currency_position' => config('app.currency_position'),
            'delivery_cost' => $costFee,
            'fee_cost' => $fee_cost,
            'minimum_cost' => $mini_cost,
            'payment_gateway' => PaymentGatewayResource::collection($paymentGateway),
            'post_code' => config('enums.post_code'),
            'android_url' => $mobileAppLink ? $mobileAppLink->android_url : '',
            'ios_url' => $mobileAppLink ? $mobileAppLink->ios_url : '',
            'otp_settings' => [
                'expiry_minutes' => $websetting?->otp_expiry_minutes ?? 5,
                'max_attempts' => $websetting?->otp_max_attempts ?? 5,
                'resend_delay_seconds' => $websetting?->otp_resend_delay_seconds ?? 60,
            ],
            'order_review_settings' => [
                'review_mode' => $websetting?->order_review_mode ?? false,
                'review_message' => $websetting?->order_review_message ?? 'We will review your order and enable you to pay later.',
            ]
        ]);

    }
}
