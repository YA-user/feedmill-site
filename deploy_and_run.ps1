$ErrorActionPreference = "Stop"

docker compose version | Out-Null
docker compose up --build -d
Write-Host ""
Write-Host "Сайт запущен:        http://localhost:8080"
Write-Host "Админ-панель:        http://localhost:8080/admin"
Write-Host "Логин администратора: admin@example.com"
Write-Host "Пароль:              admin123"
Write-Host "Остановка:           docker compose down"
