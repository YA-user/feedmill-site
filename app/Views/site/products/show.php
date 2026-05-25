<article class="product-detail">
    <p class="breadcrumbs"><a href="<?= url('/') ?>">Главная</a> / <a href="<?= url('/products') ?>">Продукция</a> / <?= e($product['title']) ?></p>
    <div class="product-detail__top">
        <img src="<?= asset(media_image($product['image'] ?? null)) ?>" alt="<?= e($product['title']) ?>">
        <div>
            <span class="badge"><?= e($product['category_name']) ?></span>
            <h1><?= e($product['title']) ?></h1>
            <p class="price big"><?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></p>
            <a class="button" href="<?= url('/order?product=' . $product['id']) ?>">Составить заявку</a>
        </div>
    </div>
    <div class="article-body"><?= $product['description'] ?></div>
    <section class="section">
        <h2>Видео о продукции</h2>
        <?php if (!empty($product['video_url'])): ?>
            <div class="video-wrap"><iframe src="<?= e(video_embed_url($product['video_url'])) ?>" title="Видео о продукции" loading="lazy" allowfullscreen></iframe></div>
        <?php else: ?>
            <div class="video-default" role="img" aria-label="Видео о продукции">
                <img src="<?= asset('assets/img/default-video.svg') ?>" alt="">
            </div>
        <?php endif; ?>
    </section>
    <?php if (!empty($product['recommendation'])): ?>
        <section class="info-box"><h2>Краткие рекомендации</h2><?= $product['recommendation'] ?></section>
    <?php endif; ?>
    <?php if ($recommendations): ?>
        <section class="section">
            <h2>Рекомендации по применению</h2>
            <div class="card-list">
                <?php foreach ($recommendations as $rec): ?>
                    <article class="card"><h3><?= e($rec['title']) ?></h3><div><?= $rec['body'] ?></div></article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</article>
