<?php
$isEdit = !empty($row['id']);
function admin_field_value(array $row, string $name): string { return (string)($row[$name] ?? ''); }
?>
<div class="page-head">
    <div>
        <p class="eyebrow"><?= $isEdit ? 'Редактирование записи' : 'Новая запись' ?></p>
        <h1><?= e($section['title']) ?></h1>
        <p class="muted"><?= e($section['description'] ?? 'Заполните поля и сохраните изменения.') ?></p>
    </div>
    <a class="button button--ghost" href="<?= url('/admin/' . $sectionKey) ?>">Назад к списку</a>
</div>
<?php if ($errors): ?><div class="alert alert--error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="admin-form panel" method="post" enctype="multipart/form-data" data-validate novalidate>
    <?= csrf_field() ?>
    <?php foreach ($section['fields'] as $field): ?>
        <?php $name = $field['name']; $type = $field['type']; $value = admin_field_value($row, $name); ?>
        <label class="field field--<?= e($type) ?>">
            <span><?= e($field['label']) ?><?= !empty($field['required']) ? ' *' : '' ?></span>
            <?php if ($type === 'textarea'): ?>
                <textarea name="<?= e($name) ?>" rows="5"<?= !empty($field['required']) ? ' required' : '' ?>><?= e($value) ?></textarea>
            <?php elseif ($type === 'wysiwyg'): ?>
                <textarea class="wysiwyg" name="<?= e($name) ?>" rows="10"<?= !empty($field['required']) ? ' required' : '' ?>><?= e($value) ?></textarea>
            <?php elseif ($type === 'select'): ?>
                <select name="<?= e($name) ?>"<?= !empty($field['required']) ? ' required' : '' ?>>
                    <option value="">Выберите</option>
                    <?php foreach (($field['options'] ?? []) as $optionValue => $optionLabel): ?>
                        <option value="<?= e($optionValue) ?>"<?= selected($value, $optionValue) ?>><?= e($optionLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($type === 'checkbox'): ?>
                <input type="checkbox" name="<?= e($name) ?>" value="1"<?= checked($value !== '' ? $value : 1) ?>>
            <?php elseif ($type === 'file_image'): ?>
                <?php $preview = $value ?: (($section['table'] ?? '') === 'news' ? default_image('news') : default_image()); ?>
                <img class="preview" src="<?= asset($preview) ?>" alt="preview">
                <input type="file" name="<?= e($name) ?>" accept="image/*">
            <?php else: ?>
                <input type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>"<?= !empty($field['required']) ? ' required' : '' ?><?= isset($field['step']) ? ' step="' . e($field['step']) . '"' : '' ?><?= isset($field['placeholder']) ? ' placeholder="' . e($field['placeholder']) . '"' : '' ?>>
            <?php endif; ?>
            <?php if (!empty($field['hint'])): ?><small><?= e($field['hint']) ?></small><?php endif; ?>
        </label>
    <?php endforeach; ?>
    <div class="form-actions">
        <button class="button" type="submit">Сохранить</button>
        <a class="button button--ghost" href="<?= url('/admin/' . $sectionKey) ?>">Отмена</a>
    </div>
</form>
