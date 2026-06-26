# Журнал работ

Хронология сборки по этапам. Каждый этап — отдельный коммит (см. историю git).
План этапов — в [`PLAN.md`](PLAN.md) §13.

## Этап 1 — каркас репозитория и план ✅
- Инициализирован отдельный git-репозиторий, изолирован от родительского hh-bot.
- Перенесены ТЗ и мок-данные в `docs/brief/`.
- Написаны `docs/PLAN.md` (подробный план + архитектура), `README.md`, `.gitignore`.
- Зафиксирован инсайт по данным (24 выручки из 47 операций, контрольная сумма сходится).

## Этап 2 — каркас backend и доменная схема ✅
- Laravel 13.17 в `backend/`, подключён API-роутинг (без Sanctum/авторизации).
- Слой `app/Domain/Enums`: ClientType, ProjectDirection, ProjectStatus,
  OperationDirection, OperationCategory, ActStatus (все с `label()` на RU).
- Миграции доменной схемы: clients, bank_operations, projects, payments, acts.
  Цикл `bank_operation ↔ payment` разорван (FK только в `payments.bank_operation_id`).
- Модели Eloquent со связями и кастами enumّов.
- `config/dashboard.php`: reference_date (2026-08-14), act_attention_days (30), own_account.
- `.env.example` переведён на PostgreSQL (цель docker); локально/тесты — SQLite.
- Проверено фактом: миграции прогоняются на sqlite, роут `api/ping` поднимается,
  enum'ы и конфиг грузятся.

## Следующий шаг
Этап 3 — парсер выписки (`StatementParser`) + классификатор (`OperationClassifier`)
с тестом на контрольную сумму (24 операции выручки = 1 405 820,00 ₽).
