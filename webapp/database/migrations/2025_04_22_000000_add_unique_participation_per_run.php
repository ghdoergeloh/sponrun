<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard against legacy duplicates: keep the row with most laps per (user, run).
        $duplicates = DB::table('run_participations')
            ->select('user_id', 'sponsored_run_id', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id', 'sponsored_run_id')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('run_participations')
                ->where('user_id', $dup->user_id)
                ->where('sponsored_run_id', $dup->sponsored_run_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('run_participations', function (Blueprint $table) {
            $table->unique(['user_id', 'sponsored_run_id'], 'run_participations_user_run_unique');
        });
    }

    public function down(): void
    {
        Schema::table('run_participations', function (Blueprint $table) {
            $table->dropUnique('run_participations_user_run_unique');
        });
    }
};
