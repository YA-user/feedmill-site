<?php
use App\Models\Repository;

$siteTitle = config('app_name');
$productCategories = Repository::categories();
$usefulCategories = Repository::usefulCategories();
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Сайт') ?> — <?= e($siteTitle) ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script defer src="<?= asset('assets/js/app.js') ?>"></script>
</head>
<body>
<header class="top-header">
    <div class="top-header__inner">
        <a class="logo" href="<?= url('/') ?>">
            <img class="logo__mark" src="<?= asset('assets/img/agrokorm-logo.png') ?>" alt="">
            <span><strong>АгроКорм</strong><small><?= current_lang() === 'en' ? 'feed mill' : 'комбикормовый завод' ?></small></span>
        </a>
        <div class="header-contacts">
            <span>☎ +7 (800) 250-10-45</span>
            <span>✉ sales@agrokorm.local</span>
            <div class="lang-switch" aria-label="Language">
                <a class="<?= current_lang() === 'ru' ? 'is-active' : '' ?>" href="<?= e(lang_url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', 'ru')) ?>">RU</a>
                <a class="<?= current_lang() === 'en' ? 'is-active' : '' ?>" href="<?= e(lang_url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', 'en')) ?>">EN</a>
            </div>
            <a class="button button--light" href="<?= url('/order') ?>"><?= e(t('request')) ?></a>
        </div>
    </div>
</header>
<div class="page-wrap">
    <aside class="sidebar">
        <nav class="side-nav" aria-label="Основное меню">
            <ul>
                <li><a href="<?= url('/') ?>"><?= e(t('home')) ?></a></li>
                <li>
                    <a href="<?= url('/about') ?>"><?= e(t('about')) ?></a>
                    <ul>
                        <li><a href="<?= url('/news') ?>"><?= e(t('company_news')) ?></a></li>
                        <li><a href="<?= url('/about/history') ?>"><?= e(t('history')) ?></a></li>
                        <li><a href="<?= url('/about/certification') ?>"><?= e(t('certification')) ?></a></li>
                        <li><a href="<?= url('/about/production') ?>"><?= e(t('production')) ?></a></li>
                        <li><a href="<?= url('/partners') ?>"><?= e(t('partners')) ?></a></li>
                    </ul>
                </li>
                <li>
                    <a href="<?= url('/products') ?>"><?= e(t('products')) ?></a>
                    <ul>
                        <?php foreach ($productCategories as $category): ?>
                            <li><a href="<?= url('/products?category=' . $category['id']) ?>"><?= e($category['name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= url('/order') ?>"><?= e(t('order')) ?></a></li>
                    </ul>
                </li>
                <li>
                    <a href="<?= url('/useful') ?>"><?= e(t('useful')) ?></a>
                    <ul>
                        <?php foreach ($usefulCategories as $category): ?>
                            <li><a href="<?= url('/useful?category=' . $category['id']) ?>"><?= e($category['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="<?= url('/contacts') ?>"><?= e(t('contacts')) ?></a></li>
                <li><a href="<?= url('/sitemap') ?>"><?= e(t('site_map')) ?></a></li>
            </ul>
        </nav>
    </aside>
    <main class="content">
        <?php if ($msg = flash('success')): ?><div class="alert alert--success"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($msg = flash('error')): ?><div class="alert alert--error"><?= e($msg) ?></div><?php endif; ?>
        <?php require $viewFile; ?>
    </main>
</div>
<footer class="footer">
    <div>© <?= date('Y') ?> АгроКорм. Демонстрационный сайт РГР.</div>
    <div><a href="<?= url('/admin') ?>"><?= e(t('admin')) ?></a></div>
</footer>
</body>
</html>
