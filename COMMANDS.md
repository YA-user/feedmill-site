# Команды для разворачивания и запуска сайта «АгроКорм»

## 1. Самый простой запуск через Docker

Перейдите в папку проекта и выполните одну команду:

```bash
bash deploy_and_run.sh
```

После запуска:

- сайт: <http://localhost:8080>
- админ-панель: <http://localhost:8080/admin>
- логин: `admin@example.com`
- пароль: `admin123`

Остановить контейнеры:

```bash
docker compose down
```

Полностью пересоздать проект с нуля, включая volume с БД:

```bash
docker compose down -v
docker compose up --build -d
```


## Обновление после старой версии проекта

Если сайт уже запускался раньше и Docker сохранил старую базу в volume, новая версия выполнит миграции автоматически. Для чистой демонстрационной базы можно сбросить volume:

```bash
docker compose down -v
bash deploy_and_run.sh
```

## 2. Запуск на Windows PowerShell

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy_and_run.ps1
```

## 3. Локальный запуск без Docker

Нужно установить PHP 8.1+ и расширение `pdo_sqlite`.

```bash
php scripts/init.php
php -S 127.0.0.1:8080 -t public public/index.php
```

Затем открыть:

- <http://127.0.0.1:8080>
- <http://127.0.0.1:8080/admin>

## 4. Сброс локальной SQLite-БД

Linux/macOS:

```bash
rm -f storage/feedmill.sqlite
php scripts/init.php
```

Windows PowerShell:

```powershell
Remove-Item -Force storage\feedmill.sqlite
php scripts/init.php
```

## 5. Проверка демо-данных

```bash
php scripts/tests.php
```

## 6. Где лежит база и заявки

- SQLite-БД: `storage/feedmill.sqlite`
- журнал писем/заявок: `storage/mail.log`
- загруженные изображения: `public/uploads/`

В локальной версии PHP-функция `mail()` может не отправлять реальные письма без SMTP-настройки, поэтому заявка сохраняется в БД и дополнительно пишется в `storage/mail.log`.
