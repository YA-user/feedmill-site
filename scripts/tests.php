<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$pdo = Database::pdo();
$checks = [
    'users' => 1,
    'products' => 1,
    'news' => 1,
    'pages' => 1,
    'media' => 1,
];
foreach ($checks as $table => $min) {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
    if ($count < $min) {
        fwrite(STDERR, "Ошибка: таблица {$table} содержит {$count} записей\n");
        exit(1);
    }
}
foreach (['news.image', 'news.video_url', 'products.video_url'] as $column) {
    [$table, $name] = explode('.', $column);
    $hasColumn = false;
    foreach ($pdo->query('PRAGMA table_info(' . $table . ')')->fetchAll() as $info) {
        if (($info['name'] ?? '') === $name) {
            $hasColumn = true;
            break;
        }
    }
    if (!$hasColumn) {
        fwrite(STDERR, "Ошибка: колонка {$column} не найдена\n");
        exit(1);
    }
}
echo "Smoke tests passed: база заполнена, медиа и новые поля доступны.\n";
