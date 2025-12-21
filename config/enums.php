<?php

$reviewStatuses = [
    'pending_review' => [
        'value' => 'pending_review',
        'label' => 'Pending Review',
    ],
    'approved_for_payment' => [
        'value' => 'approved_for_payment',
        'label' => 'Approved for Payment',
    ],
    'not_required' => [
        'value' => 'not_required',
        'label' => 'Not Required',
    ],
];

$reviewStatusLabels = array_column($reviewStatuses, 'label', 'value');

return [
    'media_types' => ['image', 'audio', 'video', 'docs', 'excel', 'pdf', 'other'],

    'post_code' => ['E14', 'E15', 'E16', 'E1W', 'E1', 'E2', 'E3', 'E6','E8','E9', 'SE16'],

    'currency' => '$',

    'coupons' => [
        'discount_types' => [
            'percent' => 'percent',
            'amount' => 'amount'
        ]
    ],

    'payment_status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
    ],

    'payment_types' => [
        'cash_on_delivery' => 'Cash on Delivery',
        'online_payment' => 'Online Payment'
    ],

    'order_status' => [
        'pickup' => 'جاري التحصيل',                      // Step 1 - Pickup truck
        'processing' => 'جاري الغسيل',                   // Step 2 - Washing machine
        'ready' => 'جاهز للتوصيل',                       // Step 3 - Box/Ready
        'on_the_way' => 'المندوب في الطريق إليك',       // Step 4 - Delivery truck
        'delivered' => 'تم التوصيل',                     // Complete
        'cancelled' => 'ملغي',                          // Cancelled
    ],

    'review_status' => $reviewStatuses,
    'review_status_labels' => $reviewStatusLabels,

    'variants' => [
        'Men', 'Women', 'Kids', 'House Hold', 'Others'
    ],

    'settings' => [
        'privacy-policy' => 'Privacy Policy',
        'trams-of-service' => 'Terms of Service',
        'contact-us' => 'Contact us',
        'about-us' => 'About Us'
    ],

    'ganders' => [
        'Male', 'Female', 'Others'
    ]
];
