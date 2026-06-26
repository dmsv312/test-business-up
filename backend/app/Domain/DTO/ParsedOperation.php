<?php

namespace App\Domain\DTO;

use App\Domain\Enums\OperationCategory;
use App\Domain\Enums\OperationDirection;
use App\Domain\Enums\ProjectDirection;

/**
 * Одна операция выписки в структурированном виде — результат работы парсера.
 * Иммутабельный носитель данных между парсером и сидером (доменной сборкой).
 * Поля проекта (projectDirection / invoice / contract / stage) заполняются
 * только для операций-выручки (isRevenue = true).
 */
final readonly class ParsedOperation
{
    public function __construct(
        public string $date,                 // Y-m-d
        public OperationDirection $direction,
        public string $amount,               // "207500.00"
        public string $docNumber,
        public string $counterpartyName,
        public ?string $counterpartyInn,
        public ?string $counterpartyOgrn,
        public string $counterpartyAccount,
        public ?string $bankBik,
        public ?string $bankName,
        public string $purpose,
        public OperationCategory $category,
        public bool $isRevenue,
        public ?ProjectDirection $projectDirection = null,
        public ?string $invoiceNumber = null,
        public ?string $contractNumber = null,
        public ?string $serviceStage = null,
    ) {
    }

    /** Сумма в копейках — для точных сверок без ошибок плавающей точки. */
    public function amountCents(): int
    {
        return (int) round(((float) $this->amount) * 100);
    }
}
