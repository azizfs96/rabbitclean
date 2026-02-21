<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationServices
{
    /** @var \Kreait\Firebase\Contract\Messaging|null */
    private $firebaseMessaging;

    /**
     * مسار ملف اعتماد Firebase (من الإعدادات أو التخزين).
     * لا يُنشأ اتصال FCM إلا إذا وُجد الملف.
     */
    public function __construct()
    {
        $credentialPath = config('services.firebase.credentials');

        if ($credentialPath && is_file($credentialPath)) {
            try {
                $this->firebaseMessaging = (new Factory)
                    ->withServiceAccount($credentialPath)
                    ->createMessaging();
            } catch (\Throwable $e) {
                \Log::warning('Firebase Messaging init failed: ' . $e->getMessage());
                $this->firebaseMessaging = null;
            }
        } else {
            $this->firebaseMessaging = null;
        }
    }

    /**
     * هل خدمة FCM متاحة (ملف الاعتماد موجود ومُحمّل).
     */
    public function isAvailable(): bool
    {
        return $this->firebaseMessaging !== null;
    }

    /**
     * إرسال إشعار لجهاز واحد.
     */
    public function send(string $deviceToken, string $title, string $message): void
    {
        $this->ensureMessaging();
        $msg = CloudMessage::new()
            ->withNotification(Notification::create($title, $message));
        $this->firebaseMessaging->send($msg->withChangedTarget('token', $deviceToken));
    }

    /**
     * إرسال إشعار لعدة أجهزة.
     */
    public function sendNotification(string $message, array $deviceTokens, string $title): void
    {
        if (empty($deviceTokens)) {
            return;
        }
        $this->ensureMessaging();
        $msg = CloudMessage::new()
            ->withNotification(Notification::create($title, $message));
        $this->firebaseMessaging->sendMulticast($msg, $deviceTokens);
    }

    /**
     * التأكد من توفر FCM؛ إن لم يُعد يُرمى استثناء واضح.
     *
     * @throws \InvalidArgumentException
     */
    private function ensureMessaging(): void
    {
        if ($this->firebaseMessaging !== null) {
            return;
        }

        $path = config('services.firebase.credentials', '');
        $hint = $path
            ? "الملف غير موجود أو غير مقروء: {$path}"
            : 'لم يتم تعيين مسار ملف اعتماد Firebase.';

        throw new \InvalidArgumentException(
            'إشعارات FCM غير مُعدّة. يرجى رفع ملف خدمة Firebase (JSON) وتحديد المسار في .env كـ FIREBASE_CREDENTIALS_PATH، أو وضع الملف في: ' . storage_path('app/firebase_credentials.json') . '. ' . $hint
        );
    }
}
