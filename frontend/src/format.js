// Форматтеры отображения.

export function money(value) {
  const n = Number(value ?? 0)
  return n.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₽'
}

export function date(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString('ru-RU')
}
