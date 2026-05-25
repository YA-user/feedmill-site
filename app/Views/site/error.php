<!doctype html>
<html lang="ru">
<head><meta charset="utf-8"><title>Ошибка</title><link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>"></head>
<body><main class="content" style="max-width:900px;margin:40px auto"><div class="alert alert--error"><h1>Ошибка приложения</h1><p><?= e($message ?? 'Неизвестная ошибка') ?></p><p>Проверьте, что база создана командой <code>php scripts/init.php</code>.</p></div></main></body>
</html>
