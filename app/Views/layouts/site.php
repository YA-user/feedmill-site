<?php
$siteTitle = config('app_name');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Сайт') ?> — <?= e($siteTitle) ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>">
    <script defer src="<?= asset('assets/js/app.js') ?>"></script>
</head>
<body>
<header class="top-header">
    <div class="top-header__inner">
        <a class="logo" href="<?= url('/') ?>">
            <img class="logo__mark" src="<?= asset('assets/img/agrokorm-logo.svg') ?>" alt="">
            <span><strong>АгроКорм</strong><small>комбикормовый завод</small></span>
        </a>
        <div class="header-contacts">
            <span>☎ +7 (800) 250-10-45</span>
            <span>✉ sales@agrokorm.local</span>
            <a class="button button--light" href="<?= url('/order') ?>">Составить заявку</a>
        </div>
    </div>
</header>
<div class="page-wrap">
    <aside class="sidebar">
        <nav class="side-nav" aria-label="Основное меню">
            <ul>
                <li><a href="<?= url('/') ?>">Главная</a></li>
                <li><a href="<?= url('/products') ?>">Продукция</a></li>
                <li><a href="<?= url('/order') ?>">Заявка</a></li>
                <li><a href="<?= url('/news') ?>">Новости</a></li>
                <li><a href="<?= url('/about') ?>">О компании</a></li>
                <li><a href="<?= url('/useful') ?>">Статьи</a></li>
                <li><a href="<?= url('/contacts') ?>">Контакты</a></li>
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
    <div><a href="<?= url('/admin') ?>">Раздел администратора</a></div>
</footer>
</body>
</html>
