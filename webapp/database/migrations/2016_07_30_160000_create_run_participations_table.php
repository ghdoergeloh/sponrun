<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sponsored_run_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            $table->integer('laps')->default(0);
            $table->string('hash')->unique();
            $table->enum('tshirt_size', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_participations');
    }
};
