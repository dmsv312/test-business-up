<?php

namespace Database\Seeders;

use App\Domain\Services\StatementImporter;
use App\Domain\Services\StatementParser;
use Illuminate\Database\Seeder;

/**
 * Заполняет БД из приложенной банковской выписки: парсит PDF и прогоняет
 * доменную сборку (клиенты, проекты, оплаты, акты + сырой слой операций).
 */
class StatementSeeder extends Seeder
{
    public function run(): void
    {
        $operations = app(StatementParser::class)->parse(database_path('data/bank_statement.pdf'));
        app(StatementImporter::class)->import($operations);
    }
}
