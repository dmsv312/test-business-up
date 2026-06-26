<?php

namespace App\Console\Commands;

use App\Domain\Services\StatementImporter;
use App\Domain\Services\StatementParser;
use Illuminate\Console\Command;

class ImportStatementCommand extends Command
{
    protected $signature = 'statement:import {path? : Путь к PDF выписки}';

    protected $description = 'Парсит банковскую выписку (PDF) и загружает операции, проекты, оплаты и акты в БД';

    public function handle(StatementParser $parser, StatementImporter $importer): int
    {
        $path = $this->argument('path') ?? database_path('data/bank_statement.pdf');
        $this->info("Парсинг выписки: {$path}");

        $operations = $parser->parse($path);
        $revenue = array_filter($operations, fn ($o) => $o->isRevenue);

        $importer->import($operations);

        $this->info(sprintf(
            'Загружено: %d операций, из них выручка по проектам: %d.',
            count($operations),
            count($revenue),
        ));

        return self::SUCCESS;
    }
}
