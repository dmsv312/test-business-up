<?php

namespace App\Domain\Services;

use App\Domain\DTO\ParsedOperation;
use App\Domain\Enums\OperationDirection;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Парсер банковской выписки (PDF) → массив ParsedOperation.
 *
 * Структура текста выписки (по строкам, поля внутри строки через табы):
 *   ДД.ММ.ГГГГ                           ← начало операции (отдельная строка)
 *   <счёт 20 цифр> / ИНН / ОГРН / имя    ← плательщик (блок A)
 *   <счёт 20 цифр> / ИНН / ОГРН / имя    ← получатель (блок B)
 *   <сумма>\t<№док>\t<ВО>[\t<банк>[\t<назначение>]]   ← строка-якорь
 *   [БИК … наименование банка]           ← может переноситься на 2 строки
 *   [назначение платежа]                 ← может переноситься на несколько строк
 *
 * Направление определяется позицией нашего счёта: плательщик (блок A) → списание,
 * получатель (блок B) → поступление.
 */
class StatementParser
{
    private const ANCHOR = '/^[\d\x{00a0} ]+,\d{2}\t\d+\t\d+(\t.*)?$/u';

    public function __construct(
        private readonly OperationClassifier $classifier = new OperationClassifier(),
        private readonly PurposeAnalyzer $analyzer = new PurposeAnalyzer(),
    ) {
    }

    /** @return list<ParsedOperation> */
    public function parse(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Файл выписки не найден: {$path}");
        }

        $text = (new PdfParser())->parseFile($path)->getText();
        $lines = $this->cleanLines($text);

