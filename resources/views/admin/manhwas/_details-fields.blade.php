<div class="row g-3">
    <div class="col-12">
        <div class="admin-form-group">
            <label for="alternative_titles">Alternative titles</label>
            <input
                type="text"
                id="alternative_titles"
                name="alternative_titles"
                value="{{ old('alternative_titles', $manhwa->alternative_titles ?? '') }}"
                placeholder="e.g. Only I Level Up, Na Honjaman Level Up"
            >
            @error('alternative_titles')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-form-group">
            <label for="author">Author</label>
            <input type="text" id="author" name="author" value="{{ old('author', $manhwa->author ?? '') }}" placeholder="Writer name">
            @error('author')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-form-group">
            <label for="artist">Artist</label>
            <input type="text" id="artist" name="artist" value="{{ old('artist', $manhwa->artist ?? '') }}" placeholder="Illustrator name">
            @error('artist')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Select status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $manhwa->status ?? '') === $status)>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-form-group">
            <label for="series_type">Type</label>
            <select id="series_type" name="series_type">
                <option value="">Select type</option>
                @foreach ($seriesTypes as $seriesType)
                    <option value="{{ $seriesType }}" @selected(old('series_type', $manhwa->series_type ?? '') === $seriesType)>
                        {{ $seriesType }}
                    </option>
                @endforeach
            </select>
            @error('series_type')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-form-group">
            <label for="badge">Homepage badge</label>
            <select id="badge" name="badge">
                <option value="">None</option>
                @foreach ($badges as $badge)
                    <option value="{{ $badge }}" @selected(old('badge', $manhwa->badge ?? '') === $badge)>
                        {{ $badge }}
                    </option>
                @endforeach
            </select>
            @error('badge')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="admin-form-group">
            <label>Genres</label>
            @php
                $selectedGenreIds = old(
                    'genre_ids',
                    isset($manhwa) ? $manhwa->genres->pluck('id')->all() : []
                );
            @endphp
            @if ($genres->isEmpty())
                <p class="admin-form-hint">
                    No genres yet.
                    <a href="{{ route('admin.genres.create') }}" class="admin-link">Add a genre</a>
                </p>
            @else
                <div class="manhwa-genre-checkboxes">
                    @foreach ($genres as $genre)
                        <label class="manhwa-genre-check">
                            <input
                                type="checkbox"
                                name="genre_ids[]"
                                value="{{ $genre->id }}"
                                @checked(in_array($genre->id, $selectedGenreIds))
                            >
                            <span>{{ $genre->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
            <p class="admin-form-hint">
                <a href="{{ route('admin.genres.index') }}" class="admin-link">Manage genres</a>
            </p>
            @error('genre_ids')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
            @error('genre_ids.*')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="admin-form-group">
            <label class="manhwa-adult-check-label">
                <input
                    type="checkbox"
                    id="is_adult_content"
                    name="is_adult_content"
                    value="1"
                    @checked(old('is_adult_content', $manhwa->is_adult_content ?? false))
                >
                <span>Adult content (18+)</span>
            </label>
            <p class="admin-form-hint">Readers must confirm they are 18+ before opening this series on the site.</p>
            @error('is_adult_content')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="admin-form-group">
            <label for="tags">Tags</label>
            <input
                type="text"
                id="tags"
                name="tags"
                value="{{ old('tags', isset($manhwa) && $manhwa->tags ? implode(', ', $manhwa->tags) : '') }}"
                placeholder="e.g. revenge, dungeon, regression"
            >
            <p class="admin-form-hint">Comma-separated tags for search and filters.</p>
            @error('tags')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
