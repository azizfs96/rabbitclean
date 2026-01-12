<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
            // New: simplified credit balance
            'credit_balance' => (float) ($this->credit_balance ?? 0),
            'total_credits_used' => (float) ($this->total_credits_used ?? 0),
            // Legacy: individual credit types (for backward compatibility)
            'credits_remaining' => [
                'laundry' => $this->laundry_credits_remaining,
                'clothing' => $this->clothing_credits_remaining,
                'delivery' => $this->delivery_credits_remaining,
                'towel' => $this->towel_credits_remaining,
                'special' => $this->special_credits_remaining,
                'total' => $this->getTotalCreditsRemaining(),
            ],
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'days_remaining' => $this->daysRemaining(),
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'auto_renew' => $this->auto_renew,
            'amount_paid' => (float) $this->amount_paid,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
