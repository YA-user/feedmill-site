<div class="page-head">
    <div>
        <p class="eyebrow">Админ-панель</p>
        <h1>Что нужно изменить на сайте?</h1>
        <p class="muted">Выберите раздел ниже. Все изменения после сохранения сразу попадают на сайт.</p>
    </div>
    <a class="button button--ghost" href="<?= url('/') ?>">Открыть сайт</a>
</div>
<div class="quick-grid">
    <a class="quick-card" href="<?= url('/admin/news') ?>">
        <strong>Новости компании</strong>
        <span><?= e($counts['news']) ?> записей. Текст, фото и видео.</span>
    </a>
    <a class="quick-card" href="<?= url('/admin/products') ?>">
        <strong>Продукция</strong>
        <span><?= e($counts['products']) ?> товаров. Цены, фото и описание.</span>
    </a>
    <a class="quick-card" href="<?= url('/admin/orders') ?>">
        <strong>Заявки покупателей</strong>
        <span><?= e($counts['orders']) ?> заявок. Контакты и статус.</span>
    </a>
    <a class="quick-card" href="<?= url('/admin/pages') ?>">
        <strong>Страницы сайта</strong>
        <span>О компании, история, сертификация и производство.</span>
    </a>
    <a class="quick-card" href="<?= url('/admin/contacts') ?>">
        <strong>Контакты</strong>
        <span>Адрес, телефон, email, график и карта.</span>
    </a>
    <a class="quick-card" href="<?= url('/admin/info-articles') ?>">
        <strong>Полезная информация</strong>
        <span>Статьи и материалы для покупателей.</span>
    </a>
</div>
