#!/usr/bin/env bash
set -euo pipefail

if docker compose version >/dev/null 2>&1; then
  COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE="docker-compose"
else
  echo "Docker Compose не найден. Установите Docker Desktop или docker-compose." >&2
  exit 1
fi

$COMPOSE up --build -d

echo ""
echo "Сайт запущен:        http://localhost:8080"
echo "Админ-панель:        http://localhost:8080/admin"
echo "Логин администратора: admin@example.com"
echo "Пароль:              admin123"
echo "Остановка:           $COMPOSE down"
