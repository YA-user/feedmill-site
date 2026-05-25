<h1>Форма заявки на продукцию</h1>
<p class="lead">Добавьте один или несколько видов комбикорма, укажите количество и контакты. Сумма заказа рассчитается автоматически.</p>
<?php if ($errors): ?>
    <div class="alert alert--error"><strong>Проверьте форму:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<?php
$lines = $old['items'] ?? [];
if (!$lines) {
    $lines = [[
        'product_id' => $selectedProduct['id'] ?? '',
        'quantity' => '1000',
    ]];
}
?>
<form class="order-form" method="post" action="<?= url('/order') ?>" data-validate data-order-form>
    <?= csrf_field() ?>

    <div class="order-lines" data-order-lines>
        <?php foreach ($lines as $line): ?>
            <div class="order-line" data-order-line>
                <label>Вид корма *
                    <select name="product_id[]" required data-product-select>
                        <option value="">Выберите продукцию</option>
                        <?php foreach ($products as $product): ?>
                            <?php $isSelected = (string)($line['product_id'] ?? '') === (string)$product['id']; ?>
                            <option value="<?= e($product['id']) ?>" data-price="<?= e($product['price']) ?>" data-unit="<?= e($product['unit'] ?: 'кг') ?>"<?= $isSelected ? ' selected' : '' ?>><?= e($product['title']) ?> — <?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Количество *
                    <input type="number" name="quantity[]" min="1" step="1" value="<?= e($line['quantity'] ?? '1000') ?>" required data-quantity>
                </label>
                <div class="line-total">
                    <span>Сумма позиции</span>
                    <strong data-line-total>0 ₽</strong>
                </div>
                <button class="button button--ghost" type="button" data-remove-line>Удалить</button>
            </div>
        <?php endforeach; ?>
    </div>

    <button class="button button--outline" type="button" data-add-order-line>+ Добавить еще вид корма</button>

    <div class="form-grid form-grid--contacts">
        <label>ФИО / организация *
            <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required>
        </label>
        <label>Контактный телефон *
            <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" required pattern="[0-9+()\-\s]{6,25}">
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>">
        </label>
        <label class="wide">Комментарий
            <textarea name="comment" rows="4"><?= e($old['comment'] ?? '') ?></textarea>
        </label>
    </div>
    <div class="total-box">
        <span>Итого по заявке:</span>
        <strong data-total>0 ₽</strong>
    </div>
    <button class="button" type="submit">Отправить заявку</button>

    <template data-order-line-template>
        <div class="order-line" data-order-line>
            <label>Вид корма *
                <select name="product_id[]" required data-product-select>
                    <option value="">Выберите продукцию</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= e($product['id']) ?>" data-price="<?= e($product['price']) ?>" data-unit="<?= e($product['unit'] ?: 'кг') ?>"><?= e($product['title']) ?> — <?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Количество *
                <input type="number" name="quantity[]" min="1" step="1" value="1000" required data-quantity>
            </label>
            <div class="line-total">
                <span>Сумма позиции</span>
                <strong data-line-total>0 ₽</strong>
            </div>
            <button class="button button--ghost" type="button" data-remove-line>Удалить</button>
        </div>
    </template>
</form>
