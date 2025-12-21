<?php

namespace App\Http\Controllers\API\Package;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    /**
     * Get all active packages
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $packages = Package::active()
            ->orderBy('price', 'asc')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name_ar ?? $package->name,
                    'name_en' => $package->name,
                    'name_ar' => $package->name_ar,
                    'price' => (float) $package->price,
                    'discount_percentage' => (int) $package->discount_percentage,
                    'wallet_credit' => (float) $package->wallet_credit,
                    'description' => $package->description_ar ?? $package->description,
                    'description_en' => $package->description,
                    'description_ar' => $package->description_ar,
                    'validity_days' => $package->validity_days,
                    'is_featured' => $package->is_featured,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Packages retrieved successfully',
            'data' => [
                'packages' => $packages,
            ],
        ]);
    }

    /**
     * Get a specific package
     * 
     * @param Package $package
     * @return JsonResponse
     */
    public function show(Package $package): JsonResponse
    {
        if (!$package->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Package retrieved successfully',
            'data' => [
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name_ar ?? $package->name,
                    'name_en' => $package->name,
                    'name_ar' => $package->name_ar,
                    'price' => (float) $package->price,
                    'discount_percentage' => (int) $package->discount_percentage,
                    'wallet_credit' => (float) $package->wallet_credit,
                    'description' => $package->description_ar ?? $package->description,
                    'description_en' => $package->description,
                    'description_ar' => $package->description_ar,
                    'validity_days' => $package->validity_days,
                    'is_featured' => $package->is_featured,
                ],
            ],
        ]);
    }
}
