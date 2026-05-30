@extends('admin.layouts.app')

@section('title', 'Add Manhwa')
@section('page-title', 'Add Manhwa')
@section('page-subtitle', 'Add series details and chapter images via ZIP')

@section('content')
    <div class="admin-panel admin-form-panel">
        <div class="admin-panel-header">
            <h2>New Manhwa</h2>
        </div>
        <div class="admin-form-panel-body">
            <form action="{{ route('admin.manhwas.store') }}" method="POST" enctype="multipart/form-data" class="admin-profile-form manhwa-create-form">
                @include('admin.manhwas._form', [
                    'categories' => $categories,
                    'badges' => $badges,
                    'statuses' => $statuses,
                    'seriesTypes' => $seriesTypes,
                    'genres' => $genres,
                ])
            </form>
        </div>
    </div>

    <script>
        (function () {
            const titleInput = document.getElementById('title');
            const slugPreview = document.getElementById('slug-preview');

            if (! titleInput || ! slugPreview) {
                return;
            }

            function slugify(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '') || 'manhwa';
            }

            titleInput.addEventListener('input', function () {
                slugPreview.textContent = slugify(this.value);
            });
        })();
    </script>
@endsection
