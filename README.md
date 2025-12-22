# SF AdTech

Laravel-приложение трекинга трафика с ролями рекламодателя, веб-мастера и администратора. Поддерживает drag&drop доску офферов, подписки веб-мастеров, редирект по токену `/r/{token}`, статистику по периодам и административное управление.

## Запуск через Docker
1. Скопируйте `.env.example` в `.env` и задайте ключ приложения: `php artisan key:generate` (или выполните внутри контейнера).
2. Поднимите инфраструктуру: `docker compose up -d`.
3. Выполните миграции и сиды внутри контейнера приложения:
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed
   ```
4. Соберите фронт (Bootstrap/JS) через Node-контейнер или локально:
   ```bash
   docker compose run --rm node npm install
   docker compose run --rm node npm run build
   ```
5. Приложение будет доступно на http://localhost:8080.

## Восстановление дампа БД
Дамп находится в `database/dumps/sf_adtech.sql`.
```bash
mysql -h 127.0.0.1 -P 3306 -u sf_adtech -psecret sf_adtech < database/dumps/sf_adtech.sql
```

## Демо-учетные записи
- Администратор: `admin@example.com` / `password123`
- Рекламодатель: `advertiser@example.com` / `password123`
- Веб-мастер: `webmaster@example.com` / `password123`

## Основные команды
- Миграции: `php artisan migrate`
- Сиды: `php artisan db:seed`
- Очистка кеша: `php artisan config:clear`

## Асинхронность и фолбэки
- Drag&drop смена статусов офферов использует `fetch` с CSRF-токеном, при отключенном JS статус меняется через формы на списке офферов.
- Подписки, управление пользователями и другие действия поддерживают AJAX, но сохраняют работу через обычные формы.

## Безопасность
- Используются Eloquent-запросы, Blade-экранирование, CSRF-токены для форм и AJAX.
- Роли и активность пользователя проверяются в middleware `role` и `auth`.
