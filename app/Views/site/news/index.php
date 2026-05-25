<h1>Новости компании</h1>
<div class="news-list">
    <?php foreach ($news as $item): ?>
        <article class="card news-card">
            <a class="news-card__media" href="<?= url('/news/' . $item['slug']) ?>" aria-label="<?= e($item['title']) ?>">
                <img src="<?= asset(media_image($item['image'] ?? null, 'news')) ?>" alt="<?= e($item['title']) ?>">
            </a>
            <div class="news-card__body">
                <time class="news-meta"><?= e(date('d.m.Y', strtotime($item['published_at']))) ?></time>
                <h2><a href="<?= url('/news/' . $item['slug']) ?>"><?= e($item['title']) ?></a></h2>
                <p><?= e($item['announce']) ?></p>
                <div class="news-card__footer">
                    <span class="badge">Фото</span>
                    <span class="badge">Видео</span>
                    <a href="<?= url('/news/' . $item['slug']) ?>">Читать полностью →</a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$news): ?>
        <p>Новости пока не опубликованы.</p>
    <?php endif; ?>
</div>
