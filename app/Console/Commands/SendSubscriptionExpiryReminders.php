<?php

namespace App\Console\Commands;

use App\Models\CustomerSubscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders {--days=7 : Days before expiry to send reminder}';
    protected $description = 'Send reminders to customers whose subscriptions are expiring soon';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Checking for subscriptions expiring in {$days} days...");
        
        $expiringSoon = $subscriptionService->getExpiringSoon($days);
        
        foreach ($expiringSoon as $subscription) {
            try {
                // Here you can send notifications via SMS, email, or push notification
                // For now, we'll just log it
                Log::info("Subscription expiring soon", [
                    'customer_id' => $subscription->customer_id,
                    'subscription_id' => $subscription->id,
                    'end_date' => $subscription->end_date,
                    'days_remaining' => $subscription->daysRemaining(),
                ]);
                
                $this->line("Reminder sent for subscription #{$subscription->id} - Customer: {$subscription->customer->user->name}");
                
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for subscription #{$subscription->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("Processed {$expiringSoon->count()} expiring subscriptions.");
        
        return Command::SUCCESS;
    }
}
