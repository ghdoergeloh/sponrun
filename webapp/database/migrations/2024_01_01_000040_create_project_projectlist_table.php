<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_projectlist', function (Blueprint $table) {
            // project_id is unsignedInteger (not bigInteger) because projects.id is unsignedInteger
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->unsignedBigInteger('projectlist_id');
            $table->foreign('projectlist_id')->references('id')->on('projectlists')->onDelete('cascade');

            $table->primary(['project_id', 'projectlist_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_projectlist');
    }
};