        return array_map(
            fn (array $op) => $this->parseOperation($op),
            $this->splitIntoOperations($lines),
        );
    }

    /** Убирает повторяющиеся шапки страниц, футеры и итоговый блок. */
    private function cleanLines(string $text): array
    {
        $clean = [];
        $inHeader = false;

        foreach (preg_split('/\r?\n/', $text) as $line) {
            // Итоговый блок в конце выписки — операции закончились.
            if (preg_match('/^Количество операций/u', $line)) {
                break;
            }
            // Шапка страницы: от строки «<дата>\tФинСервис…» до «Дебет\tКредит» включительно.
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}\tФинСервис/u', $line)) {
                $inHeader = true;
                continue;
            }
            if ($inHeader) {
                if (preg_match('/^Дебет\tКредит$/u', $line)) {
                    $inHeader = false;
                }
                continue;
            }
            // Футер «N / M» и пустые строки.
            if (trim($line) === '' || preg_match('~^\d+\s*/\s*\d+$~u', trim($line))) {
                continue;
            }

            $clean[] = $line;
        }

        return $clean;
    }

    /**
     * Режет поток строк на операции по строкам-датам (ДД.ММ.ГГГГ целиком).
     *
     * @return list<list<string>>
     */
    private function splitIntoOperations(array $lines): array
    {
        $operations = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $line)) {
                if ($current !== null) {
                    $operations[] = $current;
                }
                $current = [$line];
                continue;
            }
            if ($current !== null) {
                $current[] = $line;
            }
        }
        if ($current !== null) {
            $operations[] = $current;
        }

        return $operations;
    }

    private function parseOperation(array $lines): ParsedOperation
    {
        $date = $this->normalizeDate($lines[0]);

        $anchorIdx = $this->findAnchorIndex($lines);
        $anchorParts = explode("\t", $lines[$anchorIdx]);

        $amount = $this->normalizeAmount($anchorParts[0]);
        $docNumber = $anchorParts[1];

        // «Хвост» (банк + назначение): доп. поля строки-якоря + последующие строки.
        $tail = array_merge(
            array_slice($anchorParts, 3),
            array_slice($lines, $anchorIdx + 1),
        );
        [$bik, $bankName, $purpose] = $this->parseTail($tail);

        // Стороны операции — два блока, каждый начинается со счёта (20 цифр).
        $partyLines = array_slice($lines, 1, $anchorIdx - 1);
        [$blockA, $blockB] = $this->splitParties($partyLines);
        $partyA = $this->parseParty($blockA);
        $partyB = $this->parseParty($blockB);

        $own = (string) config('dashboard.own_account');
        $direction = $partyA['account'] === $own
            ? OperationDirection::Debit
            : OperationDirection::Credit;
        // Контрагент — сторона, которая не является нашим счётом.
        $counterparty = $partyA['account'] === $own ? $partyB : $partyA;

        $category = $this->classifier->classify($direction, $purpose);
        $isRevenue = $category->isClientRevenue();

        return new ParsedOperation(
            date: $date,
            direction: $direction,
            amount: $amount,
            docNumber: $docNumber,
            counterpartyName: $counterparty['name'] ?? '',
            counterpartyInn: $counterparty['inn'],
            counterpartyOgrn: $counterparty['ogrn'],
            counterpartyAccount: $counterparty['account'] ?? '',
            bankBik: $bik,
            bankName: $bankName,
            purpose: $purpose,
            category: $category,
            isRevenue: $isRevenue,
            projectDirection: $isRevenue ? $this->analyzer->direction($purpose) : null,
            invoiceNumber: $isRevenue ? $this->analyzer->invoiceNumber($purpose) : null,
            contractNumber: $isRevenue ? $this->analyzer->contractNumber($purpose) : null,
            serviceStage: $isRevenue ? $this->analyzer->serviceStage($purpose) : null,
        );
    }

    private function findAnchorIndex(array $lines): int
    {
        foreach ($lines as $i => $line) {
            if (preg_match(self::ANCHOR, $line)) {
                return $i;
            }
        }

        throw new RuntimeException('Не найдена строка суммы в операции: '.implode(' | ', $lines));
    }

    /** Делит строки сторон на два блока по счёту (строка из 20 цифр). */
    private function splitParties(array $partyLines): array
    {
        $starts = [];
        foreach ($partyLines as $i => $line) {
            if (preg_match('/^\d{20}$/', $line)) {
                $starts[] = $i;
            }
        }
        if (count($starts) < 2) {
            throw new RuntimeException('Ожидались два счёта в операции: '.implode(' | ', $partyLines));
        }

        return [
            array_slice($partyLines, $starts[0], $starts[1] - $starts[0]),
            array_slice($partyLines, $starts[1]),
        ];
    }

    private function parseParty(array $block): array
    {
        $party = ['account' => null, 'inn' => null, 'ogrn' => null, 'name' => null];

        foreach ($block as $line) {
            if (preg_match('/^\d{20}$/', $line)) {
                $party['account'] = $line;
            } elseif (preg_match('/^ИНН (\d+)$/u', $line, $m)) {
                $party['inn'] = $m[1];
            } elseif (preg_match('/^(?:ОГРНИП|ОГРН) (\d+)$/u', $line, $m)) {
                $party['ogrn'] = $m[1];
            } else {
                $party['name'] = trim($line);
            }
        }

        return $party;
    }

    /** Разбирает «хвост» на БИК, наименование банка и назначение платежа. */
    private function parseTail(array $tail): array
    {
        $tail = array_values(array_filter($tail, fn ($l) => trim($l) !== ''));
        $bik = $bankName = null;
        $purposeStart = 0;

        if (isset($tail[0]) && preg_match('/^БИК (\d{9})\s*(.*)$/u', $tail[0], $m)) {
            $bik = $m[1];
            $bankName = trim($m[2]);
            $purposeStart = 1;
            // Наименование банка может переноситься на вторую строку.
            if (isset($tail[1]) && $this->isBankContinuation($tail[1])) {
                $bankName = trim($bankName.' '.$tail[1]);
                $purposeStart = 2;
            }
        }

        $purpose = trim(preg_replace('/\s+/u', ' ', implode(' ', array_slice($tail, $purposeStart))));

        return [$bik, $bankName !== '' ? $bankName : null, $purpose];
    }

    private function isBankContinuation(string $line): bool
    {
        return (bool) preg_match('~^(России//|ФИЛИАЛ\b|АО "[^"]*"$|ООО "[^"]*"$|ПАО )~u', $line);
    }

    private function normalizeDate(string $date): string
    {
        [$d, $m, $y] = explode('.', $date);

        return "{$y}-{$m}-{$d}";
    }

    private function normalizeAmount(string $raw): string
    {
        $clean = preg_replace('/[\s\x{00a0}]/u', '', $raw);
        $clean = str_replace(',', '.', $clean);

        return number_format((float) $clean, 2, '.', '');
    }
}
