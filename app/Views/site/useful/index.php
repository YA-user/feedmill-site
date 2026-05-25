<h1>Полезная информация</h1>
<div class="filter-pills">
    <a class="<?= $currentCategoryId === null ? 'active' : '' ?>" href="<?= url('/useful') ?>">Все</a>
    <?php foreach ($categories as $category): ?>
        <a class="<?= (int)$currentCategoryId === (int)$category['id'] ? 'active' : '' ?>" href="<?= url('/useful?category=' . $category['id']) ?>"><?= e($category['name']) ?></a>
    <?php endforeach; ?>
</div>
<div class="card-list">
    <?php foreach ($articles as $article): ?>
        <article class="card useful-card">
            <img src="<?= asset(stock_image('grain')) ?>" alt="<?= e($article['title']) ?>">
            <div>
                <span class="badge"><?= e($article['category_name']) ?></span>
                <h2><a href="<?= url('/useful/' . $article['slug']) ?>"><?= e($article['title']) ?></a></h2>
                <p><?= e($article['announce']) ?></p>
                <a href="<?= url('/useful/' . $article['slug']) ?>">Читать →</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
