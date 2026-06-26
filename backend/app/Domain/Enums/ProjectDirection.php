<?php

namespace App\Domain\Enums;

/**
 * Направление работ агентства. Выводится из назначения платежа
 * (см. App\Domain\Services\OperationClassifier). Определяет, к какому
 * проекту клиента относится оплата.
 */
enum ProjectDirection: string
{
    case Development = 'development';
    case Seo = 'seo';
    case ContextAds = 'context_ads';
    case Smm = 'smm';
    case Serm = 'serm';
    case Design = 'design';
    case Content = 'content';
    case Placement = 'placement';
    case Marketing = 'marketing';
    case Support = 'support';
    case Presentation = 'presentation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Development => 'Разработка',
            self::Seo => 'SEO',
            self::ContextAds => 'Контекстная реклама',
            self::Smm => 'SMM',
            self::Serm => 'SERM',
            self::Design => 'Дизайн',
            self::Content => 'Контент',
            self::Placement => 'Размещение',
            self::Marketing => 'Маркетинг',
            self::Support => 'Сопровождение',
            self::Presentation => 'Презентация',
            self::Other => 'Прочее',
        };
    }
}
