<?php
use App\Core\Auth;
$user = Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
$menuGroups = [
    'Основное' => [
        ['href' => '/admin/news', 'label' => 'Новости', 'section' => 'news'],
        ['href' => '/admin/pages', 'label' => 'Страницы сайта', 'section' => 'pages'],
        ['href' => '/admin/info-categories', 'label' => 'Категории полезной информации', 'section' => 'info-categories'],
        ['href' => '/admin/info-articles', 'label' => 'Полезная информация', 'section' => 'info-articles'],
    ],
    'Продукция' => [
        ['href' => '/admin/categories', 'label' => 'Категории продукции', 'section' => 'categories'],
        ['href' => '/admin/products', 'label' => 'Продукция', 'section' => 'products'],
        ['href' => '/admin/recommendations', 'label' => 'Рекомендации', 'section' => 'recommendations'],
    ],
    'Компания' => [
        ['href' => '/admin/partners', 'label' => 'Партнеры', 'section' => 'partners'],
        ['href' => '/admin/contacts', 'label' => 'Контакты', 'section' => 'contacts'],
    ],
];
if (($user['role'] ?? '') === 'admin') {
    $menuGroups['Настройки'] = [
        ['href' => '/admin/users', 'label' => 'Пользователи'],
    ];
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Админ-панель') ?> — Админ</title>
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script defer src="<?= asset('assets/js/admin.js') ?>"></script>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-logo" href="<?= url('/admin') ?>">
            <img class="admin-logo__mark" src="<?= asset('assets/img/agrokorm-logo.png') ?>" alt="">
            <span>АгроКорм<br><small>панель управления</small></span>
        </a>
        <nav class="admin-nav" aria-label="Меню администратора">
            <a class="admin-nav__link<?= $currentPath === '/admin' ? ' is-active' : '' ?>" href="<?= url('/admin') ?>">Обзор</a>
            <?php if (Auth::canAccessSection('orders')): ?>
                <a class="admin-nav__link<?= $currentPath === '/admin/orders' ? ' is-active' : '' ?>" href="<?= url('/admin/orders') ?>">Заявки покупателей</a>
            <?php endif; ?>
            <?php foreach ($menuGroups as $groupLabel => $items): ?>
                <?php $visibleItems = array_values(array_filter($items, fn (array $item): bool => empty($item['section']) || Auth::canAccessSection($item['section']))); ?>
                <?php if (!$visibleItems) { continue; } ?>
                <div class="admin-nav__group"><?= e($groupLabel) ?></div>
                <?php foreach ($visibleItems as $item): ?>
                    <?php $active = $currentPath === $item['href'] || str_starts_with($currentPath, $item['href'] . '/'); ?>
                    <a class="admin-nav__link<?= $active ? ' is-active' : '' ?>" href="<?= url($item['href']) ?>"><?= e($item['label']) ?></a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
        <div class="admin-user">
            <div class="admin-user__name"><?= e($user['name'] ?? '') ?></div>
            <small><?= ($user['role'] ?? '') === 'admin' ? 'Администратор' : 'Контент-менеджер' ?></small>
            <div class="admin-user__links">
                <a href="<?= url('/') ?>">На сайт</a>
                <a href="<?= url('/admin/logout') ?>">Выйти</a>
            </div>
        </div>
    </aside>
    <main class="admin-content">
        <?php if ($msg = flash('success')): ?><div class="alert alert--success"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($msg = flash('error')): ?><div class="alert alert--error"><?= e($msg) ?></div><?php endif; ?>
        <?php require $viewFile; ?>
    </main>
</div>
</body>
</html>
