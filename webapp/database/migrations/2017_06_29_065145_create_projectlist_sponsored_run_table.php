<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projectlist_sponsored_run', function (Blueprint $table) {
            $table->unsignedBigInteger('projectlist_id');
            $table->foreign('projectlist_id')->references('id')->on('projectlists')->onDelete('cascade');

            $table->unsignedBigInteger('sponsored_run_id');
            $table->foreign('sponsored_run_id')->references('id')->on('sponsored_runs')->onDelete('cascade');

            $table->primary(['projectlist_id', 'sponsored_run_id'], 'projectlist_sponsored_run_primary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projectlist_sponsored_run');
    }
};
