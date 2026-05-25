<?php
function admin_order_status(string $status): string {
    $labels = [
        'new' => 'Новая',
        'processing' => 'В работе',
        'done' => 'Выполнена',
        'cancelled' => 'Отменена',
    ];
    $classes = [
        'new' => 'status-badge--yes',
        'processing' => 'status-badge--muted',
        'done' => 'status-badge--yes',
        'cancelled' => 'status-badge--no',
    ];
    return '<span class="status-badge ' . e($classes[$status] ?? 'status-badge--muted') . '">' . e($labels[$status] ?? $status) . '</span>';
}
?>
<div class="page-head">
    <div>
        <p class="eyebrow">Продажи</p>
        <h1>Заявки покупателей</h1>
        <p class="muted">Здесь видны заказы с сайта: клиент, телефон, состав заказа, сумма и статус обработки.</p>
    </div>
</div>
<div class="table-wrap">
<table>
    <thead><tr><th>№</th><th>Дата</th><th>Клиент</th><th>Телефон</th><th>Состав заказа</th><th>Сумма</th><th>Статус</th><th>Изменить статус</th><th>Комментарий</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= e($order['id']) ?></td>
            <td><?= e($order['created_at']) ?></td>
            <td><?= e($order['full_name']) ?><br><small><?= e($order['email']) ?></small></td>
            <td><?= e($order['phone']) ?></td>
            <td>
                <?php if (!empty($order['items'])): ?>
                    <ul class="order-items-admin">
                        <?php foreach ($order['items'] as $item): ?>
                            <li>
                                <strong><?= e($item['product_title']) ?></strong><br>
                                <?= e($item['quantity']) ?> <?= e($item['unit'] ?: 'кг') ?> × <?= format_money($item['price']) ?> = <?= format_money($item['total']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?= e($order['product_title']) ?><br>
                    <?= e($order['quantity']) ?> кг × <?= format_money($order['price']) ?>
                <?php endif; ?>
            </td>
            <td><strong><?= format_money($order['total']) ?></strong></td>
            <td><?= admin_order_status((string)$order['status']) ?></td>
            <td>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($order['id']) ?>">
                    <select name="status" onchange="this.form.submit()">
                        <option value="new"<?= selected($order['status'], 'new') ?>>Новая</option>
                        <option value="processing"<?= selected($order['status'], 'processing') ?>>В работе</option>
                        <option value="done"<?= selected($order['status'], 'done') ?>>Выполнена</option>
                        <option value="cancelled"<?= selected($order['status'], 'cancelled') ?>>Отменена</option>
                    </select>
                </form>
            </td>
            <td><?= e($order['comment']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?><tr><td colspan="9" class="empty-cell">Заявок пока нет.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
