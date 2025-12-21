<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:process-expired';
    protected $description = 'Process expired subscriptions and mark them as expired';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $this->info('Processing expired subscriptions...');
        
        $count = $subscriptionService->processExpiredSubscriptions();
        
        $this->info("Processed {$count} expired subscriptions.");
        
        return Command::SUCCESS;
    }
}
