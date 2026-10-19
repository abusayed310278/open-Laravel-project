<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = [
    ['url' => 'admin/blog/comments', 'controller' => 'App\Http\Controllers\Admin\BlogCommentController', 'method' => 'index'],
    ['url' => 'admin/blog', 'controller' => 'App\Http\Controllers\Admin\PostController', 'method' => 'index'],
    ['url' => 'admin/reviews', 'controller' => 'App\Http\Controllers\Admin\ReviewController', 'method' => 'index'],
    ['url' => 'admin/support', 'controller' => 'App\Http\Controllers\Admin\SupportController', 'method' => 'index'],
    ['url' => 'admin/chat', 'controller' => 'App\Http\Controllers\Admin\ChatController', 'method' => 'index'],
    ['url' => 'admin/warehouses', 'controller' => 'App\Http\Controllers\Admin\WarehouseController', 'method' => 'index'],
    ['url' => 'admin/verification-checklists', 'controller' => 'App\Http\Controllers\Admin\VerificationChecklistController', 'method' => 'index'],
    ['url' => 'admin/verification-locations', 'controller' => 'App\Http\Controllers\Admin\VerificationLocationController', 'method' => 'index'],
    ['url' => 'admin/verifiers', 'controller' => 'App\Http\Controllers\Admin\VerifierController', 'method' => 'index'],
    ['url' => 'admin/verification-requirements', 'controller' => 'App\Http\Controllers\Admin\VerificationRequirementController', 'method' => 'index'],
    ['url' => 'admin/verifications', 'controller' => 'App\Http\Controllers\Admin\VerificationController', 'method' => 'index'],
    ['url' => 'admin/subscriptions/plans', 'controller' => 'App\Http\Controllers\Admin\SubscriptionPlanController', 'method' => 'index'],
    ['url' => 'admin/payouts', 'controller' => 'App\Http\Controllers\Admin\PayoutController', 'method' => 'index'],
    ['url' => 'admin/payment-verifications', 'controller' => 'App\Http\Controllers\Admin\PaymentVerificationController', 'method' => 'index'],
    ['url' => 'admin/invoices', 'controller' => 'App\Http\Controllers\Admin\InvoiceController', 'method' => 'index'],
    ['url' => 'admin/orders', 'controller' => 'App\Http\Controllers\Admin\OrderController', 'method' => 'index'],
    ['url' => 'admin/inventory', 'controller' => 'App\Http\Controllers\Admin\InventoryController', 'method' => 'index'],
    ['url' => 'admin/banners', 'controller' => 'App\Http\Controllers\Admin\BannerController', 'method' => 'index'],
    ['url' => 'admin/brands', 'controller' => 'App\Http\Controllers\Admin\BrandController', 'method' => 'index'],
    ['url' => 'admin/products', 'controller' => 'App\Http\Controllers\Admin\ProductController', 'method' => 'index'],
];

foreach ($items as $it) {
    $ref = new ReflectionMethod($it['controller'], $it['method']);
    $start = $ref->getStartLine();
    $end = $ref->getEndLine();
    $file = $ref->getFileName();
    $lines = file($file);
    $code = implode('', array_slice($lines, $start - 1, $end - $start + 1));
    $hasSearch = stripos($code, 'search') !== false;
    
    // find view name
    preg_match("/view\(['\"]([^'\"]+)['\"]/", $code, $vm);
    $viewName = $vm[1] ?? 'unknown';
    
    $viewHasSearch = false;
    $viewPath = '';
    if ($viewName !== 'unknown') {
        $viewFile = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        if (file_exists($viewFile)) {
            $viewContent = file_get_contents($viewFile);
            $viewHasSearch = stripos($viewContent, 'name="search"') !== false || stripos($viewContent, 'id="search"') !== false;
            $viewPath = $viewFile;
        }
    }
    
    echo sprintf(
        "%-34s | Controller Search: %-5s | View (%-30s): %-5s\n",
        $it['url'],
        $hasSearch ? 'YES' : 'NO',
        $viewName,
        $viewHasSearch ? 'YES' : 'NO'
    );
}
