<?php
function admin_display_value(array $section, string $key, mixed $value): string {
    foreach ($section['fields'] as $field) {
        if ($field['name'] === $key && ($field['type'] ?? '') === 'select') {
            return e($field['options'][(string)$value] ?? $value);
        }
    }
    if ($key === 'is_active' || $key === 'active') {
        return !empty($value) ? '<span class="status-badge status-badge--yes">Да</span>' : '<span class="status-badge status-badge--no">Нет</span>';
    }
    if ($key === 'image' || $key === 'video_url') {
        return trim((string)$value) !== '' ? '<span class="status-badge status-badge--yes">Есть</span>' : '<span class="status-badge status-badge--muted">По умолчанию</span>';
    }
    if ($key === 'price') {
        return format_money((float)$value);
    }
    return e((string)$value);
}
?>
<div class="page-head">
    <div>
        <p class="eyebrow">Раздел сайта</p>
        <h1><?= e($section['title']) ?></h1>
        <p class="muted"><?= e($section['description'] ?? 'Добавляйте, изменяйте и удаляйте записи этого раздела.') ?></p>
    </div>
    <a class="button" href="<?= url('/admin/' . $sectionKey . '/create') ?>">Добавить запись</a>
</div>
<div class="table-wrap">
<table>
    <thead><tr>
        <?php foreach ($section['columns'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
        <th>Управление</th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <?php foreach ($section['columns'] as $key => $label): ?><td><?= admin_display_value($section, $key, $row[$key] ?? '') ?></td><?php endforeach; ?>
            <td class="actions">
                <a class="table-action" href="<?= url('/admin/' . $sectionKey . '/' . $row['id'] . '/edit') ?>">Изменить</a>
                <form method="post" action="<?= url('/admin/' . $sectionKey . '/' . $row['id'] . '/delete') ?>" data-confirm="Удалить запись?">
                    <?= csrf_field() ?><button type="submit" class="link-danger">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="<?= count($section['columns']) + 1 ?>" class="empty-cell">Записей пока нет. Нажмите “Добавить запись”, чтобы создать первую.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
