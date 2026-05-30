@extends('admin.layouts.app')

@section('title', 'Genres')
@section('page-title', 'Genres')
@section('page-subtitle', 'Manage genres for manhwa')

@section('content')
    <div class="admin-panel">
        <div class="admin-panel-header">
            <h2>All Genres</h2>
            <a href="{{ route('admin.genres.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <i class="fa-solid fa-plus"></i> Add Genre
            </a>
        </div>

        <div class="admin-filter-bar">
            <form action="{{ route('admin.genres.index') }}" method="GET" class="admin-filter-form">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search genre name..."
                    class="admin-filter-input"
                >
                <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Search</button>
                @if ($search)
                    <a href="{{ route('admin.genres.index') }}" class="admin-link">Clear</a>
                @endif
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Manhwa</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($genres as $genre)
                        <tr>
                            <td>{{ $genres->firstItem() + $loop->index }}</td>
                            <td>{{ $genre->name }}</td>
                            <td><code>{{ $genre->slug }}</code></td>
                            <td>{{ $genre->manhwas_count }}</td>
                            <td>{{ $genre->updated_at->format('M d, Y') }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.genres.edit', $genre) }}" class="admin-action-btn" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form
                                        action="{{ route('admin.genres.destroy', $genre) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this genre?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-action-btn admin-action-btn-danger" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-table-empty">No genres found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($genres->hasPages())
            <div class="admin-pagination">
                {{ $genres->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
