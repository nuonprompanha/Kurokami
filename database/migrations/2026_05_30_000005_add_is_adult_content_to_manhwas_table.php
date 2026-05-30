<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manhwas', function (Blueprint $table) {
            $table->boolean('is_adult_content')->default(false)->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('manhwas', function (Blueprint $table) {
            $table->dropColumn('is_adult_content');
        });
    }
};
