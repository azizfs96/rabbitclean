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
        'pickup' => 'pickup',                           // Step 1 - Pickup
        'create_invoice' => 'create_invoice',           // Step 2 - Create Invoice
        'processing' => 'processing',                   // Step 3 - Processing
        'ready' => 'ready',                             // Step 4 - Ready
        'complete' => 'complete',                       // Final - Complete
        'cancelled' => 'cancelled',                     // Final - Cancelled
    ],

    // English labels for display
    'order_status_labels_en' => [
        'pickup' => 'Pickup',
        'create_invoice' => 'Create Invoice',
        'processing' => 'Processing',
        'ready' => 'Ready',
        'complete' => 'Complete',
        'cancelled' => 'Cancelled',
    ],

    // Arabic labels for display
    'order_status_labels_ar' => [
        'pickup' => 'جاري التحصيل',
        'create_invoice' => 'إنشاء الفاتورة',
        'processing' => 'جاري الغسيل',
        'ready' => 'جاهز للتوصيل',
        'complete' => 'تم التوصيل',
        'cancelled' => 'ملغي',
    ],

    // Statuses returned to mobile app (active orders)
    'order_status_mobile' => [
        'pickup',
        'create_invoice',
        'processing',
        'ready',
    ],

    // Final statuses (order history)
    'order_status_final' => [
        'complete',
        'cancelled',
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
