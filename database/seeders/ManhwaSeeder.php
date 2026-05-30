<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Manhwa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ManhwaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Chapter::query()->delete();
        Manhwa::query()->delete();

        $types = ['New', 'Hot', null, 'New', 'Hot'];
        $manhwas = [
            ['title' => 'Solo Leveling', 'category' => 'Action'],
            ['title' => 'Tower of God', 'category' => 'Adventure'],
            ['title' => 'The Beginning After the End', 'category' => 'Fantasy'],
            ['title' => 'Omniscient Reader', 'category' => 'Action'],
            ['title' => 'Lookism', 'category' => 'Drama'],
            ['title' => 'True Beauty', 'category' => 'Romance'],
            ['title' => 'Sweet Home', 'category' => 'Horror'],
            ['title' => 'Noblesse', 'category' => 'Action'],
            ['title' => 'The Gamer', 'category' => 'Fantasy'],
            ['title' => 'Wind Breaker', 'category' => 'Sports'],
            ['title' => 'Eleceed', 'category' => 'Comedy'],
            ['title' => 'UnOrdinary', 'category' => 'Drama'],
            ['title' => 'Hardcore Leveling Warrior', 'category' => 'Action'],
            ['title' => 'Return of the Mount Hua Sect', 'category' => 'Action'],
        ];

        foreach ($manhwas as $index => $manhwaData) {
            $slug = Str::slug($manhwaData['title']);
            $chapterCount = random_int(20, 80);
            $latestChapter = $chapterCount;

            $manhwa = Manhwa::query()->create([
                'title' => $manhwaData['title'],
                'slug' => $slug,
                'category' => $manhwaData['category'],
                'badge' => $types[$index % count($types)],
                'status' => 'Ongoing',
                'series_type' => 'Manhwa',
                'author' => 'Author '.($index + 1),
                'tags' => ['adventure', 'fantasy'],
                'views' => random_int(1200, 98500),
                'cover_image' => 'https://picsum.photos/seed/'.$slug.'/300/420',
                'description' => 'Read '.$manhwaData['title'].' online on Korukami.',
            ]);

            $genre = Genre::query()->where('name', $manhwaData['category'])->first();

            if ($genre) {
                $manhwa->genres()->sync([$genre->id]);
            }

            for ($chapter = $latestChapter; $chapter >= max(1, $latestChapter - 4); $chapter--) {
                Chapter::query()->create([
                    'manhwa_id' => $manhwa->id,
                    'chapter_number' => $chapter,
                    'title' => 'Chapter '.$chapter,
                ]);
            }
        }
    }
}
