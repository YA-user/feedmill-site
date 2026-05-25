<h1>Наши партнеры</h1>
<div class="partner-grid">
    <?php foreach ($partners as $partner): ?>
        <article class="card partner-card">
            <img src="<?= asset(media_image($partner['logo'] ?? null, 'premix')) ?>" alt="<?= e($partner['name']) ?>">
            <h2><?= e($partner['name']) ?></h2>
            <div><?= $partner['description'] ?></div>
            <?php if (!empty($partner['website'])): ?><a href="<?= e($partner['website']) ?>" target="_blank" rel="noopener">Сайт партнера</a><?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
