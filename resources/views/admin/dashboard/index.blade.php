@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your manhwa site')

@section('content')
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div>
                <p class="admin-stat-label">Total Manhwa</p>
                <p class="admin-stat-value">{{ number_format($stats['manhwas']) }}</p>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fa-solid fa-list-ol"></i>
            </div>
            <div>
                <p class="admin-stat-label">Total Chapters</p>
                <p class="admin-stat-value">{{ number_format($stats['chapters']) }}</p>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fa-regular fa-eye"></i>
            </div>
            <div>
                <p class="admin-stat-label">Total Views</p>
                <p class="admin-stat-value">{{ number_format($stats['views']) }}</p>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="admin-stat-label">Total Users</p>
                <p class="admin-stat-value">{{ number_format($stats['users']) }}</p>
            </div>
        </div>
    </div>
@endsection
