# API-контракт

REST API дашборда. **Contract-first**: документ описывает целевой контракт, под
который пишется backend и который потребляет Vue SPA. Держится в синхроне с
реализацией (`backend/routes/api.php`, контроллеры, API Resources).

- База: `/api`
- Формат: JSON (`Accept: application/json`)
- Авторизация: нет (по ТЗ)
- Деньги: строки с двумя знаками (`"1405820.00"`) — Eloquent `decimal:2`
- Даты: `YYYY-MM-DD`; метки времени ISO-8601

## Общие параметры фильтрации
Применяются к `GET /payments`, `GET /dashboard/summary` (и частично к `projects`):

| параметр | тип | описание |
|---|---|---|
| `from`, `to` | date | период по дате оплаты |
| `client_id` | int | фильтр по юрлицу |
| `project_id` | int | фильтр по проекту |
| `direction` | enum | направление работ (`development`, `seo`, `context_ads`, …) |
| `act_status` | enum | статус акта (`not_sent`, `awaiting_signature`, `closed`, `needs_attention`) |
| `q` | string | поиск по назначению платежа или названию клиента |

---

## GET /api/dashboard/summary
Итоги (считаются на backend), с учётом активных фильтров.

```json
{
  "data": {
    "total_revenue": "1405820.00",
    "clients_count": 19,
    "projects_count": 21,
    "payments_count": 24,
    "closed_acts_amount": "0.00",
    "open_acts_amount": "1405820.00",
    "payments_without_sent_act": 7,
    "payments_sent_not_signed": 6,
    "acts_needs_attention": 4,
    "filtered_out_operations": 23
  }
}
```
`filtered_out_operations` — сколько небизнесовых операций выписки исключено из выручки
(плашка «из 47 операций 24 — выручка»).

## GET /api/clients
Юрлица с агрегатами.

```json
{
  "data": [
    {
      "id": 1, "name": "ООО «Облако-Имидж»", "type": "ooo", "type_label": "ООО",
      "inn": "5031198742", "ogrn": "1235000198741",
      "payments_count": 2, "total_amount": "66800.00",
      "acts": { "closed": 0, "open": 2, "needs_attention": 0 }
    }
  ]
}
```

## GET /api/projects
Проекты с агрегатами (проект = тело работ по клиенту). Доп. фильтры: `client_id`,
`status`, `q`. Направления работ проекта выводятся из его оплат (`directions`).

```json
{
  "data": [
    {
      "id": 1, "name": "Облако-Имидж",
      "client": { "id": 1, "name": "ООО «Облако-Имидж»" },
      "status": "active", "status_label": "В работе",
      "directions": ["serm", "context_ads"],
      "payments_count": 2, "total_amount": "66800.00",
      "acts": { "closed": 0, "open": 2, "needs_attention": 1 }
    }
  ]
}
```

## GET /api/payments
Оплаты с фильтрами (см. общие) + `sort`, `page`. Каждая — с вложенным актом.

```json
{
  "data": [
    {
      "id": 12, "payment_date": "2026-07-18", "amount": "19800.00",
      "client": { "id": 1, "name": "ООО «Облако-Имидж»", "inn": "5031198742" },
      "project": { "id": 1, "name": "Облако-Имидж" },
      "work_direction": "serm", "work_direction_label": "SERM",
      "service_stage": "аванс", "invoice_number": "728", "contract_number": "214",
      "payment_purpose": "Оплата по сч. № 728 …, услуги SERM …",
      "act": {
        "id": 12, "is_sent": true, "sent_at": "2026-07-25T10:00:00Z",
        "is_signed": false, "signed_at": null,
        "status": "awaiting_signature", "status_label": "Ожидает подписи",
        "manager_comment": null
      }
    }
  ],
  "meta": { "current_page": 1, "per_page": 25, "total": 24 }
}
```

## PATCH /api/acts/{id}
Отметить отправку/подпись акта или задать комментарий. Изменения сохраняются;
`status` пересчитывается автоматически (`ActStatusService`).

Запрос (любое подмножество полей):
```json
{ "is_sent": true, "is_signed": true, "manager_comment": "Подписан, скан в папке" }
```
Ответ — обновлённый акт (как объект `act` выше). Валидация — Form Request;
ошибки — стандартный `422` с `errors`.

## GET /api/bank-operations
Сырой слой выписки (все 47 операций) — для прозрачности классификации.
Фильтры: `category`, `direction`, `is_revenue`, `from`, `to`, `q`.

```json
{
  "data": [
    {
      "id": 1, "op_date": "2026-07-15", "direction": "debit", "direction_label": "Списание",
      "amount": "207500.00", "counterparty_name": "УФК по Арктическому краю",
      "counterparty_inn": "7801047935",
      "purpose": "Пополнение ЕНС. Единый налог по спецрежиму …",
      "category": "tax", "category_label": "Налоги", "is_revenue": false
    }
  ]
}
```

---

### Коды ответов
`200` — успех · `404` — ресурс не найден · `422` — ошибка валидации (`PATCH`).
