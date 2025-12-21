<?php

namespace App\Enum;

enum Roles: string
{
    case ROOT = 'root';
    case ADMIN = 'admin';
    case VENDOR = 'vendor';
    case STORE = 'store';
    case EMPLOYE = 'employee';
    case CUSTOMER = 'customer';
    case VISITOR = 'visitor';
}
