<section class="hero">
    <div class="hero__content">
        <p class="eyebrow">Производство гранулированных комбикормов</p>
        <h1><?= e($page['title'] ?? 'АгроКорм — комбикормовый завод') ?></h1>
        <div class="lead"><?= $page['body'] ?? '<p>Современный завод по производству кормов для животноводческих хозяйств.</p>' ?></div>
        <div class="hero-actions">
            <a class="button" href="<?= url('/products') ?>">Смотреть продукцию</a>
            <a class="button button--outline" href="<?= url('/order') ?>">Составить заявку</a>
        </div>
    </div>
    <div class="hero__media">
        <img src="<?= asset(stock_image('hero')) ?>" alt="Поле зерновых культур">
        <div class="hero-panel">
            <strong>1–2 дня</strong>
            <span>подбор рациона, фасовка и доставка партий</span>
        </div>
    </div>
</section>

<section class="section photo-story">
    <div class="section-head">
        <h2>От поля до готового корма</h2>
        <a href="<?= url('/about/production') ?>">О производстве →</a>
    </div>
    <div class="photo-story__grid">
        <?php foreach (stock_gallery() as $index => $photo): ?>
            <article class="photo-story__item <?= $index === 0 ? 'photo-story__item--large' : '' ?>">
                <img src="<?= asset($photo['image']) ?>" alt="<?= e($photo['title']) ?>">
                <div>
                    <h3><?= e($photo['title']) ?></h3>
                    <p><?= e($photo['text']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Виды продукции</h2>
        <a href="<?= url('/products') ?>">В каталог →</a>
    </div>
    <div class="slideshow" data-slideshow>
        <?php foreach (array_slice($products, 0, 4) as $i => $product): ?>
            <article class="slide <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= asset(media_image($product['image'] ?? null)) ?>" alt="<?= e($product['title']) ?>">
                <div>
                    <span class="badge"><?= e($product['category_name']) ?></span>
                    <h3><?= e($product['title']) ?></h3>
                    <p><?= excerpt($product['description'], 160) ?></p>
                    <p class="price">от <?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></p>
                    <a href="<?= url('/products/' . $product['slug']) ?>">Подробнее →</a>
                </div>
            </article>
        <?php endforeach; ?>
        <div class="slider-dots" data-slider-dots></div>
    </div>
</section>

<section class="section">
    <div class="section-head"><h2>Новости компании</h2><a href="<?= url('/news') ?>">Все новости →</a></div>
    <div class="home-news-grid">
        <?php foreach ($news as $item): ?>
            <article class="home-news-card">
                <img src="<?= asset(media_image($item['image'] ?? null, 'news')) ?>" alt="<?= e($item['title']) ?>">
                <div>
                    <time><?= e(date('d.m.Y', strtotime($item['published_at']))) ?></time>
                    <h3><a href="<?= url('/news/' . $item['slug']) ?>"><?= e($item['title']) ?></a></h3>
                    <p><?= e($item['announce']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
