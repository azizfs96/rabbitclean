<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PICKUP = 'جاري التحصيل';                       // Step 1 - Pickup truck
    case PROCESSING = 'جاري الغسيل';                    // Step 2 - Washing machine
    case READY = 'جاهز للتوصيل';                        // Step 3 - Ready for delivery
    case ON_THE_WAY = 'المندوب في الطريق إليك';        // Step 4 - On the way
    case DELIVERED = 'تم التوصيل';                      // Complete
    case CANCELLED = 'ملغي';                            // Cancelled
}
