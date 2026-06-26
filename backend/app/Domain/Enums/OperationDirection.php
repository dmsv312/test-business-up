<?php

namespace App\Domain\Enums;

/**
 * Направление банковской операции относительно нашего счёта.
 * Credit — поступление (потенциальная выручка), Debit — списание (расход).
 */
enum OperationDirection: string
{
    case Debit = 'debit';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Debit => 'Списание',
            self::Credit => 'Поступление',
        };
    }
}
