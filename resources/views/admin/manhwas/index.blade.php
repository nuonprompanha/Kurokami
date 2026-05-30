@extends('admin.layouts.app')

@section('title', 'Manhwa')
@section('page-title', 'Manhwa')
@section('page-subtitle', 'Manage all manhwa titles and chapters')

@section('content')
    <div class="admin-panel">
        <div class="admin-panel-header">
            <h2>All Manhwa</h2>
            <a href="{{ route('admin.manhwas.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <i class="fa-solid fa-plus"></i> Add Manhwa
            </a>
        </div>

        <div class="admin-filter-bar">
            <form action="{{ route('admin.manhwas.index') }}" method="GET" class="admin-filter-form">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search title or category..."
                    class="admin-filter-input"
                >
                <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Search</button>
                @if ($search)
                    <a href="{{ route('admin.manhwas.index') }}" class="admin-link">Clear</a>
                @endif
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Chapters</th>
                        <th>Views</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($manhwas as $manhwa)
                        <tr>
                            <td>{{ $manhwas->firstItem() + $loop->index }}</td>
                            <td>
                                <img
                                    src="{{ $manhwa->coverUrl() }}"
                                    alt="{{ $manhwa->title }}"
                                    class="admin-table-thumb"
                                >
                            </td>
                            <td>
                                {{ $manhwa->title }}
                                @if ($manhwa->is_adult_content)
                                    <span class="admin-badge admin-badge-adult">18+</span>
                                @endif
                            </td>
                            <td>{{ $manhwa->category }}</td>
                            <td>
                                @if ($manhwa->badge)
                                    <span class="admin-badge">{{ $manhwa->badge }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $manhwa->chapters_count }}</td>
                            <td>{{ number_format($manhwa->views) }}</td>
                            <td>{{ $manhwa->updated_at->format('M d, Y') }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a
                                        href="{{ route('manhwa.show', $manhwa) }}"
                                        class="admin-action-btn"
                                        title="View on site"
                                        target="_blank"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.manhwas.edit', $manhwa) }}" class="admin-action-btn" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form
                                        action="{{ route('admin.manhwas.destroy', $manhwa) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this manhwa and all chapters?')"
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
                            <td colspan="9" class="admin-table-empty">No manhwa found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($manhwas->hasPages())
            <div class="admin-pagination">
                {{ $manhwas->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
