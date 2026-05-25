<?php $isEdit = !empty($editing); ?>
<div class="page-head">
    <div>
        <p class="eyebrow">Настройки</p>
        <h1>Пользователи</h1>
        <p class="muted">Создавайте доступы для администратора и контент-менеджеров.</p>
    </div>
</div>
<?php if ($errors): ?><div class="alert alert--error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="grid-two-admin">
    <form class="panel admin-form" method="post" data-validate novalidate>
        <h2><?= $isEdit ? 'Редактировать пользователя' : 'Новый пользователь' ?></h2>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
        <label><span>Имя *</span><input type="text" name="name" value="<?= e($editing['name'] ?? '') ?>" required></label>
        <label><span>Email *</span><input type="email" name="email" value="<?= e($editing['email'] ?? '') ?>" required></label>
        <label><span>Роль</span><select name="role"><option value="content_manager"<?= selected($editing['role'] ?? '', 'content_manager') ?>>Контент-менеджер</option><option value="admin"<?= selected($editing['role'] ?? '', 'admin') ?>>Администратор</option></select></label>
        <label><span>Пароль <?= $isEdit ? '(оставьте пустым, если не менять)' : '*' ?></span><input type="password" name="password" <?= $isEdit ? '' : 'required' ?>></label>
        <fieldset class="permission-box">
            <legend>Доступные разделы для контент-менеджера</legend>
            <p class="muted">Администратор получает полный доступ автоматически.</p>
            <div class="permission-grid">
                <?php foreach (($permissionSections ?? []) as $sectionKey => $sectionLabel): ?>
                    <label class="check">
                        <input type="checkbox" name="permissions[]" value="<?= e($sectionKey) ?>"<?= in_array($sectionKey, $editingPermissions ?? [], true) ? ' checked' : '' ?>>
                        <?= e($sectionLabel) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <label class="check"><input type="checkbox" name="active" value="1"<?= checked($editing['active'] ?? 1) ?>> Активен</label>
        <div class="form-actions">
            <button class="button" type="submit">Сохранить</button>
            <?php if ($isEdit): ?><a class="button button--ghost" href="<?= url('/admin/users') ?>">Отмена</a><?php endif; ?>
        </div>
    </form>
    <div class="panel">
        <h2>Список пользователей</h2>
        <table><thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Активен</th><th></th></tr></thead><tbody>
        <?php foreach ($users as $user): ?>
            <tr><td><?= e($user['name']) ?></td><td><?= e($user['email']) ?></td><td><?= ($user['role'] ?? '') === 'admin' ? 'Администратор' : 'Контент-менеджер' ?></td><td><?= $user['active'] ? '<span class="status-badge status-badge--yes">Да</span>' : '<span class="status-badge status-badge--no">Нет</span>' ?></td><td><a class="table-action" href="<?= url('/admin/users?edit=' . $user['id']) ?>">Изменить</a></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</div>
