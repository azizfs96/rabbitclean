<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class ServiceAreaController extends Controller
{
    /**
     * تحقق من قابلية الخدمة لعنوان معيّن (عن طريق address_id).
     *
     * GET /api/check-service-area?address_id=123
     */
    public function check(Request $request)
    {
        $addressId = $request->query('address_id');

        if (!$addressId) {
            return $this->json('لم يتم إرسال رقم العنوان.', [
                'service_available' => false,
                'allow_with_extra_fee' => false,
                'extra_delivery_fee' => 0,
            ], 400);
        }

        /** @var \App\Models\Address|null $address */
        $address = Address::find($addressId);

        if (!$address || !isset($address->area)) {
            return $this->json('لم يتم العثور على هذا العنوان أو لا يحتوي على حقل الحي.', [
                'service_available' => false,
                'allow_with_extra_fee' => false,
                'extra_delivery_fee' => 0,
            ], 404);
        }

        $serviceArea = ServiceArea::where('name', $address->area)->first();

        if (!$serviceArea) {
            return $this->json('عذراً، حيّك غير مشمول حالياً ضمن نطاق خدمة Rabbit Clean.', [
                'service_available' => false,
                'allow_with_extra_fee' => false,
                'extra_delivery_fee' => 0,
            ]);
        }

        if ($serviceArea->is_served) {
            return $this->json('حيّك مشمول بالخدمة.', [
                'service_available' => true,
                'allow_with_extra_fee' => false,
                'extra_delivery_fee' => 0,
            ]);
        }

        if ($serviceArea->allow_with_extra_fee) {
            return $this->json('حيّك خارج نطاق التغطية الأساسية، يمكننا خدمتك مع رسوم توصيل إضافية.', [
                'service_available' => false,
                'allow_with_extra_fee' => true,
                'extra_delivery_fee' => (float) $serviceArea->extra_delivery_fee,
            ]);
        }

        return $this->json('عذراً، حيّك غير مشمول حالياً ضمن نطاق خدمة Rabbit Clean.', [
            'service_available' => false,
            'allow_with_extra_fee' => false,
            'extra_delivery_fee' => 0,
        ]);
    }
}

