<?php
return [
    'app_name' => 'АгроКорм — комбикормовый завод',
    'timezone' => 'Europe/Moscow',
    'db_path' => getenv('DB_PATH') ?: __DIR__ . '/../storage/feedmill.sqlite',
    'mail_log' => __DIR__ . '/../storage/mail.log',
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@agrokorm.local',
    'demo_notice' => 'Локальная демонстрационная версия. Письма сохраняются в storage/mail.log.',
];
