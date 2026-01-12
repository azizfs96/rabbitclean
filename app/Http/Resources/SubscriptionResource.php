<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'price' => (float) $this->price,
            'currency' => 'SAR',
            'credit_amount' => (float) ($this->credit_amount ?? 0), // New: simplified credit with bonus
            'validity' => $this->validity,
            'validity_type' => $this->validity_type?->value,
            'validity_display' => $this->validity . ' ' . ($this->validity_type?->value ?? 'days'),
            'credits' => [
                'laundry' => $this->laundry_credits,
                'clothing' => $this->clothing_credits,
                'delivery' => $this->delivery_credits,
                'towel' => $this->towel_credits,
                'special' => $this->special_credits,
                'total' => $this->getTotalCredits(),
            ],
            'features' => $this->features ?? [],
            'is_featured' => $this->is_featured,
            'color' => $this->color,
            'discount_percentage' => $this->credit_amount > 0 && $this->price > 0 
                ? round((($this->credit_amount - $this->price) / $this->price) * 100, 0) 
                : null, // Show bonus percentage
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
