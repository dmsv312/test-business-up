<?php

namespace App\Domain\Services;

use App\Domain\Enums\OperationCategory;
use App\Domain\Enums\OperationDirection;

/**
 * Классификатор банковских операций — ядро отделения выручки от шума.
 * По направлению (поступление/списание) и тексту назначения определяет
 * категорию. Только поступления, не являющиеся возвратами/процентами по
 * депозитам, считаются выручкой по проектам (ClientRevenue).
 *
 * Важно про налоги: ключ «налог» НЕ используется намеренно — слова
 * «НДС не облагается» / «Без налога (НДС)» встречаются почти в каждом
 * назначении. Налоговые платежи ловим по конкретным признакам (НДФЛ, ЕНС).
 */
class OperationClassifier
{
    public function classify(OperationDirection $direction, string $purpose): OperationCategory
    {
        $p = mb_strtolower($purpose);

        if ($direction === OperationDirection::Credit) {
            // Поступления: всё, кроме возвратов вкладов и процентов по ним, — выручка.
            if ($this->matches($p, ['депозит', 'вклад', 'процент'])) {
                return OperationCategory::Deposit;
            }

            return OperationCategory::ClientRevenue;
        }

        // Списания (расходы, шум для дашборда выручки) — по убыванию специфичности.
        return match (true) {
            $this->matches($p, ['ндфл', 'енс', 'единый налог', 'спецрежим']) => OperationCategory::Tax,
            $this->matches($p, ['перевод средств предпринимателя на личный счет']) => OperationCategory::OwnerDraw,
            $this->matches($p, ['заработная плата', 'по реестру']) => OperationCategory::Salary,
            $this->matches($p, ['аренд']) => OperationCategory::Rent,
            $this->matches($p, ['комисси']) => OperationCategory::BankFee,
            $this->matches($p, ['вклад', 'депозит']) => OperationCategory::Deposit,
            default => OperationCategory::Subcontractor, // прочие исходящие платежи поставщикам/субподряду
        };
    }

    private function matches(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
