<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportManhwaChaptersJob;
use App\Models\Genre;
use App\Models\Manhwa;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use RuntimeException;
use Throwable;

class ManhwaController extends Controller
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function index(Request $request)
    {
        $manhwas = Manhwa::query()
            ->withCount('chapters')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.manhwas.index', [
            'manhwas' => $manhwas,
            'search' => $request->query('search'),
        ]);
    }

    public function create()
    {
        return view('admin.manhwas.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateManhwa($request, requireZip: true);

        $slug = $this->resolveUniqueSlug($validated['title']);
        $coverPath = null;

        try {
            $coverPath = $request->file('cover_image')
                ? $this->storeCoverImage($request->file('cover_image'), $slug)
                : null;

            $manhwa = Manhwa::query()->create(array_merge(
                $this->manhwaPayload($validated),
                [
                    'slug' => $slug,
                    'cover_image' => $coverPath,
                    'views' => 0,
                ]
            ));

            $manhwa->genres()->sync($validated['genre_ids'] ?? []);

            $this->deferChapterImport($manhwa, $request->file('chapters_zip'));

            return redirect()
                ->route('admin.manhwas.index')
                ->with('success', 'Manhwa created. Chapter images are importing in the background — refresh the edit page in a few minutes.');
        } catch (Throwable $exception) {
            Manhwa::query()->where('slug', $slug)->delete();

            if ($coverPath) {
                $this->imageStorage->delete($coverPath);
            }

            $this->imageStorage->deleteDirectory('manhwa/'.$slug);

            return back()
                ->withInput()
                ->withErrors([$this->uploadErrorField($exception) => $exception->getMessage()]);
        }
    }

    public function edit(Manhwa $manhwa)
    {
        $manhwa->load([
            'genres',
            'chapters' => fn ($query) => $query
                ->whereHas('pages')
                ->withCount('pages')
                ->with(['pages' => fn ($pageQuery) => $pageQuery->orderBy('sort_order')->limit(1)])
                ->orderByDesc('chapter_number'),
        ]);

        return view('admin.manhwas.edit', array_merge($this->formData(), [
            'manhwa' => $manhwa,
        ]));
    }

    public function update(Request $request, Manhwa $manhwa)
    {
        $validated = $this->validateManhwa($request, manhwa: $manhwa);

        $slug = $this->resolveUniqueSlug($validated['title'], ignoreId: $manhwa->id);

        $oldSlug = $manhwa->slug;

        if ($request->hasFile('cover_image')) {
            try {
                $this->deleteCoverIfStored($manhwa);
                $manhwa->cover_image = $this->storeCoverImage($request->file('cover_image'), $slug);
            } catch (Throwable $exception) {
                return back()
                    ->withInput()
                    ->withErrors(['cover_image' => $exception->getMessage()]);
            }
        }

        if ($slug !== $oldSlug && ! $this->imageStorage->usesPostimages()) {
            try {
                $this->moveManhwaStorage($oldSlug, $slug);
                $this->refreshStoragePathsAfterSlugChange($manhwa, $oldSlug, $slug);
            } catch (Throwable $exception) {
                return back()
                    ->withInput()
                    ->withErrors(['title' => 'Could not move stored files after title change: '.$exception->getMessage()]);
            }
        }

        $manhwa->update(array_merge(
            $this->manhwaPayload($validated),
            [
                'slug' => $slug,
                'cover_image' => $manhwa->cover_image,
            ]
        ));

        $manhwa->genres()->sync($validated['genre_ids'] ?? []);

        $message = 'Manhwa updated successfully.';

        if ($request->hasFile('chapters_zip')) {
            try {
                $this->deferChapterImport($manhwa, $request->file('chapters_zip'));
                $message .= ' Chapter images are importing in the background — refresh this page in a few minutes.';
            } catch (Throwable $exception) {
                return back()
                    ->withInput()
                    ->withErrors(['chapters_zip' => $exception->getMessage()]);
            }
        }

        return redirect()
            ->route('admin.manhwas.edit', $manhwa)
            ->with('success', $message);
    }

    public function destroy(Manhwa $manhwa)
    {
        $this->deleteManhwaStorage($manhwa);
        $manhwa->delete();

        return redirect()
            ->route('admin.manhwas.index')
            ->with('success', 'Manhwa deleted successfully.');
    }

    private function deferChapterImport(Manhwa $manhwa, $zipFile): void
    {
        $zipPath = $zipFile->store('manhwa-imports', 'local');

        if (! is_string($zipPath) || $zipPath === '') {
            throw new RuntimeException('Unable to store the chapter zip for import.');
        }

        ImportManhwaChaptersJob::dispatch($manhwa->id, $zipPath)->afterResponse();
    }

    private function uploadErrorField(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'cover')) {
            return 'cover_image';
        }

        if (str_contains($message, 'postimages') || str_contains($message, 'zip') || str_contains($message, 'chapter')) {
            return 'chapters_zip';
        }

        return 'chapters_zip';
    }

    private function validateManhwa(Request $request, ?Manhwa $manhwa = null, bool $requireZip = false): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Manhwa::CATEGORIES)],
            'badge' => ['nullable', Rule::in(Manhwa::BADGES)],
            'description' => ['nullable', 'string'],
            'alternative_titles' => ['nullable', 'string', 'max:500'],
            'author' => ['nullable', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(Manhwa::STATUSES)],
            'series_type' => ['nullable', Rule::in(Manhwa::SERIES_TYPES)],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'cover_image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(5120),
            ],
            'chapters_zip' => [
                $requireZip ? 'required' : 'nullable',
                'file',
                'mimes:zip',
                'max:512000',
            ],
            'is_adult_content' => ['nullable', 'boolean'],
        ]);

        $validated['genre_ids'] = array_values(array_unique($validated['genre_ids'] ?? []));
        $validated['tags'] = Manhwa::parseListInput($validated['tags'] ?? null);
        $validated['is_adult_content'] = $request->boolean('is_adult_content');

        return $validated;
    }

    private function manhwaPayload(array $validated): array
    {
        return [
            'title' => $validated['title'],
            'category' => $validated['category'],
            'badge' => $validated['badge'] ?? null,
            'description' => $validated['description'] ?? null,
            'alternative_titles' => $validated['alternative_titles'] ?? null,
            'author' => $validated['author'] ?? null,
            'artist' => $validated['artist'] ?? null,
            'status' => $validated['status'] ?? null,
            'series_type' => $validated['series_type'] ?? null,
            'tags' => $validated['tags'],
            'is_adult_content' => $validated['is_adult_content'],
        ];
    }

    private function formData(): array
    {
        return [
            'categories' => Manhwa::CATEGORIES,
            'badges' => Manhwa::BADGES,
            'statuses' => Manhwa::STATUSES,
            'seriesTypes' => Manhwa::SERIES_TYPES,
            'genres' => Genre::query()->orderBy('name')->get(),
        ];
    }

    private function resolveUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'manhwa';
        }

        $candidate = $base;
        $suffix = 1;

        while (
            Manhwa::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function storeCoverImage($file, string $slug): string
    {
        $path = 'manhwa/'.$slug.'/cover.'.$file->getClientOriginalExtension();

        return $this->imageStorage->storeUploadedFile($file, $path);
    }

    private function deleteCoverIfStored(Manhwa $manhwa): void
    {
        if ($manhwa->cover_image) {
            $this->imageStorage->delete($manhwa->cover_image);
        }
    }

    private function deleteManhwaStorage(Manhwa $manhwa): void
    {
        $this->imageStorage->deleteDirectory($manhwa->storageDirectory());
    }

    private function moveManhwaStorage(string $oldSlug, string $newSlug): void
    {
        $this->imageStorage->moveDirectory('manhwa/'.$oldSlug, 'manhwa/'.$newSlug);
    }

    private function refreshStoragePathsAfterSlugChange(Manhwa $manhwa, string $oldSlug, string $newSlug): void
    {
        $oldPrefix = 'manhwa/'.$oldSlug.'/';
        $newPrefix = 'manhwa/'.$newSlug.'/';

        if ($manhwa->cover_image && Str::startsWith($manhwa->cover_image, $oldPrefix)) {
            $manhwa->cover_image = str_replace($oldPrefix, $newPrefix, $manhwa->cover_image);
        }

        foreach ($manhwa->chapters()->with('pages')->get() as $chapter) {
            foreach ($chapter->pages as $page) {
                $page->update([
                    'path' => str_replace($oldPrefix, $newPrefix, $page->path),
                ]);
            }
        }
    }
}
