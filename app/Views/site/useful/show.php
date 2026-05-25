<article class="article">
    <p class="breadcrumbs"><a href="<?= url('/') ?>">Главная</a> / <a href="<?= url('/useful') ?>">Полезная информация</a> / <?= e($article['title']) ?></p>
    <span class="badge"><?= e($article['category_name']) ?></span>
    <h1><?= e($article['title']) ?></h1>
    <img class="article-cover" src="<?= asset(stock_image('grain')) ?>" alt="<?= e($article['title']) ?>">
    <div class="article-body"><?= $article['body'] ?></div>
</article>
