<?php

namespace Tests\Feature;

use App\Domain\Services\StatementImporter;
use App\Domain\Services\StatementParser;
use App\Models\Act;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $operations = app(StatementParser::class)->parse(database_path('data/bank_statement.pdf'));
        app(StatementImporter::class)->import($operations);
    }

    public function test_dashboard_summary_endpoint(): void
    {
        $this->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.total_revenue', '1405820.00')
            ->assertJsonPath('data.payments_count', 24)
            ->assertJsonPath('data.clients_count', 19)
            ->assertJsonPath('data.filtered_out_operations', 23);
    }

    public function test_payments_endpoint_is_paginated_and_shaped(): void
    {
        $this->getJson('/api/payments')
            ->assertOk()
            ->assertJsonPath('meta.total', 24)
            ->assertJsonStructure([
                'data' => [['id', 'payment_date', 'amount', 'work_direction', 'client' => ['id', 'name', 'inn'], 'project' => ['id', 'name'], 'act' => ['status', 'status_label']]],
                'meta' => ['total', 'per_page', 'current_page'],
            ]);
    }

    public function test_payments_endpoint_filters_by_act_status(): void
    {
        $this->getJson('/api/payments?act_status=closed')
            ->assertOk()
            ->assertJsonPath('meta.total', 12);
    }

    public function test_payments_endpoint_filters_by_client(): void
    {
        $oblako = Client::where('inn', '5031198742')->sole();

        $this->getJson("/api/payments?client_id={$oblako->id}")
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_clients_endpoint(): void
    {
        $this->getJson('/api/clients')
            ->assertOk()
            ->assertJsonCount(19, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'type_label', 'inn', 'payments_count', 'total_amount', 'acts' => ['closed', 'open', 'needs_attention']]]]);
    }

    public function test_projects_endpoint_exposes_multi_payment_projects(): void
    {
        $response = $this->getJson('/api/projects')->assertOk()->assertJsonCount(19, 'data');

        $oblako = collect($response->json('data'))->firstWhere('name', 'Облако-Имидж');
        $this->assertSame(2, $oblako['payments_count']);
        $this->assertEqualsCanonicalizing(['serm', 'context_ads'], $oblako['directions']);
    }

    public function test_bank_operations_raw_layer_endpoint(): void
    {
        $this->getJson('/api/bank-operations')->assertOk()->assertJsonCount(47, 'data');
        $this->getJson('/api/bank-operations?is_revenue=false')->assertOk()->assertJsonCount(23, 'data');
    }

    public function test_patch_act_marks_closed_and_persists(): void
    {
        $act = Act::where('status', '!=', 'closed')->firstOrFail();

        $this->patchJson("/api/acts/{$act->id}", ['is_sent' => true, 'is_signed' => true])
            ->assertOk()
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.is_signed', true);

        $this->assertDatabaseHas('acts', [
            'id' => $act->id,
            'status' => 'closed',
            'is_signed' => true,
        ]);
    }

    public function test_patch_act_validation_rejects_bad_input(): void
    {
        $act = Act::first();

        $this->patchJson("/api/acts/{$act->id}", ['is_sent' => 'maybe'])
            ->assertStatus(422);
    }
}
