<?php

namespace App\Domain\Enums;

/**
 * Категория банковской операции. Ключевой инструмент отделения сигнала от шума:
 * только ClientRevenue превращается в доменную оплату (Payment) и попадает на
 * дашборд проектов. Остальное (налоги, зарплаты, аренда, комиссии, депозиты,
 * субподряд) сохраняется в сыром слое, но из выручки исключается.
 */
enum OperationCategory: string
{
    case ClientRevenue = 'client_revenue';
    case Tax = 'tax';
    case Salary = 'salary';
    case OwnerDraw = 'owner_draw';
    case Rent = 'rent';
    case BankFee = 'bank_fee';
    case Deposit = 'deposit';
    case Subcontractor = 'subcontractor';

    public function label(): string
    {
        return match ($this) {
            self::ClientRevenue => 'Выручка по проекту',
            self::Tax => 'Налоги',
            self::Salary => 'Зарплата',
            self::OwnerDraw => 'Вывод средств ИП',
            self::Rent => 'Аренда',
            self::BankFee => 'Банковская комиссия',
            self::Deposit => 'Депозит / проценты',
            self::Subcontractor => 'Субподряд',
        };
    }

    /** Является ли операция бизнес-выручкой по проекту. */
    public function isClientRevenue(): bool
    {
        return $this === self::ClientRevenue;
    }
}
