<article class="article">
    <p class="breadcrumbs"><a href="<?= url('/') ?>">Главная</a> / <?= e($page['title']) ?></p>
    <div class="article-hero">
        <div>
            <h1><?= e($page['title']) ?></h1>
            <div class="article-body"><?= $page['body'] ?></div>
        </div>
        <img src="<?= asset(page_image($page['slug'] ?? null)) ?>" alt="<?= e($page['title']) ?>">
    </div>
</article>
