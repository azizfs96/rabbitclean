<?php

namespace App\Enum;

enum PaymentGateway: string
{
    case PAYTABS = 'paytabs';
    case CASH = 'cash';
}
