<?php

namespace App\Enum;

enum PaymentType: string
{
    case CASHONDELIVERY = 'Cash on Delivery';
    case ONLINEPAYMENT = 'Online Payment';
}
