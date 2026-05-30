<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manhwa_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manhwa_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('page_url', 2048)->nullable();
            $table->string('referer', 2048)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'manhwa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manhwa_ratings');
    }
};
