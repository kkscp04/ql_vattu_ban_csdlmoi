<?php

define('APP_ROOT', __DIR__);
define('BASE_URL', '/ql_vattu2');

require_once APP_ROOT . '/config/connect.php';

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, '/');

        if ($path === '') {
            return $base . '/';
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to(string $path): void
    {
        header('Location: ' . app_url($path));
        exit;
    }
}
