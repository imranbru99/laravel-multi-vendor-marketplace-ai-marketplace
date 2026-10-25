<?php

return [
    'default_tenant' => env('MARKETPLACE_TENANT', 'atlas'),
    'demo' => (bool) env('MARKETPLACE_DEMO', false),
    'sandbox' => (bool) env('PAYMENT_SANDBOX', true),
    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    'modules' => [
        'catalog' => ['name' => 'Product catalog', 'category' => 'Commerce', 'dependencies' => [], 'core' => true],
        'inventory' => ['name' => 'Inventory & warehouses', 'category' => 'Operations', 'dependencies' => ['catalog'], 'core' => false],
        'checkout' => ['name' => 'Cart & checkout', 'category' => 'Commerce', 'dependencies' => ['inventory'], 'core' => false],
        'payments' => ['name' => 'Sandbox payments', 'category' => 'Finance', 'dependencies' => ['checkout'], 'core' => false],
        'digital' => ['name' => 'Private digital downloads', 'category' => 'Commerce', 'dependencies' => ['payments'], 'core' => false],
        'shipping' => ['name' => 'Shipping & fulfillment', 'category' => 'Operations', 'dependencies' => ['payments'], 'core' => false],
        'returns' => ['name' => 'Returns & refunds', 'category' => 'Finance', 'dependencies' => ['payments'], 'core' => false],
        'reviews' => ['name' => 'Verified reviews', 'category' => 'Engagement', 'dependencies' => ['catalog'], 'core' => false],
        'marketing' => ['name' => 'Coupons & campaigns', 'category' => 'Growth', 'dependencies' => ['checkout'], 'core' => false],
        'support' => ['name' => 'Customer support', 'category' => 'Engagement', 'dependencies' => [], 'core' => false],
        'b2b' => ['name' => 'Wholesale quotations', 'category' => 'Commerce', 'dependencies' => ['catalog'], 'core' => false],
        'bookings' => ['name' => 'Service bookings', 'category' => 'Commerce', 'dependencies' => ['catalog'], 'core' => false],
        'procurement' => ['name' => 'Purchase orders', 'category' => 'Operations', 'dependencies' => ['inventory'], 'core' => false],
        'workforce' => ['name' => 'Staff & attendance records', 'category' => 'Operations', 'dependencies' => [], 'core' => false],
        'ai' => ['name' => 'AI review workspace (sandbox)', 'category' => 'Intelligence', 'dependencies' => ['catalog'], 'core' => false],
    ],
];
