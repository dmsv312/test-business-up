# CLAUDE.md — учёт оплат, проектов и закрывающих актов

Контекст для агентских сессий по этому репозиторию. Держать актуальным.

## Что это
Тестовое задание: дашборд для digital-агентства, связывающий **проект / юрлицо →
оплаты → этапы → статус закрывающих актов**. Источник данных — банковская выписка
(PDF). Оцениваются бизнес-логика, модель данных, разделение слоёв — **не вёрстка**.

- ТЗ: [`docs/brief/TASK.md`](docs/brief/TASK.md)
- Мок-данные: [`docs/brief/bank_statement_project_data_clean.pdf`](docs/brief/)
- Подробный план + архитектура: [`docs/PLAN.md`](docs/PLAN.md)
- Журнал работ: [`docs/WORKLOG.md`](docs/WORKLOG.md)

## Стек
- Backend: **Laravel 13** (PHP 8.4), REST API, без авторизации.
- Frontend: **Vue 3** SPA (Vite + Pinia + Tailwind) — отдельным приложением.
- БД: **PostgreSQL 16** (в docker-compose); тесты и локальная разработка — **SQLite**.
- Инфраструктура: **Docker Compose** (вся, включая БД).

## Ключевой инсайт по данным (основа решения)
Выписка ИП Громов А.В. за 15.07–14.08.2026, дата формирования **14.08.2026**.
47 операций, но **только 24 — выручка по проектам (1 405 820,00 ₽)**; остальное шум
(налоги, зарплаты, аренда, комиссии, депозиты, субподряд). Контроль: 24 выручки +
возврат депозита 810 000 + проценты 4 490 = 2 220 310 ₽ = итог по кредиту в выписке.
Сырой слой (`bank_operations`) хранит все 47 с категорией; доменный — только 24.
Актов в источнике нет → статусы засеваются. «Сейчас» для давности = `2026-08-14`
(config `dashboard.reference_date`), т.к. данные в будущем.

## Архитектура слоёв
```
PDF → StatementParser + OperationClassifier → bank_operations (сырой слой, все 47)
    → clients · projects · payments · acts (доменный слой, только выручка)
    → Domain Services (статусы актов, итоги, фильтры)
    → REST API (тонкие контроллеры + API Resources)
    → Vue SPA (дашборд)
```
Бизнес-логика живёт в `backend/app/Domain/*` (Enums, Services, Support), НЕ в
контроллерах и не размазана по моделям.

## Структура
```
backend/                 Laravel 13 API
  app/Domain/Enums/      ClientType, ProjectDirection, ProjectStatus,
                         OperationDirection, OperationCategory, ActStatus
  app/Domain/Services/   StatementParser, OperationClassifier, ActStatusService,
                         DashboardSummaryService (по мере сборки)
  app/Models/            Client, BankOperation, Project, Payment, Act
  database/migrations/   доменная схема (clients/bank_operations/projects/payments/acts)
  routes/api.php         REST-эндпоинты
  config/dashboard.php   reference_date, act_attention_days, own_account
frontend/                Vue 3 SPA (добавляется на этапе фронта)
docker-compose.yml       app + nginx + db(pgsql) + frontend (добавляется)
docs/                    PLAN.md, WORKLOG.md, brief/
```

## Команды
```bash
# Backend (локально, на sqlite)
cd backend && php artisan migrate:fresh --seed
php artisan test
php artisan route:list --path=api

# Полный запуск (для ревьюера) — после этапа docker
docker compose up -d
```

## Конвенции
- Enum'ы — backed string, с `label()` (RU) в `app/Domain/Enums`.
- Касты моделей — через метод `casts()` (Laravel 11+).
- Деньги — `decimal(15,2)`, каст `decimal:2`.
- Контроллеры тонкие: запрос → доменный сервис → API Resource. Валидация — Form Requests.
- Тесты — на SQLite (`:memory:`), включая контрольную сумму парсера (24 / 1 405 820 ₽).

## Git / коммиты
- Отдельный репозиторий: `github.com/dmsv312/test-business-up` (SSH, ключ `id_ed25519_github`).
- Коммиты **без** трейлера Co-Authored-By, под `Dmitrii <dm.sv312@gmail.com>`,
  логичными этапами (см. WORKLOG). Изолирован от родительского hh-bot репо.

## Деплой
Laravel Forge — настраивается **в самом конце** (нужен бесплатный домен).

## Текущий статус
См. [`docs/WORKLOG.md`](docs/WORKLOG.md) — последний завершённый этап и следующий шаг.
