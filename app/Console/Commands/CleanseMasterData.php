<?php

namespace App\Console\Commands;

use App\Services\MasterDataCleansingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanseMasterData extends Command
{
    protected $signature = 'master:cleanse-data {--dry-run : Preview only, rollback all changes}';

    protected $description = 'Cleanse diseases/medications master data (split combined names and merge duplicates)';

    public function __construct(
        protected MasterDataCleansingService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $before = $this->service->stats();

        $this->info('Current data quality status:');
        $this->table(['Metric', 'Count'], [
            ['combined_diseases', $before['combined_diseases']],
            ['combined_medications', $before['combined_medications']],
            ['duplicate_disease_keys', $before['duplicate_disease_keys']],
            ['duplicate_medication_keys', $before['duplicate_medication_keys']],
        ]);

        if ($this->option('dry-run')) {
            DB::beginTransaction();
            try {
                $after = $this->service->cleanse();
                DB::rollBack();

                $this->newLine();
                $this->warn('Dry-run mode: no changes were persisted.');
                $this->info('Projected status after cleansing:');
                $this->table(['Metric', 'Count'], [
                    ['combined_diseases', $after['combined_diseases']],
                    ['combined_medications', $after['combined_medications']],
                    ['duplicate_disease_keys', $after['duplicate_disease_keys']],
                    ['duplicate_medication_keys', $after['duplicate_medication_keys']],
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Dry-run failed: ' . $e->getMessage());
                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        try {
            $after = $this->service->cleanse();
        } catch (\Throwable $e) {
            $this->error('Cleansing failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Cleansing completed.');
        $this->table(['Metric', 'Count'], [
            ['combined_diseases', $after['combined_diseases']],
            ['combined_medications', $after['combined_medications']],
            ['duplicate_disease_keys', $after['duplicate_disease_keys']],
            ['duplicate_medication_keys', $after['duplicate_medication_keys']],
        ]);

        return self::SUCCESS;
    }
}
