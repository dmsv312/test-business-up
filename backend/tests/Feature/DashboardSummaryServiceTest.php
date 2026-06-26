<?php

namespace Tests\Feature;

use App\Domain\Services\DashboardSummaryService;
use App\Domain\Services\StatementImporter;
use App\Domain\Services\StatementParser;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardSummaryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $operations = app(StatementParser::class)->parse(database_path('data/bank_statement.pdf'));
        app(StatementImporter::class)->import($operations);
        $this->service = app(DashboardSummaryService::class);
    }

    public function test_summary_totals_without_filters(): void
    {
        $s = $this->service->build();

        $this->assertSame('1405820.00', $s['total_revenue']);
        $this->assertSame(24, $s['payments_count']);
        $this->assertSame(19, $s['clients_count']);
        $this->assertSame(19, $s['projects_count']);
        $this->assertSame(23, $s['filtered_out_operations']);
        $this->assertSame(8, $s['acts_needs_attention']);
    }

    public function test_closed_and_open_amounts_split_the_total(): void
    {
        $s = $this->service->build();

        $sum = fn (string $rub) => (int) round(((float) $rub) * 100);
        $this->assertSame(
            $sum($s['total_revenue']),
            $sum($s['closed_acts_amount']) + $sum($s['open_acts_amount']),
        );
    }

    public function test_summary_respects_client_filter(): void
    {
        $oblako = Client::where('inn', '5031198742')->sole();
        $s = $this->service->build(['client_id' => $oblako->id]);

        $this->assertSame(2, $s['payments_count']);
        $this->assertSame('66800.00', $s['total_revenue']);
        $this->assertSame(1, $s['clients_count']);
    }

    public function test_summary_respects_act_status_filter(): void
    {
        $s = $this->service->build(['act_status' => 'closed']);

        // 12 оплат с подписанными (закрытыми) актами.
        $this->assertSame(12, $s['payments_count']);
    }
}
