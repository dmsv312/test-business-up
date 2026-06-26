<?php

namespace App\Domain\Enums;

/**
 * Статус закрывающего акта по оплате. Рассчитывается автоматически из
 * is_sent / is_signed / возраста оплаты (см. App\Domain\Services\ActStatusService).
 */
enum ActStatus: string
{
    case NotSent = 'not_sent';
    case AwaitingSignature = 'awaiting_signature';
    case Closed = 'closed';
    case NeedsAttention = 'needs_attention';

    public function label(): string
    {
        return match ($this) {
            self::NotSent => 'Не отправлен',
            self::AwaitingSignature => 'Ожидает подписи',
            self::Closed => 'Закрыт',
            self::NeedsAttention => 'Требует внимания',
        };
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }
}
