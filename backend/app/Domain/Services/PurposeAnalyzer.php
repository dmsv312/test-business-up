<?php

namespace App\Domain\Services;

use App\Domain\Enums\ProjectDirection;

/**
 * Извлекает из назначения платежа атрибуты проекта: направление работ,
 * номер счёта, номер договора, этап. Применяется к операциям-выручке.
 * Логика эвристическая (зеркалит формулировки выписки) — best effort;
 * точная часть (выручка vs шум) живёт в OperationClassifier.
 */
class PurposeAnalyzer
{
    /**
     * Направление работ. Порядок проверки = приоритет: специфичные услуги
     * (SERM/SMM/SEO/реклама/дизайн/разработка) важнее общего «сопровождения».
     */
    public function direction(string $purpose): ProjectDirection
    {
        $p = mb_strtolower($purpose);

        return match (true) {
            $this->has($p, ['serm']) => ProjectDirection::Serm,
            $this->has($p, ['smm']) => ProjectDirection::Smm,
            $this->has($p, ['seo', 'продвижени']) => ProjectDirection::Seo,
            $this->has($p, ['директ', 'контекст', 'рекламн', 'рекламы', 'кампани']) => ProjectDirection::ContextAds,
            $this->has($p, ['дизайн', 'прототип']) => ProjectDirection::Design,
            $this->has($p, ['разработ', 'доработ', 'лендинг', 'личного кабинета', 'личный кабинет']) => ProjectDirection::Development,
            $this->has($p, ['объявлени', 'размещени']) => ProjectDirection::Placement,
            $this->has($p, ['копирайт', 'текст', 'контент', 'наполнени', 'публикаци', 'материал']) => ProjectDirection::Content,
            $this->has($p, ['маркетинг']) => ProjectDirection::Marketing,
            $this->has($p, ['презентаци']) => ProjectDirection::Presentation,
            $this->has($p, ['сопровождени', 'обслуживани', 'поддержк']) => ProjectDirection::Support,
            default => ProjectDirection::Other,
        };
    }

    /**
     * Номер(а) счёта на оплату. Один платёж может покрывать несколько счетов
     * («по счетам № 738, 791 и 792») — собираем все, через запятую.
     */
    public function invoiceNumber(string $purpose): ?string
    {
        if (preg_match('/сч(?:[её]т[а-я]*|\.)\s*(?:на оплату)?\s*№?\s*(\d+(?:\s*[,и]+\s*\d+)*)/iu', $purpose, $m)) {
            $numbers = array_values(array_filter(
                array_map('trim', preg_split('/\s*[,и]+\s*/u', trim($m[1]))),
                fn ($n) => $n !== '',
            ));

            return implode(', ', $numbers);
        }

        return null;
    }

    /** Номер договора, если указан. */
    public function contractNumber(string $purpose): ?string
    {
        if (preg_match('/догово?р[а-я]*\s*(?:услуг)?\s*№?\s*(\d+)/iu', $purpose, $m)) {
            return $m[1];
        }

        return null;
    }

    /** Этап оплаты: финальный платёж / этап N / аванс. */
    public function serviceStage(string $purpose): ?string
    {
        $p = mb_strtolower($purpose);

        if (preg_match('/финальн\w*\s+платеж\w*/u', $p)) {
            return 'финальный платёж';
        }
        if (preg_match('/этап\s*\d+/u', $p, $m)) {
            return trim($m[0]);
        }
        if (str_contains($p, 'аванс')) {
            return 'аванс';
        }

        return null;
    }

    private function has(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
