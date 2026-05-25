<h1><?= current_lang() === 'en' ? 'Feed mill products' : 'Продукция комбикормового завода' ?></h1>
<p class="lead"><?= current_lang() === 'en' ? 'Catalog of compound feed and premixes with automatic order cost calculation.' : 'Каталог комбикормов и премиксов с расчетом стоимости заказа.' ?></p>
<div class="filter-pills">
    <a class="<?= $currentCategoryId === null ? 'active' : '' ?>" href="<?= url('/products') ?>"><?= current_lang() === 'en' ? 'All' : 'Все' ?></a>
    <?php foreach ($categories as $category): ?>
        <a class="<?= (int)$currentCategoryId === (int)$category['id'] ? 'active' : '' ?>" href="<?= url('/products?category=' . $category['id']) ?>"><?= e($category['name']) ?></a>
    <?php endforeach; ?>
</div>
<div class="product-grid">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <a class="product-card__media" href="<?= url('/products/' . $product['slug']) ?>">
                <img src="<?= asset(media_image($product['image'] ?? null)) ?>" alt="<?= e($product['title']) ?>">
            </a>
            <div>
                <span class="badge"><?= e($product['category_name']) ?></span>
                <h2><a href="<?= url('/products/' . $product['slug']) ?>"><?= e($product['title']) ?></a></h2>
                <p><?= excerpt($product['description'], 130) ?></p>
                <p class="price"><?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></p>
                <div class="card-actions">
                    <a href="<?= url('/products/' . $product['slug']) ?>"><?= current_lang() === 'en' ? 'Details' : 'Подробнее' ?></a>
                    <a href="<?= url('/order?product=' . $product['id']) ?>"><?= current_lang() === 'en' ? 'Order' : 'Заказать' ?></a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
