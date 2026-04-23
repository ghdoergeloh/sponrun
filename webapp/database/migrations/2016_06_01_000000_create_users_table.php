<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('ext_personnel_no')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->date('birthday');
            $table->string('street');
            $table->string('housenumber', 31);
            $table->string('postcode', 5);
            $table->string('city');
            $table->enum('gender', ['m', 'f']);
            $table->string('password');
            $table->boolean('confirmed')->default(false);
            $table->string('confirmation_code')->nullable();
            $table->boolean('wants_newsletter')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
