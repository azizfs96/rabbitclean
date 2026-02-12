<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_served',
        'allow_with_extra_fee',
        'extra_delivery_fee',
    ];
}

