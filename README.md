# SF AdTech

Laravel-приложение трекинга трафика с ролями рекламодателя, веб-мастера и администратора. Поддерживает drag&drop доску офферов, подписки веб-мастеров, редирект по токену `/r/{token}`, статистику по периодам и административное управление.

---

## Архитектура

- **Nginx** — точка входа (`http://localhost:8080`)
- **PHP-FPM (`app`)** — Laravel-приложение
- **MySQL 8 (`mysql`)** — база данных
- **Node (`node`)** — сборка фронта (Vite / Bootstrap)

Все сервисы работают в одной Docker-сети.

---

## Запуск через Docker
1. Скопируйте `.env.example` в `.env`.
2. Поднимите инфраструктуру: 
- `docker compose up -d`.
3. Установите PHP-зависимости: 
- `docker compose exec app composer install`.
4. Сгенерируйте ключ приложения: 
- `docker compose exec app php artisan key:generate`.
5. Очистите кеш конфигурации:
- `docker compose exec app php artisan config:clear`.
6. Выполните миграции и сиды внутри контейнера приложения:
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed
   ```
7. ### Сборка фронта (через Node-контейнер)
   ```bash
   docker compose exec node npm ci
   docker compose exec node npm run build
   ```
8. Приложение будет доступно на http://localhost:8080.

## Восстановление дампа БД
Если требуется восстановить тестовые данные из дампа (вместо сидов):
Дамп находится в `database/dumps/sf_adtech.sql`.
```bash
docker compose exec mysql mysql -u root -p sf_adtech < database/dumps/sf_adtech.sql
```
После восстановления дампа выполнение сидов не требуется.

## Демо-учетные записи
- Администратор: `admin@example.com` / `password123`
- Рекламодатель: `advertiser@example.com` / `password123`
- Веб-мастер: `webmaster@example.com` / `password123`

## Основные команды
Миграции:
docker compose exec app php artisan migrate

Сиды:
docker compose exec app php artisan db:seed

Очистка кеша:
docker compose exec app php artisan config:clear

## Асинхронность и фолбэки
- Drag&drop смена статусов офферов использует `fetch` с CSRF-токеном, при отключенном JS статус меняется через формы на списке офферов.
- Подписки, управление пользователями и другие действия поддерживают AJAX, но сохраняют работу через обычные формы.

## Безопасность
- Используются Eloquent-запросы, Blade-экранирование, CSRF-токены для форм и AJAX.
- Роли и активность пользователя проверяются в middleware `role` и `auth`.
