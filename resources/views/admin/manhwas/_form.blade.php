@csrf

<div class="row g-3">
    <div class="col-12">
        <div class="admin-form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title', $manhwa->title ?? '') }}" required>
            <p class="admin-form-hint">
                URL slug:
                <code id="slug-preview">{{ isset($manhwa) ? $manhwa->slug : 'auto-from-title' }}</code>
                <span class="admin-label-hint">(auto-generated from title)</span>
            </p>
            @error('title')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-form-group">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(old('category', $manhwa->category ?? '') === $category)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-form-group">
            <label for="cover_image">
                Cover Image (optional{{ isset($manhwa) ? ' — leave empty to keep current' : '' }})
            </label>
            <input
                type="file"
                id="cover_image"
                name="cover_image"
                accept="image/jpeg,image/png,image/webp"
            >
            @error('cover_image')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
            @if (isset($manhwa))
                <div class="admin-form-preview">
                    <img src="{{ $manhwa->coverUrl() }}" alt="{{ $manhwa->title }}" class="admin-form-preview-img">
                </div>
            @endif
        </div>
    </div>

    <div class="col-12">
        <h3 class="admin-form-section-title">Series details</h3>
        @include('admin.manhwas._details-fields', [
            'manhwa' => $manhwa ?? null,
            'badges' => $badges,
            'statuses' => $statuses,
            'seriesTypes' => $seriesTypes,
            'genres' => $genres,
        ])
    </div>

    <div class="col-12">
        <div class="admin-form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Optional synopsis...">{{ old('description', $manhwa->description ?? '') }}</textarea>
            @error('description')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="admin-form-group">
            <label for="chapters_zip">
                Chapters ZIP {{ isset($manhwa) ? '(optional — replaces pages for chapters in zip)' : '' }}
            </label>
            <input
                type="file"
                id="chapters_zip"
                name="chapters_zip"
                accept=".zip,application/zip"
                {{ isset($manhwa) ? '' : 'required' }}
            >
            @error('chapters_zip')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
            <p class="admin-form-hint">
                Zip structure: one folder per chapter (e.g. <code>1/</code>, <code>chapter-2/</code>, <code>ch03/</code>)
                containing ordered image files (jpg, png, webp).
            </p>
            @if (isset($manhwa) && $manhwa->chapters->isNotEmpty())
                <p class="admin-form-hint">Current: {{ $manhwa->chapters->count() }} chapter(s) with pages uploaded.</p>
            @endif
        </div>
    </div>
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-primary">
        {{ isset($manhwa) ? 'Update Manhwa' : 'Create Manhwa' }}
    </button>
    <a href="{{ route('admin.manhwas.index') }}" class="admin-btn admin-btn-outline">Cancel</a>
</div>
