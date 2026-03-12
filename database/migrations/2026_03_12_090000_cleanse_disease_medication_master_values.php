<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $this->cleanseDiseaseMaster();
            $this->cleanseMedicationMaster();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleansing migration is intentionally irreversible.
    }

    protected function cleanseDiseaseMaster(): void
    {
        $combinedRows = DB::table('diseases')
            ->select(['id', 'name', 'category'])
            ->where('name', 'like', '%,%')
            ->orWhere('name', 'like', '%;%')
            ->orWhere('name', 'like', '%|%')
            ->orderBy('id')
            ->get();

        foreach ($combinedRows as $row) {
            $parts = $this->splitNames((string) $row->name);
            if (empty($parts)) {
                continue;
            }

            $targetIds = [];
            foreach ($parts as $partName) {
                $targetIds[] = $this->resolveDiseaseId($partName, $row->category);
            }
            $targetIds = array_values(array_unique(array_filter($targetIds)));

            if (empty($targetIds)) {
                continue;
            }

            $visitIds = DB::table('disease_visit')
                ->where('disease_id', $row->id)
                ->pluck('visit_id')
                ->all();

            if (!empty($visitIds)) {
                $payload = [];
                $now = now();
                foreach ($visitIds as $visitId) {
                    foreach ($targetIds as $targetId) {
                        $payload[] = [
                            'visit_id' => $visitId,
                            'disease_id' => $targetId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                DB::table('disease_visit')->upsert(
                    $payload,
                    ['visit_id', 'disease_id'],
                    ['updated_at']
                );
            }

            DB::table('visits')
                ->where('disease_id', $row->id)
                ->update(['disease_id' => $targetIds[0]]);

            DB::table('disease_visit')->where('disease_id', $row->id)->delete();
            DB::table('diseases')->where('id', $row->id)->delete();
        }

        $this->dedupeDiseaseMaster();
    }

    protected function cleanseMedicationMaster(): void
    {
        $combinedRows = DB::table('medications')
            ->select(['id', 'name', 'category'])
            ->where('name', 'like', '%,%')
            ->orWhere('name', 'like', '%;%')
            ->orWhere('name', 'like', '%|%')
            ->orderBy('id')
            ->get();

        foreach ($combinedRows as $row) {
            $parts = $this->splitNames((string) $row->name);
            if (empty($parts)) {
                continue;
            }

            $targetIds = [];
            foreach ($parts as $partName) {
                $targetIds[] = $this->resolveMedicationId($partName, $row->category);
            }
            $targetIds = array_values(array_unique(array_filter($targetIds)));

            if (empty($targetIds)) {
                continue;
            }

            $visitIds = DB::table('medication_visit')
                ->where('medication_id', $row->id)
                ->pluck('visit_id')
                ->all();

            if (!empty($visitIds)) {
                $payload = [];
                $now = now();
                foreach ($visitIds as $visitId) {
                    foreach ($targetIds as $targetId) {
                        $payload[] = [
                            'visit_id' => $visitId,
                            'medication_id' => $targetId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                DB::table('medication_visit')->upsert(
                    $payload,
                    ['visit_id', 'medication_id'],
                    ['updated_at']
                );
            }

            DB::table('visits')
                ->where('medication_id', $row->id)
                ->update(['medication_id' => $targetIds[0]]);

            DB::table('medication_visit')->where('medication_id', $row->id)->delete();
            DB::table('medications')->where('id', $row->id)->delete();
        }

        $this->dedupeMedicationMaster();
    }

    protected function dedupeDiseaseMaster(): void
    {
        $rows = DB::table('diseases')
            ->select(['id', 'name', 'category'])
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $key = $this->canonicalName((string) $row->name);
            if ($key === '') {
                continue;
            }
            $groups[$key][] = $row;
        }

        foreach ($groups as $groupRows) {
            if (count($groupRows) < 2) {
                continue;
            }

            $keeper = $groupRows[0];
            $duplicateIds = array_map(fn ($item) => $item->id, array_slice($groupRows, 1));

            $visitIds = DB::table('disease_visit')
                ->whereIn('disease_id', $duplicateIds)
                ->pluck('visit_id')
                ->all();

            if (!empty($visitIds)) {
                $now = now();
                $payload = array_map(function ($visitId) use ($keeper, $now) {
                    return [
                        'visit_id' => $visitId,
                        'disease_id' => $keeper->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $visitIds);

                DB::table('disease_visit')->upsert(
                    $payload,
                    ['visit_id', 'disease_id'],
                    ['updated_at']
                );
            }

            DB::table('visits')
                ->whereIn('disease_id', $duplicateIds)
                ->update(['disease_id' => $keeper->id]);

            DB::table('disease_visit')->whereIn('disease_id', $duplicateIds)->delete();
            DB::table('diseases')->whereIn('id', $duplicateIds)->delete();
        }
    }

    protected function dedupeMedicationMaster(): void
    {
        $rows = DB::table('medications')
            ->select(['id', 'name', 'category'])
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $key = $this->canonicalName((string) $row->name);
            if ($key === '') {
                continue;
            }
            $groups[$key][] = $row;
        }

        foreach ($groups as $groupRows) {
            if (count($groupRows) < 2) {
                continue;
            }

            $keeper = $groupRows[0];
            $duplicateIds = array_map(fn ($item) => $item->id, array_slice($groupRows, 1));

            $visitIds = DB::table('medication_visit')
                ->whereIn('medication_id', $duplicateIds)
                ->pluck('visit_id')
                ->all();

            if (!empty($visitIds)) {
                $now = now();
                $payload = array_map(function ($visitId) use ($keeper, $now) {
                    return [
                        'visit_id' => $visitId,
                        'medication_id' => $keeper->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $visitIds);

                DB::table('medication_visit')->upsert(
                    $payload,
                    ['visit_id', 'medication_id'],
                    ['updated_at']
                );
            }

            DB::table('visits')
                ->whereIn('medication_id', $duplicateIds)
                ->update(['medication_id' => $keeper->id]);

            DB::table('medication_visit')->whereIn('medication_id', $duplicateIds)->delete();
            DB::table('medications')->whereIn('id', $duplicateIds)->delete();
        }
    }

    protected function resolveDiseaseId(string $name, ?string $category = null): int
    {
        $canonical = $this->canonicalName($name);
        if ($canonical === '') {
            return 0;
        }

        $existing = DB::table('diseases')
            ->select(['id'])
            ->whereRaw('LOWER(TRIM(name)) = ?', [$canonical])
            ->orderBy('id')
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('diseases')->insertGetId([
            'name' => $this->normalizeName($name),
            'category' => $category,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function resolveMedicationId(string $name, ?string $category = null): int
    {
        $canonical = $this->canonicalName($name);
        if ($canonical === '') {
            return 0;
        }

        $existing = DB::table('medications')
            ->select(['id'])
            ->whereRaw('LOWER(TRIM(name)) = ?', [$canonical])
            ->orderBy('id')
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('medications')->insertGetId([
            'name' => $this->normalizeName($name),
            'category' => $category,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function splitNames(string $raw): array
    {
        $parts = preg_split('/\s*(?:,|;|\|)\s*/', $raw) ?: [];
        $normalized = [];

        foreach ($parts as $part) {
            $name = $this->normalizeName($part);
            if ($name !== '') {
                $normalized[] = $name;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function canonicalName(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    protected function normalizeName(string $value): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($value));
        return $clean ?? '';
    }
};
