@extends('admin.layouts.app')

@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Manage your account and security settings')

@section('content')
    <div class="admin-profile-header">
        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="admin-profile-avatar">
        <div>
            <h2 class="admin-profile-name">{{ $user->name }}</h2>
            <p class="admin-profile-email">{{ $user->email }}</p>
            <span class="admin-role-badge admin-role-badge-{{ $user->role }}">{{ $user->roleLabel() }}</span>
            @if ($user->bio)
                <p class="admin-profile-bio">{{ $user->bio }}</p>
            @endif
            <p class="admin-profile-meta">Admin since {{ $user->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <div class="admin-panel admin-profile-panel">
        <nav class="admin-profile-tabs" aria-label="Profile sections">
            @if ($user->hasTwoFactorEnabled())
                <a href="{{ route('admin.profile.show', ['tab' => 'profile']) }}" @class(['admin-profile-tab', 'active' => $tab === 'profile'])>
                    <i class="fa-solid fa-user-pen"></i>
                    Edit Profile
                </a>
                <a href="{{ route('admin.profile.show', ['tab' => 'password']) }}" @class(['admin-profile-tab', 'active' => $tab === 'password'])>
                    <i class="fa-solid fa-key"></i>
                    Password
                </a>
            @endif
            <a href="{{ route('admin.profile.show', ['tab' => 'security']) }}" @class(['admin-profile-tab', 'active' => $tab === 'security'])>
                <i class="fa-solid fa-shield-halved"></i>
                Security
            </a>
        </nav>

        <div class="admin-profile-body">
            @if ($tab === 'profile')
                @include('admin.profile._tab-profile')
            @elseif ($tab === 'password')
                @include('admin.profile._tab-password')
            @else
                @include('admin.profile._two-factor')
            @endif
        </div>
    </div>
@endsection
