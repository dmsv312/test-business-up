<script setup>
import { ref } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'

// Просмотр + инлайн-редактирование комментария менеджера. Один компонент на
// таблицу и карточку. Состояние редактирования локальное (одна ячейка за раз).
const props = defineProps({
  payment: { type: Object, required: true },
})

const store = useDashboardStore()
const editing = ref(false)
const draft = ref('')

const start = () => {
  draft.value = props.payment.act.manager_comment || ''
  editing.value = true
}
const cancel = () => { editing.value = false }
const save = async () => {
  await store.updateAct(props.payment, { manager_comment: draft.value.trim() || null })
  editing.value = false
}

const btn = 'rounded border px-2 py-0.5 text-xs font-medium whitespace-nowrap'
</script>

<template>
  <div v-if="editing" class="flex flex-col gap-1">
    <textarea
      v-model="draft"
      rows="2"
      class="w-full min-w-[11rem] rounded border border-slate-300 px-2 py-1 text-xs focus:border-slate-400 focus:outline-none"
      placeholder="Комментарий менеджера…"
    ></textarea>
    <div class="flex gap-1">
      <button :class="[btn, 'border-emerald-300 text-emerald-700 hover:bg-emerald-50']" @click="save">Сохранить</button>
      <button :class="[btn, 'border-slate-300 text-slate-500 hover:bg-slate-100']" @click="cancel">Отмена</button>
    </div>
  </div>
  <div v-else-if="payment.act.manager_comment" class="flex items-start gap-1">
    <span class="line-clamp-2 text-xs text-slate-600" :title="payment.act.manager_comment">{{ payment.act.manager_comment }}</span>
    <button
      :class="[btn, 'shrink-0 border-slate-200 text-slate-500 hover:bg-slate-100']"
      title="Изменить комментарий"
      @click="start"
    >Изм.</button>
  </div>
  <button
    v-else
    class="self-start text-xs text-slate-400 hover:text-slate-600"
    title="Добавить комментарий"
    @click="start"
  >+ комментарий</button>
</template>
