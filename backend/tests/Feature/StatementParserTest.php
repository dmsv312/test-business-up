<?php

namespace Tests\Feature;

use App\Domain\DTO\ParsedOperation;
use App\Domain\Enums\OperationCategory;
use App\Domain\Enums\OperationDirection;
use App\Domain\Enums\ProjectDirection;
use App\Domain\Services\StatementParser;
use Tests\TestCase;

class StatementParserTest extends TestCase
{
    /** @var list<ParsedOperation> */
    private array $ops;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ops = (new StatementParser())->parse(database_path('data/bank_statement.pdf'));
    }

    public function test_parses_all_operations_from_statement(): void
    {
        // Выписка: 21 списание + 26 поступлений = 47 операций.
        $this->assertCount(47, $this->ops);
    }

    public function test_separates_client_revenue_from_noise_by_control_sum(): void
    {
        $revenue = array_filter($this->ops, fn (ParsedOperation $o) => $o->isRevenue);

        // Ровно 24 операции выручки на контрольную сумму 1 405 820,00 ₽.
        $this->assertCount(24, $revenue);
        $this->assertSame(140_582_000, $this->sumCents($revenue));
        $this->assertCount(23, array_filter($this->ops, fn (ParsedOperation $o) => ! $o->isRevenue));
    }

    public function test_turnover_matches_statement_totals(): void
    {
        // Сверка с «Итого оборотов» выписки — доказывает, что распарсены ВСЕ
        // операции и направление (дебет/кредит) определено верно.
        $debit = array_filter($this->ops, fn (ParsedOperation $o) => $o->direction === OperationDirection::Debit);
        $credit = array_filter($this->ops, fn (ParsedOperation $o) => $o->direction === OperationDirection::Credit);

        $this->assertSame(243_781_153, $this->sumCents($debit), 'Оборот по дебету');   // 2 437 811,53
        $this->assertSame(222_031_000, $this->sumCents($credit), 'Оборот по кредиту');  // 2 220 310,00
    }

    public function test_revenue_operations_carry_project_attributes(): void
    {
        foreach ($this->ops as $op) {
            if ($op->isRevenue) {
                $this->assertNotNull($op->projectDirection, "Нет направления у выручки: {$op->purpose}");
            } else {
                $this->assertNull($op->projectDirection);
            }
        }
    }

    public function test_noise_is_categorised_correctly(): void
    {
        // Налоги, депозиты и зарплаты не должны попадать в выручку.
        $this->assertSame(OperationCategory::Tax, $this->find('УФК', '3280.00')->category);
        $this->assertFalse($this->find('УФК', '3280.00')->isRevenue);
        $this->assertSame(OperationCategory::Deposit, $this->find('ГРОМОВ', '810000.00')->category); // возврат депозита
        $this->assertSame(OperationCategory::Deposit, $this->find('ФИН-МОСТ', '4490.00')->category);  // проценты
        $this->assertSame(OperationCategory::Salary, $this->find('МАЛЬЦЕВА', '27900.00')->category);
        $this->assertSame(OperationCategory::OwnerDraw, $this->find('КИСЕЛЕВ', '324000.00')->category);
    }

    public function test_extracts_project_attributes_from_purpose(): void
    {
        // Облако-Имидж, аванс SERM по сч. № 728, договор № 214.
        $serm = $this->find('ОБЛАКО-ИМИДЖ', '19800.00');
        $this->assertTrue($serm->isRevenue);
        $this->assertSame(ProjectDirection::Serm, $serm->projectDirection);
        $this->assertSame('аванс', $serm->serviceStage);
        $this->assertSame('728', $serm->invoiceNumber);
        $this->assertSame('214', $serm->contractNumber);

        // Вертикаль Модуль — финальный платёж за этап дизайна.
        $design = $this->find('ВЕРТИКАЛЬ', '82400.00');
        $this->assertSame(ProjectDirection::Design, $design->projectDirection);
        $this->assertSame('финальный платёж', $design->serviceStage);

        // Один платёж может покрывать несколько счетов: «по счетам № 738, 791 и 792».
        $multi = $this->find('ОРЛОВ', '69000.00');
        $this->assertSame('738, 791, 792', $multi->invoiceNumber);
    }

    public function test_extracts_counterparty_requisites(): void
    {
        $op = $this->find('ОБЛАКО-ИМИДЖ', '19800.00');
        $this->assertSame('5031198742', $op->counterpartyInn);
        $this->assertSame('1235000198741', $op->counterpartyOgrn);
        $this->assertSame('40702810444010086137', $op->counterpartyAccount);
    }

    /** @param iterable<ParsedOperation> $ops */
    private function sumCents(iterable $ops): int
    {
        $sum = 0;
        foreach ($ops as $op) {
            $sum += $op->amountCents();
        }

        return $sum;
    }

    private function find(string $namePart, string $amount): ParsedOperation
    {
        foreach ($this->ops as $op) {
            if (str_contains($op->counterpartyName, $namePart) && $op->amount === $amount) {
                return $op;
            }
        }

        $this->fail("Операция не найдена: {$namePart} / {$amount}");
    }
}
