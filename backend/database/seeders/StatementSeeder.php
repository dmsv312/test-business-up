<?php

namespace Database\Seeders;

use App\Domain\Services\StatementImporter;
use App\Domain\Services\StatementParser;
use App\Models\BankOperation;
use Illuminate\Database\Seeder;

/**
 * Заполняет БД из приложенной банковской выписки: парсит PDF и прогоняет
 * доменную сборку (клиенты, проекты, оплаты, акты + сырой слой операций).
 * Идемпотентен: если данные уже есть, повторный запуск пропускается
 * (важно для `migrate --seed` при перезапуске контейнера).
 */
class StatementSeeder extends Seeder
{
    public function run(): void
    {
        if (BankOperation::query()->exists()) {
            return;
        }

        $operations = app(StatementParser::class)->parse(database_path('data/bank_statement.pdf'));
        app(StatementImporter::class)->import($operations);
    }
}
