<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case PENDING = 'Pending';
    case PAID = 'Paid';
    case UNPAID = 'Unpaid';
}
