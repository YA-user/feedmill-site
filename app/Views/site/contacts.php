<h1>Контакты и схема проезда</h1>
<div class="contact-hero">
    <img src="<?= asset(stock_image('truck')) ?>" alt="Доставка комбикорма">
    <div>
        <h2>Связь с отделом продаж</h2>
        <p>Для учебного проекта достаточно демонстрационных контактов: телефон, email, адрес и карта проезда.</p>
    </div>
</div>
<div class="contact-grid">
    <?php foreach ($contacts as $contact): ?>
        <article class="card">
            <h2><?= e($contact['title']) ?></h2>
            <p><strong>Адрес:</strong> <?= e($contact['address']) ?></p>
            <p><strong>Телефон:</strong> <?= e($contact['phone']) ?></p>
            <p><strong>Email:</strong> <?= e($contact['email']) ?></p>
            <?php if ($contact['work_time']): ?><p><strong>График:</strong> <?= e($contact['work_time']) ?></p><?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
<?php $map = $contacts[0]['map_url'] ?? ''; ?>
<?php if ($map): ?>
    <div class="map-wrap"><iframe src="<?= e($map) ?>" loading="lazy" title="Схема проезда Яндекс Карты"></iframe></div>
<?php endif; ?>
