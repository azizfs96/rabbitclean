<?php

namespace App\Enum;

enum SubscriptionRequestStatus: string
{
    case PENDING = 'Pending';
    case SUCCESS = 'Success';
    case FAILED = 'Failed';
}
