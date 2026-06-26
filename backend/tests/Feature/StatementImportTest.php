<?php

namespace Tests\Feature;

use App\Domain\Enums\ActStatus;
use App\Domain\Enums\ProjectStatus;
use App\Domain\Services\StatementImporter;
use App\Domain\Services\StatementParser;
use App\Models\Act;
use App\Models\BankOperation;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $operations = app(StatementParser::class)->parse(database_path('data/bank_statement.pdf'));
        app(StatementImporter::class)->import($operations);
    }

    public function test_stores_every_operation_in_raw_layer(): void
    {
        $this->assertSame(47, BankOperation::count());
        $this->assertSame(24, BankOperation::where('is_revenue', true)->count());
    }

    public function test_builds_domain_entities_from_revenue_only(): void
    {
        $this->assertSame(19, Client::count());
        $this->assertSame(24, Payment::count());
        $this->assertSame(24, Act::count());
        $this->assertSame('1405820.00', number_format((float) Payment::sum('amount'), 2, '.', ''));
    }

    public function test_deduplicates_clients_by_inn(): void
    {
        // Облако-Имидж заплатил дважды → один клиент, две оплаты, два проекта (разные направления).
        $oblako = Client::where('inn', '5031198742')->sole();
        $this->assertSame(2, $oblako->payments()->count());
        $this->assertSame(2, $oblako->projects()->count());
    }

    public function test_links_payment_to_its_raw_operation(): void
    {
        $payment = Payment::first();
        $this->assertNotNull($payment->bankOperation);
        $this->assertTrue($payment->bankOperation->is_revenue);
    }

    public function test_act_statuses_cover_all_four_states(): void
    {
        $present = Act::pluck('status')->map(fn (ActStatus $s) => $s->value)->unique()->all();

        foreach (ActStatus::cases() as $status) {
            $this->assertContains($status->value, $present, "В сидах нет акта в статусе {$status->value}");
        }
    }

    public function test_project_is_closed_only_when_all_acts_signed(): void
    {
        Project::with('payments.act')->get()->each(function (Project $project) {
            $allClosed = $project->payments->isNotEmpty()
                && $project->payments->every(fn (Payment $p) => $p->act?->status === ActStatus::Closed);

            $this->assertSame(
                $allClosed ? ProjectStatus::Closed : ProjectStatus::Active,
                $project->status,
                "Неверный статус проекта: {$project->name}",
            );
        });
    }

    public function test_project_named_and_grouped_by_contract(): void
    {
        $serm = Project::where('contract_number', '214')->sole();
        $this->assertSame('Облако-Имидж — SERM', $serm->name);
    }
}
