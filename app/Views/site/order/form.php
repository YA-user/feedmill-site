<h1><?= current_lang() === 'en' ? 'Product request form' : 'Форма заявки на продукцию' ?></h1>
<p class="lead"><?= current_lang() === 'en' ? 'Add one or more feed products, enter quantity and contacts. The total cost is calculated automatically.' : 'Добавьте один или несколько видов комбикорма, укажите количество и контакты. Сумма заказа рассчитается автоматически.' ?></p>
<?php if ($errors): ?>
    <div class="alert alert--error"><strong><?= current_lang() === 'en' ? 'Check the form:' : 'Проверьте форму:' ?></strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
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
                <label><?= current_lang() === 'en' ? 'Feed type *' : 'Вид корма *' ?>
                    <select name="product_id[]" required data-product-select>
                        <option value=""><?= current_lang() === 'en' ? 'Select product' : 'Выберите продукцию' ?></option>
                        <?php foreach ($products as $product): ?>
                            <?php $isSelected = (string)($line['product_id'] ?? '') === (string)$product['id']; ?>
                            <option value="<?= e($product['id']) ?>" data-price="<?= e($product['price']) ?>" data-unit="<?= e($product['unit'] ?: 'кг') ?>"<?= $isSelected ? ' selected' : '' ?>><?= e($product['title']) ?> — <?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= current_lang() === 'en' ? 'Quantity *' : 'Количество *' ?>
                    <input type="number" name="quantity[]" min="1" step="1" value="<?= e($line['quantity'] ?? '1000') ?>" required data-quantity>
                </label>
                <div class="line-total">
                    <span><?= current_lang() === 'en' ? 'Line total' : 'Сумма позиции' ?></span>
                    <strong data-line-total>0 ₽</strong>
                </div>
                <button class="button button--ghost" type="button" data-remove-line><?= current_lang() === 'en' ? 'Remove' : 'Удалить' ?></button>
            </div>
        <?php endforeach; ?>
    </div>

    <button class="button button--outline" type="button" data-add-order-line><?= current_lang() === 'en' ? '+ Add another feed type' : '+ Добавить еще вид корма' ?></button>

    <div class="form-grid form-grid--contacts">
        <label><?= current_lang() === 'en' ? 'Full name / company *' : 'ФИО / организация *' ?>
            <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required>
        </label>
        <label><?= current_lang() === 'en' ? 'Phone *' : 'Контактный телефон *' ?>
            <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" required pattern="[0-9+()\-\s]{6,25}">
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>">
        </label>
        <label class="wide"><?= current_lang() === 'en' ? 'Comment' : 'Комментарий' ?>
            <textarea name="comment" rows="4"><?= e($old['comment'] ?? '') ?></textarea>
        </label>
    </div>
    <div class="total-box">
        <span><?= current_lang() === 'en' ? 'Request total:' : 'Итого по заявке:' ?></span>
        <strong data-total>0 ₽</strong>
    </div>
    <button class="button" type="submit"><?= current_lang() === 'en' ? 'Send request' : 'Отправить заявку' ?></button>

    <template data-order-line-template>
        <div class="order-line" data-order-line>
            <label><?= current_lang() === 'en' ? 'Feed type *' : 'Вид корма *' ?>
                <select name="product_id[]" required data-product-select>
                    <option value=""><?= current_lang() === 'en' ? 'Select product' : 'Выберите продукцию' ?></option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= e($product['id']) ?>" data-price="<?= e($product['price']) ?>" data-unit="<?= e($product['unit'] ?: 'кг') ?>"><?= e($product['title']) ?> — <?= format_money($product['price']) ?> / <?= e($product['unit'] ?: 'кг') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><?= current_lang() === 'en' ? 'Quantity *' : 'Количество *' ?>
                <input type="number" name="quantity[]" min="1" step="1" value="1000" required data-quantity>
            </label>
            <div class="line-total">
                <span><?= current_lang() === 'en' ? 'Line total' : 'Сумма позиции' ?></span>
                <strong data-line-total>0 ₽</strong>
            </div>
            <button class="button button--ghost" type="button" data-remove-line><?= current_lang() === 'en' ? 'Remove' : 'Удалить' ?></button>
        </div>
    </template>
</form>
