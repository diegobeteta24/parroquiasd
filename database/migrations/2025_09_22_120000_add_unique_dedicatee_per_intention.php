<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ensure only one dedicatee per intention
        // 1) Clean up existing duplicates keeping the earliest record per intention
        if (Schema::hasColumn('intention_dedicatees', 'intention_id')) {
            // Find intention_ids with more than one row
            $duplicates = DB::table('intention_dedicatees')
                ->select('intention_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('intention_id')
                ->having('cnt', '>', 1)
                ->pluck('intention_id');

            if ($duplicates->isNotEmpty()) {
                DB::beginTransaction();
                try {
                    foreach ($duplicates as $intentionId) {
                        // Keep the earliest (smallest id) record, delete the rest
                        $rows = DB::table('intention_dedicatees')
                            ->where('intention_id', $intentionId)
                            ->orderBy('id', 'asc')
                            ->pluck('id');

                        if ($rows->count() > 1) {
                            $keepId = $rows->first();
                            $toDelete = $rows->slice(1)->all();
                            DB::table('intention_dedicatees')->whereIn('id', $toDelete)->delete();
                        }
                    }
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            // 2) Add the unique constraint now that data is clean
            Schema::table('intention_dedicatees', function (Blueprint $table) {
                $table->unique('intention_id', 'intention_dedicatees_intention_id_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('intention_dedicatees', function (Blueprint $table) {
            $table->dropUnique('intention_dedicatees_intention_id_unique');
        });
    }
};
