<div class="login-card">
    <h1>Вход в админ-панель</h1>
    <p>Демо-доступ: <strong>admin@example.com</strong> / <strong>admin123</strong></p>
    <?php if ($errors): ?><div class="alert alert--error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
    <?php if ($msg = flash('success')): ?><div class="alert alert--success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert--error"><?= e($msg) ?></div><?php endif; ?>
    <form method="post" data-validate>
        <?= csrf_field() ?>
        <label>Email<input type="email" name="email" value="admin@example.com" required></label>
        <label>Пароль<input type="password" name="password" value="admin123" required></label>
        <button class="button" type="submit">Войти</button>
    </form>
    <a href="<?= url('/') ?>">← На сайт</a>
</div>
