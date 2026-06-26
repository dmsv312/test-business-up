// Справочники зеркалят enum'ы backend (App\Domain\Enums). Малы и стабильны,
// поэтому продублированы во фронте, а не тянутся отдельным запросом.

export const DIRECTIONS = [
  { value: 'development', label: 'Разработка' },
  { value: 'seo', label: 'SEO' },
  { value: 'context_ads', label: 'Контекстная реклама' },
  { value: 'smm', label: 'SMM' },
  { value: 'serm', label: 'SERM' },
  { value: 'design', label: 'Дизайн' },
  { value: 'content', label: 'Контент' },
  { value: 'placement', label: 'Размещение' },
  { value: 'marketing', label: 'Маркетинг' },
  { value: 'support', label: 'Сопровождение' },
  { value: 'presentation', label: 'Презентация' },
  { value: 'other', label: 'Прочее' },
]

export const ACT_STATUSES = [
  { value: 'not_sent', label: 'Не отправлен' },
  { value: 'awaiting_signature', label: 'Ожидает подписи' },
  { value: 'closed', label: 'Закрыт' },
  { value: 'needs_attention', label: 'Требует внимания' },
]

// Цветовые схемы плашек статусов актов.
export const ACT_STATUS_STYLE = {
  not_sent: 'bg-slate-100 text-slate-600 ring-slate-200',
  awaiting_signature: 'bg-amber-100 text-amber-700 ring-amber-200',
  closed: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
  needs_attention: 'bg-rose-100 text-rose-700 ring-rose-200',
}
