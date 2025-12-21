<?php

namespace App\Http\Controllers\API\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Repositories\SettingRepository;

class SettingController extends Controller
{
    public function show(Setting $page)
    {
        return $this->json($page->title , [
            'setting' => new SettingResource($page)
        ]);
    }

    public function privacyShow(){
        return view('settings.privacy-policy');

    }
    public function termsShow(Setting $page){
        return view('settings.trams-of-service');
    }
}
