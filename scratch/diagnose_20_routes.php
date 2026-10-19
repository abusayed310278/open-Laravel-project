<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = [
    'admin/blog/comments' => 'admin.blog.comments.index',
    'admin/blog' => 'admin.blog.index',
    'admin/reviews' => 'admin.reviews.index',
    'admin/support' => 'admin.support.index',
    'admin/chat' => 'admin.chat.index',
    'admin/warehouses' => 'admin.warehouses.index',
    'admin/verification-checklists' => 'admin.verification-checklists.index',
    'admin/verification-locations' => 'admin.verification-locations.index',
    'admin/verifiers' => 'admin.verifiers.index',
    'admin/verification-requirements' => 'admin.verification-requirements.index',
    'admin/verifications' => 'admin.verifications.index',
    'admin/subscriptions/plans' => 'admin.subscriptions.plans.index',
    'admin/payouts' => 'admin.payouts.index',
    'admin/payment-verifications' => 'admin.payment-verifications.index',
    'admin/invoices' => 'admin.invoices.index',
    'admin/orders' => 'admin.orders.index',
    'admin/inventory' => 'admin.inventory.index',
    'admin/banners' => 'admin.banners.index',
    'admin/brands' => 'admin.brands.index',
    'admin/products' => 'admin.products.index',
];

foreach ($routes as $url => $name) {
    $route = app('router')->getRoutes()->getByName($name);
    if ($route) {
        $action = $route->getActionName();
        echo "URL: {$url}\n  Route: {$name}\n  Action: {$action}\n";
    } else {
        echo "URL: {$url} -> ROUTE NOT FOUND FOR {$name}!\n";
    }
}
