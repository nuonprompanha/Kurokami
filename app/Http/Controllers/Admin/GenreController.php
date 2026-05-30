<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GenreController extends Controller
{
    public function index(Request $request)
    {
        $genres = Genre::query()
            ->withCount('manhwas')
            ->when($request->query('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.genres.index', [
            'genres' => $genres,
            'search' => $request->query('search'),
        ]);
    }

    public function create()
    {
        return view('admin.genres.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:genres,name'],
        ]);

        Genre::query()->create([
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug($validated['name']),
        ]);

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre created successfully.');
    }

    public function edit(Genre $genre)
    {
        $genre->loadCount('manhwas');

        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('genres', 'name')->ignore($genre->id),
            ],
        ]);

        $genre->update([
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug($validated['name'], ignoreId: $genre->id),
        ]);

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre updated successfully.');
    }

    public function destroy(Genre $genre)
    {
        if ($genre->manhwas()->exists()) {
            return back()->withErrors([
                'genre' => 'Cannot delete a genre that is assigned to manhwa. Remove it from manhwa first.',
            ]);
        }

        $genre->delete();

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre deleted successfully.');
    }

    private function resolveUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'genre';
        $candidate = $base;
        $suffix = 1;

        while (
            Genre::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
