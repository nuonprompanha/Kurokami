<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const DEFAULT_GENRES = [
        'Action',
        'Adventure',
        'Comedy',
        'Drama',
        'Fantasy',
        'Horror',
        'Romance',
        'Sci-Fi',
        'Slice of Life',
        'Martial Arts',
        'Mystery',
        'Psychological',
        'School Life',
        'Supernatural',
        'Tragedy',
        'Isekai',
        'Historical',
        'Sports',
    ];

    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('manhwa_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manhwa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['manhwa_id', 'genre_id']);
        });

        $now = now();
        $genreIdsByName = [];

        foreach (self::DEFAULT_GENRES as $name) {
            $id = DB::table('genres')->insertGetId([
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $genreIdsByName[$name] = $id;
        }

        if (Schema::hasColumn('manhwas', 'genres')) {
            foreach (DB::table('manhwas')->get(['id', 'genres']) as $manhwa) {
                $names = json_decode($manhwa->genres ?? '[]', true);

                if (! is_array($names)) {
                    continue;
                }

                foreach ($names as $name) {
                    $name = trim((string) $name);

                    if ($name === '') {
                        continue;
                    }

                    if (! isset($genreIdsByName[$name])) {
                        $id = DB::table('genres')->insertGetId([
                            'name' => $name,
                            'slug' => $this->uniqueGenreSlug($name),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $genreIdsByName[$name] = $id;
                    }

                    DB::table('manhwa_genre')->insertOrIgnore([
                        'manhwa_id' => $manhwa->id,
                        'genre_id' => $genreIdsByName[$name],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            Schema::table('manhwas', function (Blueprint $table) {
                $table->dropColumn('genres');
            });
        }
    }

    public function down(): void
    {
        Schema::table('manhwas', function (Blueprint $table) {
            $table->json('genres')->nullable()->after('series_type');
        });

        Schema::dropIfExists('manhwa_genre');
        Schema::dropIfExists('genres');
    }

    private function uniqueGenreSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'genre';
        $slug = $base;
        $suffix = 1;

        while (DB::table('genres')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
};
