<?php

namespace App\Http\Controllers\Web\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AdminLoginRequest as LoginRequest;
use App\Models\Setting;
use App\Models\WebSetting;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $loginRequest)
    {
        $user = $this->isAuthenticate($loginRequest);
        $loginRequest->only('email', 'password');

        if (!$user) {
            Log::warning('تسجيل الدخول فشل (Admin Login failed)', [
                'email' => $loginRequest->email,
                'reason' => $this->getLoginFailureReason($loginRequest),
            ]);
            return redirect()->back()
                ->withErrors(['email' => ["Invalid credentials"]])
                ->withInput();
        }

        Log::info('تسجيل الدخول ناجح (Admin Login successful)', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        Auth::login($user);
        return redirect()->route('root');
    }

    private function isAuthenticate($loginRequest)
    {
        $user = (new UserRepository())->findByContact($loginRequest->email);
        if (!is_null($user) && $user->is_active && Hash::check($loginRequest->password, $user->password)) {
            return $user;
        }
        return false;
    }

    /**
     * سبب فشل تسجيل الدخول (للتسجيل في الـ log فقط).
     */
    private function getLoginFailureReason(LoginRequest $loginRequest): string
    {
        $user = (new UserRepository())->findByContact($loginRequest->email);
        if (is_null($user)) {
            return 'user_not_found';
        }
        if (!$user->is_active) {
            return 'user_inactive';
        }
        if (!Hash::check($loginRequest->password, $user->password)) {
            return 'wrong_password';
        }
        return 'unknown';
    }

    public function logout()
    {
        $user = auth()->user();
        Auth::logout($user);
        return redirect()->route('login');
    }

    public function privacyPolicy(){
        $websiteName = WebSetting::all();
        $setting = Setting::where('slug', 'privacy-policy')->first();
        return view('auth.privacy-policy',compact('setting','websiteName'));
    }
    public function termsCondition(){
        $websiteName = WebSetting::all();
        $setting = Setting::where('slug', 'trams-of-service')->first();
        return view('auth.terms-condition',compact('setting','websiteName'));
    }
}
