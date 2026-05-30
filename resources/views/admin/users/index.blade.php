@extends('admin.layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-subtitle', 'Manage user accounts and departments')

@section('content')
    <div class="admin-panel">
        <div class="admin-panel-header">
            <h2>All Users</h2>
            <a href="{{ route('admin.users.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <i class="fa-solid fa-plus"></i> Add User
            </a>
        </div>

        <div class="admin-filter-bar">
            <form action="{{ route('admin.users.index') }}" method="GET" class="admin-filter-form">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search name or email..."
                    class="admin-filter-input"
                >
                <select name="role" class="admin-filter-select">
                    <option value="">All Departments</option>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Filter</button>
                @if ($search || $currentRole)
                    <a href="{{ route('admin.users.index') }}" class="admin-link">Clear</a>
                @endif
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="admin-user-cell">
                                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="admin-table-avatar">
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="admin-role-badge admin-role-badge-{{ $user->role }}">
                                    {{ $user->roleLabel() }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="admin-action-btn" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-btn admin-action-btn-danger" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-table-empty">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="admin-pagination">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
