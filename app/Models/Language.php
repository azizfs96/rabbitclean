<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Language extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public function thumbnail()
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    public function getFileAttribute()
    {
        $img = asset('defualt/defualt.jpg');

        if($this->thumbnail && Storage::disk('public')->exists($this->thumbnail->src)){
            $img =  Storage::disk('public')->url($this->thumbnail->src);
        }

        return $img;
    }

}
