<?php

namespace App\Repositories;

use App\Models\VerificationCode;
use App\Models\WebSetting;

class VerificationCodeRepository extends Repository
{
    public function model()
    {
        return VerificationCode::class;
    }

    public function findOrCreateByContact($contact, $purpose = 'login'): VerificationCode
    {
        // Delete expired or old OTPs for this contact
        $this->cleanupExpiredOtps($contact);

        // Get OTP settings from database
        $settings = WebSetting::first();
        $expiryMinutes = $settings?->otp_expiry_minutes ?? 5;

        return $this->model()::updateOrCreate([
            'contact' => $contact,
            'purpose' => $purpose
        ], [
            'otp' => $this->generateUniqueOtp(),
            'token' => $this->generateUniqueToken(),
            'expires_at' => now()->addMinutes($expiryMinutes),
            'attempts' => 0,
            'last_attempt_at' => null,
        ]);
    }

    public function checkCode($contact, $otp, $purpose = 'login'): ?VerificationCode
    {
        // Get OTP settings from database
        $settings = WebSetting::first();
        $maxAttempts = $settings?->otp_max_attempts ?? 5;

        return $this->model()::where(['contact' => $contact, 'otp' => $otp, 'purpose' => $purpose])
            ->where('expires_at', '>', now())
            ->where('attempts', '<', $maxAttempts)
            ->latest()
            ->first();
    }

    public function checkByToken($token)
    {
        return $this->model()::where('token', $token)->latest()->first();
    }

    private function generateUniqueOtp(): int
    {
        do {
            $otp = mt_rand(1000, 9999);
        } while ($this->query()->where('otp', $otp)->exists());

        return $otp;
    }

    private function generateUniqueToken()
    {
        do {
            $token = $this->generateToken();
        } while ($this->query()->where('token', $token)->exists());

        return $token;
    }

    private function generateToken()
    {
        return hash_hmac(
            'sha256',
            uniqid(rand(100000000, 100000000000000), true),
            substr(md5(mt_rand()), 500000000, 700000000000)
        );
    }

    /**
     * Increment attempt counter for OTP verification
     */
    public function incrementAttempts(VerificationCode $code): void
    {
        $code->update([
            'attempts' => $code->attempts + 1,
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Check if OTP can be resent (throttling)
     */
    public function canResendOtp($contact, $purpose = 'login'): bool
    {
        $lastOtp = $this->model()::where('contact', $contact)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return true;
        }

        // Get OTP settings from database
        $settings = WebSetting::first();
        $resendDelaySeconds = $settings?->otp_resend_delay_seconds ?? 60;

        // Allow resend after configured delay
        return $lastOtp->created_at->addSeconds($resendDelaySeconds)->isPast();
    }

    /**
     * Clean up expired OTPs for contact
     */
    private function cleanupExpiredOtps($contact): void
    {
        // Get OTP settings from database
        $settings = WebSetting::first();
        $maxAttempts = $settings?->otp_max_attempts ?? 5;

        $this->model()::where('contact', $contact)
            ->where(function ($query) use ($maxAttempts) {
                $query->where('expires_at', '<', now())
                    ->orWhere('attempts', '>=', $maxAttempts);
            })
            ->delete();
    }

    /**
     * Delete all OTPs for contact after successful verification
     */
    public function clearOtpsForContact($contact): void
    {
        $this->model()::where('contact', $contact)->delete();
    }
}
