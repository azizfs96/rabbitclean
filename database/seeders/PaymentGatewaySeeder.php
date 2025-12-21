<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::truncate();
        $paymentMethods = [
            [
                'title'             => 'PayTabs',
                'name'              => 'paytabs',
                'config'            => json_encode([
                    'profile_id'        => '',  // Your PayTabs Profile ID
                    'server_key'        => '',  // Your PayTabs Server Key
                    'currency'          => 'SAR',  // Saudi Riyal (or USD, AED, EGP, etc.)
                    'payment_methods'   => 'all',  // or 'creditcard' or specific methods
                    'transaction_class' => 'ecom',  // ecom or recurring
                ]),
                'mode'              => 'test',
                'alias'             => 'paytabs',
                'is_active'         => true,
            ],
        ];

        PaymentGateway::insert($paymentMethods);
    }
}
