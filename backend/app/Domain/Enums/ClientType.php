<?php

namespace App\Domain\Enums;

/**
 * Организационно-правовая форма плательщика, выведенная из выписки
 * (ООО / АНО / ИП — по префиксу наименования).
 */
enum ClientType: string
{
    case Company = 'ooo';
    case JointStock = 'ao';
    case NonProfit = 'ano';
    case Entrepreneur = 'ip';

    public function label(): string
    {
        return match ($this) {
            self::Company => 'ООО',
            self::JointStock => 'АО',
            self::NonProfit => 'АНО',
            self::Entrepreneur => 'ИП',
        };
    }
}
