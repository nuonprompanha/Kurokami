@extends('admin.layouts.app')

@section('title', 'Edit Manhwa')
@section('page-title', 'Edit Manhwa')
@section('page-subtitle', 'Update details, cover, or upload new chapters')

@section('content')
    <div class="manhwa-edit">
        <div class="manhwa-edit-topbar">
            <a href="{{ route('admin.manhwas.index') }}" class="manhwa-edit-back">
                <i class="fa-solid fa-arrow-left"></i> All Manhwa
            </a>
            <div class="manhwa-edit-topbar-actions">
                <a href="{{ route('manhwa.show', $manhwa) }}" class="admin-btn admin-btn-outline admin-btn-sm" target="_blank">
                    <i class="fa-solid fa-eye"></i> View on site
                </a>
            </div>
        </div>

        <form
            action="{{ route('admin.manhwas.update', $manhwa) }}"
            method="POST"
            enctype="multipart/form-data"
            class="manhwa-edit-form"
        >
            @csrf
            @method('PUT')

            <div class="manhwa-edit-layout">
                <aside class="manhwa-edit-sidebar">
                    <div class="manhwa-edit-cover-card">
                        <img
                            src="{{ $manhwa->coverUrl() }}"
                            alt="{{ $manhwa->title }}"
                            class="manhwa-edit-cover-img"
                            id="manhwa-cover-preview"
                        >
                        @if ($manhwa->badge)
                            <span class="manhwa-edit-cover-badge">{{ $manhwa->badge }}</span>
                        @endif
                        @if ($manhwa->status)
                            <span class="manhwa-edit-cover-status">{{ $manhwa->status }}</span>
                        @endif
                    </div>

                    <ul class="manhwa-edit-stats">
                        <li>
                            <span class="manhwa-edit-stat-label">Chapters</span>
                            <strong>{{ $manhwa->chapters->count() }}</strong>
                        </li>
                        <li>
                            <span class="manhwa-edit-stat-label">Views</span>
                            <strong>{{ number_format($manhwa->views) }}</strong>
                        </li>
                        <li>
                            <span class="manhwa-edit-stat-label">Last updated</span>
                            <strong>{{ $manhwa->updated_at->format('M d, Y') }}</strong>
                        </li>
                        <li>
                            <span class="manhwa-edit-stat-label">URL slug</span>
                            <code class="manhwa-edit-slug" id="slug-preview-sidebar">{{ $manhwa->slug }}</code>
                        </li>
                    </ul>
                </aside>

                <div class="manhwa-edit-main">
                    <section class="manhwa-edit-card">
                        <header class="manhwa-edit-card-header">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>
                                <h2>Basic information</h2>
                                <p>Title and primary category. URL slug updates automatically from the title.</p>
                            </div>
                        </header>
                        <div class="manhwa-edit-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="admin-form-group">
                                        <label for="title">Title</label>
                                        <input type="text" id="title" name="title" value="{{ old('title', $manhwa->title) }}" required>
                                        <p class="admin-form-hint">
                                            URL slug:
                                            <code id="slug-preview">{{ $manhwa->slug }}</code>
                                            <span class="admin-label-hint">(auto-generated from title)</span>
                                        </p>
                                        @error('title')
                                            <span class="admin-field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label for="category">Category</label>
                                        <select id="category" name="category" required>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category }}" @selected(old('category', $manhwa->category) === $category)>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <span class="admin-field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="manhwa-edit-card">
                        <header class="manhwa-edit-card-header">
                            <i class="fa-solid fa-list-ul"></i>
                            <div>
                                <h2>Series details</h2>
                                <p>Alternative titles, author, artist, status, type, genres, and tags.</p>
                            </div>
                        </header>
                        <div class="manhwa-edit-card-body">
                            @include('admin.manhwas._details-fields', [
                                'manhwa' => $manhwa,
                                'badges' => $badges,
                                'statuses' => $statuses,
                                'seriesTypes' => $seriesTypes,
                                'genres' => $genres,
                            ])
                        </div>
                    </section>

                    <section class="manhwa-edit-card">
                        <header class="manhwa-edit-card-header">
                            <i class="fa-solid fa-align-left"></i>
                            <div>
                                <h2>Description</h2>
                                <p>Short synopsis shown on the manhwa detail page.</p>
                            </div>
                        </header>
                        <div class="manhwa-edit-card-body">
                            <div class="admin-form-group">
                                <label for="description" class="visually-hidden">Description</label>
                                <textarea id="description" name="description" rows="5" placeholder="Write a short description...">{{ old('description', $manhwa->description) }}</textarea>
                                @error('description')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="manhwa-edit-card">
                        <header class="manhwa-edit-card-header">
                            <i class="fa-solid fa-image"></i>
                            <div>
                                <h2>Cover image</h2>
                                <p>Upload a new file only if you want to replace the current cover.</p>
                            </div>
                        </header>
                        <div class="manhwa-edit-card-body">
                            <label class="manhwa-edit-upload" for="cover_image">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span class="manhwa-edit-upload-title">Choose new cover image</span>
                                <span class="manhwa-edit-upload-hint">JPG, PNG or WebP · max 5 MB</span>
                                <span class="manhwa-edit-upload-name" id="cover-file-name">No file selected</span>
                            </label>
                            <input
                                type="file"
                                id="cover_image"
                                name="cover_image"
                                accept="image/jpeg,image/png,image/webp"
                                class="manhwa-edit-upload-input"
                            >
                            @error('cover_image')
                                <span class="admin-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </section>

                    <section class="manhwa-edit-card">
                        <header class="manhwa-edit-card-header">
                            <i class="fa-solid fa-book-open"></i>
                            <div>
                                <h2>Chapters</h2>
                                <p>Uploaded chapters on the site. Upload a ZIP to add or replace chapter images.</p>
                            </div>
                        </header>
                        <div class="manhwa-edit-card-body">
                            @if ($manhwa->chapters->isNotEmpty())
                                <div class="manhwa-edit-chapter-grid">
                                    @foreach ($manhwa->chapters as $chapter)
                                        @php
                                            $firstPage = $chapter->pages->first();
                                        @endphp
                                        <article class="manhwa-edit-chapter-card">
                                            <div class="manhwa-edit-chapter-thumb">
                                                @if ($firstPage)
                                                    <img src="{{ $firstPage->url() }}" alt="Ch. {{ $chapter->chapter_number }}" loading="lazy">
                                                @else
                                                    <span class="manhwa-edit-chapter-no-thumb"><i class="fa-solid fa-image"></i></span>
                                                @endif
                                            </div>
                                            <div class="manhwa-edit-chapter-info">
                                                <h3>{{ $chapter->displayTitle() }}</h3>
                                                <p>{{ $chapter->pages_count }} page(s)</p>
                                            </div>
                                            <div class="manhwa-edit-chapter-actions">
                                                <a
                                                    href="{{ route('chapter.show', ['manhwa' => $manhwa->slug, 'chapterNumber' => $chapter->chapter_number]) }}"
                                                    class="manhwa-edit-chapter-link"
                                                    target="_blank"
                                                    title="Preview chapter"
                                                >
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </a>
                                                <button
                                                    type="submit"
                                                    form="delete-chapter-{{ $chapter->id }}"
                                                    class="manhwa-edit-chapter-delete"
                                                    title="Delete chapter"
                                                    onclick="return confirm('Delete {{ $chapter->displayTitle() }} and all its pages?')"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <p class="manhwa-edit-empty-chapters">
                                    <i class="fa-solid fa-folder-open"></i>
                                    No chapters yet. Upload a ZIP below to add chapters.
                                </p>
                            @endif

                            <div class="manhwa-edit-zip-block">
                                <label class="manhwa-edit-upload manhwa-edit-upload-zip" for="chapters_zip">
                                    <i class="fa-solid fa-file-zipper"></i>
                                    <span class="manhwa-edit-upload-title">Upload chapters ZIP</span>
                                    <span class="manhwa-edit-upload-hint">Folders: <code>1/</code>, <code>chapter-2/</code> · images inside each folder</span>
                                    <span class="manhwa-edit-upload-name" id="zip-file-name">No file selected</span>
                                </label>
                                <input
                                    type="file"
                                    id="chapters_zip"
                                    name="chapters_zip"
                                    accept=".zip,application/zip"
                                    class="manhwa-edit-upload-input"
                                >
                                @error('chapters_zip')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                                @if ($manhwa->chapters->isNotEmpty())
                                    <p class="admin-form-hint manhwa-edit-zip-warning">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Uploading a new ZIP will replace pages for chapters included in the archive.
                                    </p>
                                @endif
                                <p class="admin-form-hint">
                                    Large ZIP files are processed in the background after upload — refresh this page in a few minutes to see new chapters.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="manhwa-edit-footer">
                <button type="submit" class="admin-btn admin-btn-primary" id="manhwa-submit-btn">
                    <i class="fa-solid fa-floppy-disk"></i> Save changes
                </button>
                <a href="{{ route('admin.manhwas.index') }}" class="admin-btn admin-btn-outline">Cancel</a>
            </div>
        </form>

        @foreach ($manhwa->chapters as $chapter)
            <form
                id="delete-chapter-{{ $chapter->id }}"
                action="{{ route('admin.manhwas.chapters.destroy', ['manhwa' => $manhwa, 'chapterNumber' => $chapter->chapter_number]) }}"
                method="POST"
                hidden
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>

    <script>
        (function () {
            const titleInput = document.getElementById('title');
            const slugPreview = document.getElementById('slug-preview');
            const coverInput = document.getElementById('cover_image');
            const coverPreview = document.getElementById('manhwa-cover-preview');
            const coverFileName = document.getElementById('cover-file-name');
            const zipInput = document.getElementById('chapters_zip');
            const zipFileName = document.getElementById('zip-file-name');

            function slugify(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '') || 'manhwa';
            }

            const slugPreviewSidebar = document.getElementById('slug-preview-sidebar');

            if (titleInput && slugPreview) {
                titleInput.addEventListener('input', function () {
                    const slug = slugify(this.value);
                    slugPreview.textContent = slug;
                    if (slugPreviewSidebar) {
                        slugPreviewSidebar.textContent = slug;
                    }
                });
            }

            if (coverInput) {
                coverInput.addEventListener('change', function () {
                    const file = this.files[0];
                    coverFileName.textContent = file ? file.name : 'No file selected';

                    if (file && coverPreview) {
                        coverPreview.src = URL.createObjectURL(file);
                    }
                });
            }

            if (zipInput) {
                zipInput.addEventListener('change', function () {
                    const file = this.files[0];
                    zipFileName.textContent = file ? file.name : 'No file selected';
                });
            }

            const form = document.querySelector('.manhwa-edit-form');
            const submitBtn = document.getElementById('manhwa-submit-btn');

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
                });
            }
        })();
    </script>
@endsection
