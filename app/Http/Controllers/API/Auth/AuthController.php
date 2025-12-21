<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserMailEvent;
use App\Repositories\SMS;
use Illuminate\Http\Response;
use App\Http\Requests\OTPRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ReSendOtpRequest;
use App\Http\Requests\RegistrationRequest;
use App\Repositories\CustomerRepository;
use App\Repositories\DeviceKeyRepository;
use App\Repositories\VerificationCodeRepository;
use App\Http\Requests\OTPLoginRequest;
use App\Http\Requests\OTPVerifyRequest;
use App\Http\Requests\CompleteProfileRequest;

class AuthController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepo;
    /**
     * @var VerificationCodeRepository
     */
    private $verificationCodeRepo;

    public function __construct(UserRepository $userRepo, VerificationCodeRepository $verificationCodeRepository)
    {
        $this->userRepo = $userRepo;
        $this->verificationCodeRepo = $verificationCodeRepository;
    }

    public function register(RegistrationRequest $request)
    {

        $contact = $request->mobile ?? $request->email;

        $user = $this->userRepo->registerUser($request);

        (new CustomerRepository())->storeByUser($user);

        $verificationCode = $this->verificationCodeRepo->findOrCreateByContact($contact);

        #todo create an event send otp to mobile

        $user->assignRole('customer');

        $user->update([
            'mobile_verified_at' => now()
        ]);

        if($request->device_key){
            (new DeviceKeyRepository())->storeByRequest($user->customer, $request);
        }

        $message = "Welcome to laundry \r\n
        Your otp verification code is " . $verificationCode->otp;

        // Send SMS via Msegat
        if ($request->mobile) {
            (new SMS())->sendSms($request->mobile, $message);
        }
        UserMailEvent::dispatch($user, $verificationCode->otp);

        return $this->json('Registration successfully complete' , [
            'user' => new UserResource($user),
            'access' => $this->userRepo->getAccessToken($user),
            'otp' => $verificationCode->otp
        ]);
    }

    public function mobileVerify(OTPRequest $request)
    {
        // $contact = \formatMobile($request->contact);
        $contact = $request->contact;
        $user = $this->userRepo->findByContact($contact);
        $verificationCode = $this->verificationCodeRepo->findOrCreateByContact($contact);

        // Accept 1111 as a master OTP for testing
        if (!is_null($user) && ($verificationCode->otp == $request->otp || $request->otp == '1111')) {
            $verificationCode->delete();
            $user->update([
                'mobile_verified_at' => now()
            ]);
            return $this->json('Mobile verification complete', [
                'user' => new UserResource($user)
            ]);
        }
        return $this->json('Invalid OTP or contact!', [], Response::HTTP_BAD_REQUEST);
    }

    public function login(LoginRequest $request)
    {
        if ($user = $this->authenticate($request)) {
            if ($user->customer) {
                if($request->device_key){
                    (new DeviceKeyRepository())->storeByRequest($user->customer, $request);
                }

                return $this->json('Log In Successfull', [
                    'user' => new UserResource($user),
                    'access' => $this->userRepo->getAccessToken($user)
                ]);
            }
        }
        return $this->json('Credential is invalid!', [], Response::HTTP_BAD_REQUEST);
    }

    public function logout()
    {
        $user = auth()->user();
        if(\request()->device_key){
            (new DeviceKeyRepository())->destroy(\request()->device_key);
        }

        if ($user) {
            $user->token()->revoke();
            return $this->json('Logged out successfully!');
        }
        return $this->json('No Logged in user found', [], Response::HTTP_UNAUTHORIZED);
    }

    private function authenticate(LoginRequest $request)
    {
        $user = $this->userRepo->findActiveBycontact($request->contact);

        if (!is_null($user) && Hash::check($request->password, $user->password)) {
            return $user;
        }

        return false;
    }

    public function resendOTP(ReSendOtpRequest $request)
    {
        $contact = $request->contact;
        $user = $this->userRepo->findByContact($contact);

        if($user){
            $verificationCode = $this->verificationCodeRepo->findOrCreateByContact($contact, 'password_reset');
            $message = "Hello \r\n". $user->name . 'Your password reset otp is '. $verificationCode->otp ;

            // (new SMS())->sendSms($request->contact, $message);
            UserMailEvent::dispatch($user, $verificationCode->otp);

            return $this->json('Verification code is resent success to your contact',[
                'otp' => $verificationCode->otp
            ]);
        }

        return $this->json('Sorry, your contact is not found!');
    }

    /**
     * OTP-based login: Step 1 - Request OTP
     */
    public function requestLoginOtp(OTPLoginRequest $request)
    {
        $contact = $request->contact;

        // Check throttling
        if (!$this->verificationCodeRepo->canResendOtp($contact, 'login')) {
            return $this->json('Please wait 60 seconds before requesting another OTP', [], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Check if user exists
        $user = $this->userRepo->findByContact($contact);
        $isNewUser = is_null($user);

        // Generate OTP
        $verificationCode = $this->verificationCodeRepo->findOrCreateByContact($contact, 'login');

        // Prepare message
        $message = "Welcome to Laundry\r\n";
        $message .= "Your login OTP is: " . $verificationCode->otp;
        $message .= "\r\nValid for 5 minutes.";

        // Send SMS if mobile number
        if (preg_match('/^[0-9+]+$/', $contact)) {
            (new SMS())->sendSms($contact, $message);
        }

        // Send email if exists
        if ($user && $user->email) {
            UserMailEvent::dispatch($user, $verificationCode->otp);
        }

        // Mask contact for response
        $maskedContact = $this->maskContact($contact);

        return $this->json('OTP sent successfully', [
            'masked_contact' => $maskedContact,
            'expires_in' => 300,
            'is_new_user' => $isNewUser,
            'otp' => config('app.env') === 'local' ? $verificationCode->otp : null
        ]);
    }

    /**
     * OTP-based login: Step 2 - Verify OTP and login
     */
    public function verifyLoginOtp(OTPVerifyRequest $request)
    {
        $contact = $request->contact;
        $otp = $request->otp;

        // Accept 1111 as a master OTP for testing
        if ($otp == '1111') {
            // Find or create verification code for this contact
            $verificationCode = $this->verificationCodeRepo->model()::where('contact', $contact)
                ->where('purpose', 'login')
                ->latest()
                ->first();
            
            if (!$verificationCode) {
                $verificationCode = $this->verificationCodeRepo->findOrCreateByContact($contact, 'login');
            }
        } else {
            // Check if OTP is valid
            $verificationCode = $this->verificationCodeRepo->checkCode($contact, $otp, 'login');

            if (!$verificationCode) {
                return $this->json('Invalid or expired OTP', [], Response::HTTP_BAD_REQUEST);
            }
        }

        // Mark OTP as verified
        $verificationCode->update(['verified_at' => now()]);

        // Find or check if user exists
        $user = $this->userRepo->findByContact($contact);

        if (!$user) {
            // New user - needs profile completion
            return $this->json('OTP verified. Please complete your profile', [
                'requires_profile' => true,
                'contact' => $contact
            ], Response::HTTP_CREATED);
        }

        // Existing user - verify mobile and log in
        if (!$user->customer) {
            return $this->json('This account is not a customer account', [], Response::HTTP_FORBIDDEN);
        }

        // Update mobile verification
        $user->update([
            'mobile_verified_at' => now()
        ]);

        // Store device key if provided
        if ($request->device_key) {
            (new DeviceKeyRepository())->storeByRequest($user->customer, $request);
        }

        // Clear all OTPs for this contact
        $this->verificationCodeRepo->clearOtpsForContact($contact);

        return $this->json('Login successful', [
            'user' => new UserResource($user),
            'access' => $this->userRepo->getAccessToken($user)
        ]);
    }

    /**
     * Complete profile for new users after OTP verification
     */
    public function completeProfile(CompleteProfileRequest $request)
    {
        $contact = $request->contact;

        // Parse date_of_birth if provided
        $dateOfBirth = null;
        if ($request->date_of_birth) {
            try {
                // Try to parse the date in various formats
                $dateOfBirth = \Carbon\Carbon::parse($request->date_of_birth)->format('Y-m-d');
            } catch (\Exception $e) {
                // If date parsing fails, just set it to null
                $dateOfBirth = null;
            }
        }

        // Verify contact was OTP verified (check for verified_at timestamp)
        $verificationCode = $this->verificationCodeRepo->model()::where('contact', $contact)
            ->where('purpose', 'login')
            ->whereNotNull('verified_at')
            ->latest()
            ->first();

        if (!$verificationCode) {
            return $this->json('Please verify your contact first', [], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exists
        $existingUser = $this->userRepo->findByContact($contact);
        if ($existingUser) {
            return $this->json('User already exists', [], Response::HTTP_CONFLICT);
        }

        // Determine if contact is mobile or email
        $isMobile = preg_match('/^[0-9+]+$/', $contact);

        // Create user
        $user = $this->userRepo->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile' => $isMobile ? $contact : null,
            'city' => $request->city,
            'neighborhood' => $request->neighborhood,
            'date_of_birth' => $dateOfBirth,
            'gender' => $request->gender,
            'password' => Hash::make(uniqid()),
            'is_active' => true,
            'mobile_verified_at' => now()
        ]);

        // Create customer profile
        (new CustomerRepository())->storeByUser($user);

        // Assign customer role
        $user->assignRole('customer');

        // Store device key if provided
        if ($request->device_key) {
            (new DeviceKeyRepository())->storeByRequest($user->customer, $request);
        }

        // Clear all OTPs for this contact
        $this->verificationCodeRepo->clearOtpsForContact($contact);

        return $this->json('Profile completed successfully', [
            'user' => new UserResource($user),
            'access' => $this->userRepo->getAccessToken($user)
        ]);
    }

    /**
     * Helper method to mask contact for privacy
     */
    private function maskContact($contact)
    {
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $contact);
            $name = $parts[0];
            $domain = $parts[1];
            $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
            return $maskedName . '@' . $domain;
        } else {
            $length = strlen($contact);
            if ($length > 4) {
                return substr($contact, 0, 2) . str_repeat('*', $length - 4) . substr($contact, -2);
            }
            return $contact;
        }
    }
}
