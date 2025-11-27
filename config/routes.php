<?php

/**
 * Web Routes
 */

// Register middleware
$router->middleware('auth', \App\Middleware\AuthMiddleware::class);
$router->middleware('csrf', \App\Middleware\CsrfMiddleware::class);

// Public routes (no middleware)
$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');

// Login POST with CSRF protection
$router->group(['middleware' => 'csrf'], function ($router) {
    $router->post('/login', 'AuthController@login');
});

// Logout (requires auth)
$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('/logout', 'AuthController@logout');
});

// Test route (temporary - no middleware)
$router->get('/test', 'TestController@index');

// Protected routes (require authentication)
$router->group(['middleware' => ['auth', 'csrf']], function ($router) {

    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');

    // Goods Receipt
    $router->get('/goods-receipt', 'GoodsReceiptController@index');
    $router->post('/goods-receipt', 'GoodsReceiptController@store');

    // Rejected Stock
    $router->get('/rejected-stock', 'RejectedStockController@index');

    // Inventory
    $router->get('/inventory', 'InventoryController@index');
    $router->post('/inventory/hold-status', 'InventoryController@updateHoldStatus');
});

// API Routes (auth required, no CSRF for GET requests)
$router->group(['middleware' => 'auth'], function ($router) {

    // Goods Receipt API
    $router->get('/api/materials/search', 'GoodsReceiptController@searchMaterials');
    $router->get('/api/suppliers/search', 'GoodsReceiptController@searchSuppliers');
    $router->get('/api/receipts/recent', 'GoodsReceiptController@recentReceipts');

    // Rejected Stock API
    $router->get('/api/rejected-stock/data', 'RejectedStockController@getRejectedStock');
    $router->get('/api/batch/details', 'RejectedStockController@getBatchDetails');

    // Batch Details (shared API)
    $router->get('/api/batch/details', 'InventoryController@getBatchDetails');
});
