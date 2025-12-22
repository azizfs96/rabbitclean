<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PICKUP = 'pickup';                             // Step 1 - Pickup
    case CREATE_INVOICE = 'create_invoice';             // Step 2 - Create Invoice
    case PROCESSING = 'processing';                     // Step 3 - Processing
    case READY = 'ready';                               // Step 4 - Ready
    case COMPLETE = 'complete';                         // Final - Complete
    case CANCELLED = 'cancelled';                       // Final - Cancelled

    /**
     * Get Arabic label for this status
     */
    public function label(): string
    {
        return match($this) {
            self::PICKUP => 'جاري التحصيل',
            self::CREATE_INVOICE => 'إنشاء الفاتورة',
            self::PROCESSING => 'جاري المعالجة',
            self::READY => 'جاهز',
            self::COMPLETE => 'مكتمل',
            self::CANCELLED => 'ملغي',
        };
    }

    /**
     * Check if this status should be shown in mobile app as active
     */
    public function isActiveForMobile(): bool
    {
        return in_array($this, [
            self::PICKUP,
            self::CREATE_INVOICE,
            self::PROCESSING,
            self::READY,
        ]);
    }

    /**
     * Check if this is a final status
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETE,
            self::CANCELLED,
        ]);
    }

    /**
     * Get all active statuses for mobile app
     */
    public static function getActiveStatuses(): array
    {
        return [
            self::PICKUP,
            self::CREATE_INVOICE,
            self::PROCESSING,
            self::READY,
        ];
    }

    /**
     * Get all final statuses
     */
    public static function getFinalStatuses(): array
    {
        return [
            self::COMPLETE,
            self::CANCELLED,
        ];
    }
}
