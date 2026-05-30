<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manhwas', function (Blueprint $table) {
            $table->renameColumn('type', 'badge');
            $table->text('alternative_titles')->nullable()->after('description');
            $table->string('author')->nullable()->after('alternative_titles');
            $table->string('artist')->nullable()->after('author');
            $table->string('status')->nullable()->after('artist');
            $table->string('series_type')->nullable()->after('status');
            $table->json('genres')->nullable()->after('series_type');
            $table->json('tags')->nullable()->after('genres');
        });
    }

    public function down(): void
    {
        Schema::table('manhwas', function (Blueprint $table) {
            $table->dropColumn([
                'alternative_titles',
                'author',
                'artist',
                'status',
                'series_type',
                'genres',
                'tags',
            ]);
            $table->renameColumn('badge', 'type');
        });
    }
};
