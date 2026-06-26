<?php

namespace App\Domain\Services;

use App\Models\Act;

/**
 * Применяет изменения статуса акта (отметки «отправлен»/«подписан», комментарий)
 * и согласованно проставляет метки времени, после чего пересчитывает статус
 * через ActStatusService. Держит инварианты: нельзя подписать неотправленный акт;
 * снятие отметки «отправлен» сбрасывает подпись.
 */
class ActUpdater
{
    public function __construct(
        private readonly ActStatusService $statusService = new ActStatusService(),
    ) {
    }

    public function update(Act $act, array $data): Act
    {
        if (array_key_exists('is_sent', $data)) {
            $act->is_sent = (bool) $data['is_sent'];

            if ($act->is_sent && $act->sent_at === null) {
                $act->sent_at = now();
            }
            if (! $act->is_sent) {
                $act->sent_at = null;
                $act->is_signed = false;
                $act->signed_at = null;
            }
        }

        if (array_key_exists('is_signed', $data)) {
            $act->is_signed = (bool) $data['is_signed'];

            if ($act->is_signed) {
                $act->is_sent = true;
                $act->sent_at ??= now();
                $act->signed_at ??= now();
            } else {
                $act->signed_at = null;
            }
        }

        if (array_key_exists('manager_comment', $data)) {
            $act->manager_comment = $data['manager_comment'];
        }

        $act->status = $this->statusService->for($act->loadMissing('payment'));
        $act->save();

        return $act;
    }
}
