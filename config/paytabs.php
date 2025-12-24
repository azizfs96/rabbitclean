<?php

return [
    /**
     * Region: SAU for Saudi Arabia, ARE for UAE, EGY for Egypt, OMN for Oman,
     * JOR for Jordan, GLOBAL for global payments
     */
    'region' => env('PAYTABS_REGION', 'SAU'),

    /**
     * The profile ID from PayTabs dashboard
     */
    'profile_id' => env('PAYTABS_PROFILE_ID', ''),

    /**
     * The server key from PayTabs dashboard
     */
    'server_key' => env('PAYTABS_SERVER_KEY', ''),

    /**
     * Currency to use for transactions
     * SAR for Saudi Riyal, AED for UAE Dirham, etc.
     */
    'currency' => env('PAYTABS_CURRENCY', 'SAR'),

    /**
     * Default language (ar for Arabic, en for English)
     */
    'lang' => env('PAYTABS_LANGUAGE', 'ar'),

    /**
     * Default callback URL - called by PayTabs server after payment
     */
    'callback' => env('PAYTABS_CALLBACK_URL', ''),

    /**
     * Default return URL - user is redirected here after payment
     */
    'return' => env('PAYTABS_RETURN_URL', ''),
];
