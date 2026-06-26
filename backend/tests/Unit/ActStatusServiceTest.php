<?php

namespace Tests\Unit;

use App\Domain\Enums\ActStatus;
use App\Domain\Services\ActStatusService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ActStatusServiceTest extends TestCase
{
    private ActStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // «Сейчас» = 2026-08-14, порог = 21 день → граница давности 2026-07-24.
        $this->service = new ActStatusService('2026-08-14', 21);
    }

    public function test_closed_when_sent_and_signed(): void
    {
        $status = $this->service->compute(true, true, CarbonImmutable::parse('2026-08-01'));
        $this->assertSame(ActStatus::Closed, $status);
    }

    public function test_signed_act_is_closed_regardless_of_age(): void
    {
        $status = $this->service->compute(true, true, CarbonImmutable::parse('2026-01-01'));
        $this->assertSame(ActStatus::Closed, $status);
    }

    public function test_awaiting_signature_when_sent_not_signed_and_recent(): void
    {
        $status = $this->service->compute(true, false, CarbonImmutable::parse('2026-08-10'));
        $this->assertSame(ActStatus::AwaitingSignature, $status);
    }

    public function test_not_sent_when_not_sent_and_recent(): void
    {
        $status = $this->service->compute(false, false, CarbonImmutable::parse('2026-08-10'));
        $this->assertSame(ActStatus::NotSent, $status);
    }

    public function test_needs_attention_when_old_and_not_closed(): void
    {
        $this->assertSame(
            ActStatus::NeedsAttention,
            $this->service->compute(true, false, CarbonImmutable::parse('2026-07-10')),
        );
        $this->assertSame(
            ActStatus::NeedsAttention,
            $this->service->compute(false, false, CarbonImmutable::parse('2026-07-10')),
        );
    }

    public function test_attention_threshold_boundary(): void
    {
        // Ровно на границе (2026-07-24) — уже «старая» (сравнение <=).
        $this->assertSame(
            ActStatus::NeedsAttention,
            $this->service->compute(true, false, CarbonImmutable::parse('2026-07-24')),
        );
        // На день позже границы — ещё «свежая».
        $this->assertSame(
            ActStatus::AwaitingSignature,
            $this->service->compute(true, false, CarbonImmutable::parse('2026-07-25')),
        );
    }
}
