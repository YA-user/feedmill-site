<article class="article">
    <p class="breadcrumbs"><a href="<?= url('/') ?>">Главная</a> / <a href="<?= url('/news') ?>">Новости</a> / <?= e($item['title']) ?></p>
    <time class="news-meta"><?= e(date('d.m.Y', strtotime($item['published_at']))) ?></time>
    <h1><?= e($item['title']) ?></h1>
    <?php if (!empty($item['announce'])): ?><p class="lead"><?= e($item['announce']) ?></p><?php endif; ?>
    <div class="article-media">
        <img class="article-cover" src="<?= asset(media_image($item['image'] ?? null, 'news')) ?>" alt="<?= e($item['title']) ?>">
        <?php if (!empty($item['video_url'])): ?>
            <div class="video-wrap"><iframe src="<?= e(video_embed_url($item['video_url'])) ?>" title="Видео новости" loading="lazy" allowfullscreen></iframe></div>
        <?php else: ?>
            <div class="video-default" role="img" aria-label="Видео новости">
                <img src="<?= asset('assets/img/default-video.svg') ?>" alt="">
            </div>
        <?php endif; ?>
    </div>
    <div class="article-body"><?= $item['body'] ?></div>
</article>
