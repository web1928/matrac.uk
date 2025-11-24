<?php

namespace App\Controllers;

use Core\Controller;

class TestController extends Controller
{
    public function index()
    {
        $data = [
            'message' => 'MVC Framework is working!',
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'base_url' => url('/'),
            'asset_url' => asset('css/layout.css'),
        ];

        return $this->view('test', $data);
    }
}
