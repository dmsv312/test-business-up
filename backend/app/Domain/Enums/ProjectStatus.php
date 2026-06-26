<?php

namespace App\Domain\Enums;

/**
 * Состояние проекта по документообороту. Производное: проект «закрыт»,
 * когда по всем его оплатам акты подписаны (статус Closed).
 */
enum ProjectStatus: string
{
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'В работе',
            self::Closed => 'Закрыт',
        };
    }
}
