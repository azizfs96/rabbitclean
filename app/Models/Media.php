<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{

    use HasFactory;
    protected $guarded = ['id'];

    // protected $casts = [
    //     'type' => FileTypes::class,
    // ];

    public function getFileAttribute()
    {
        $defualt=  File::exists(public_path($this->src)) ? asset(path: $this->src) : (Storage::disk('public')->exists($this->src) ? Storage::disk('public')->url($this->src) : asset('defualt/defualt.jpg'));

        return $defualt;
    }
}
