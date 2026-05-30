@csrf

<div class="admin-form-group">
    <label for="name">Genre name</label>
    <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $genre->name ?? '') }}"
        placeholder="e.g. Action, Isekai"
        required
    >
    @if (isset($genre))
        <p class="admin-form-hint">Slug: <code>{{ $genre->slug }}</code> (auto-updates from name)</p>
    @else
        <p class="admin-form-hint">URL slug is generated automatically from the name.</p>
    @endif
    @error('name')
        <span class="admin-field-error">{{ $message }}</span>
    @enderror
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-primary">
        {{ isset($genre) ? 'Update Genre' : 'Create Genre' }}
    </button>
    <a href="{{ route('admin.genres.index') }}" class="admin-btn admin-btn-outline">Cancel</a>
</div>
