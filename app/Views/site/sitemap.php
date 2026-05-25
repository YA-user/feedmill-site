<h1>Карта сайта</h1>
<div class="sitemap">
    <ul>
        <li><a href="<?= url('/') ?>">Главная</a></li>
        <li><a href="<?= url('/news') ?>">Новости</a></li>
        <li>О компании
            <ul>
                <li><a href="<?= url('/about/history') ?>">История компании</a></li>
                <li><a href="<?= url('/about/certification') ?>">Сертификация и научная поддержка</a></li>
                <li><a href="<?= url('/about/production') ?>">Производство</a></li>
            </ul>
        </li>
        <li><a href="<?= url('/products') ?>">Продукция</a>
            <ul><?php foreach ($categories as $category): ?><li><a href="<?= url('/products?category=' . $category['id']) ?>"><?= e($category['name']) ?></a></li><?php endforeach; ?></ul>
        </li>
        <li><a href="<?= url('/order') ?>">Форма заказа</a></li>
        <li><a href="<?= url('/partners') ?>">Наши партнеры</a></li>
        <li><a href="<?= url('/useful') ?>">Полезная информация</a></li>
        <li><a href="<?= url('/contacts') ?>">Контакты и схема проезда</a></li>
    </ul>
</div>
