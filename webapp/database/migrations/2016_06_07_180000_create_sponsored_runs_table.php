<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsored_runs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->datetime('begin');
            $table->datetime('end');
            $table->boolean('closed')->default(false);
            $table->boolean('with_tshirt')->default(false);
            $table->string('street')->nullable();
            $table->string('housenumber', 31)->nullable();
            $table->string('postcode', 5)->nullable();
            $table->string('city')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsored_runs');
    }
};
