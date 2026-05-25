<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\SiteController;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim(rawurldecode($path), '/');
if ($path !== '/' && str_starts_with($path, '/index.php')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$site = new SiteController();
$admin = new AdminController();

try {
    if ($path === '/') {
        $site->home();
    } elseif ($path === '/news') {
        $site->newsIndex();
    } elseif (preg_match('#^/news/([^/]+)$#', $path, $m)) {
        $site->newsShow($m[1]);
    } elseif ($path === '/about') {
        $site->page('about');
    } elseif (preg_match('#^/about/(history|certification|production)$#', $path, $m)) {
        $site->page($m[1]);
    } elseif ($path === '/partners') {
        $site->partners();
    } elseif ($path === '/contacts') {
        $site->contacts();
    } elseif ($path === '/products') {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $site->products($categoryId ?: null);
    } elseif (preg_match('#^/products/([^/]+)$#', $path, $m)) {
        $site->productShow($m[1]);
    } elseif ($path === '/order' && $method === 'GET') {
        $site->orderForm(isset($_GET['product']) ? (int)$_GET['product'] : null);
    } elseif ($path === '/order' && $method === 'POST') {
        $site->orderSubmit();
    } elseif ($path === '/order/success') {
        $site->orderSuccess();
    } elseif ($path === '/useful') {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $site->useful($categoryId ?: null);
    } elseif (preg_match('#^/useful/([^/]+)$#', $path, $m)) {
        $site->usefulShow($m[1]);
    } elseif ($path === '/sitemap') {
        $site->sitemap();
    } elseif ($path === '/admin/login') {
        $admin->login();
    } elseif ($path === '/admin/logout') {
        $admin->logout();
    } elseif ($path === '/admin') {
        $admin->dashboard();
    } elseif ($path === '/admin/orders') {
        $admin->orders();
    } elseif ($path === '/admin/users') {
        $admin->users();
    } elseif (preg_match('#^/admin/([a-z-]+)/create$#', $path, $m) && $admin->sectionExists($m[1])) {
        $admin->create($m[1]);
    } elseif (preg_match('#^/admin/([a-z-]+)/(\d+)/edit$#', $path, $m) && $admin->sectionExists($m[1])) {
        $admin->edit($m[1], (int)$m[2]);
    } elseif (preg_match('#^/admin/([a-z-]+)/(\d+)/delete$#', $path, $m) && $method === 'POST' && $admin->sectionExists($m[1])) {
        $admin->delete($m[1], (int)$m[2]);
    } elseif (preg_match('#^/admin/([a-z-]+)$#', $path, $m) && $admin->sectionExists($m[1])) {
        $admin->list($m[1]);
    } else {
        http_response_code(404);
        (new SiteController())->page('__not_found__');
    }
} catch (Throwable $exception) {
    http_response_code(500);
    $message = $exception->getMessage();
    require __DIR__ . '/../app/Views/site/error.php';
}
