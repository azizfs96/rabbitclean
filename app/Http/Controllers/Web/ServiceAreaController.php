<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class ServiceAreaController extends Controller
{
    public function index()
    {
        $serviceAreas = ServiceArea::orderBy('name')->get();
        return view('service-areas.index', compact('serviceAreas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_areas,name',
            'is_served' => 'boolean',
            'allow_with_extra_fee' => 'boolean',
            'extra_delivery_fee' => 'nullable|numeric|min:0',
        ]);

        ServiceArea::create([
            'name' => $request->name,
            'is_served' => $request->boolean('is_served', true),
            'allow_with_extra_fee' => $request->boolean('allow_with_extra_fee', false),
            'extra_delivery_fee' => $request->input('extra_delivery_fee', 0) ?: 0,
        ]);

        return redirect()->route('service-areas.index')->with('success', __('تم إضافة الحي بنجاح'));
    }

    public function update(Request $request, ServiceArea $serviceArea)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_areas,name,' . $serviceArea->id,
            'is_served' => 'boolean',
            'allow_with_extra_fee' => 'boolean',
            'extra_delivery_fee' => 'nullable|numeric|min:0',
        ]);

        $serviceArea->update([
            'name' => $request->name,
            'is_served' => $request->boolean('is_served', true),
            'allow_with_extra_fee' => $request->boolean('allow_with_extra_fee', false),
            'extra_delivery_fee' => $request->input('extra_delivery_fee', 0) ?: 0,
        ]);

        return redirect()->route('service-areas.index')->with('success', __('تم تحديث الحي بنجاح'));
    }

    public function toggle(ServiceArea $serviceArea)
    {
        $serviceArea->update(['is_served' => !$serviceArea->is_served]);
        return redirect()->route('service-areas.index')->with('success', __('تم تغيير الحالة بنجاح'));
    }

    public function delete(ServiceArea $serviceArea)
    {
        $serviceArea->delete();
        return redirect()->route('service-areas.index')->with('success', __('تم الحذف بنجاح'));
    }
}
