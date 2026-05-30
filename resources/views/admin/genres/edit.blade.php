@extends('admin.layouts.app')

@section('title', 'Edit Genre')
@section('page-title', 'Edit Genre')
@section('page-subtitle', $genre->name)

@section('content')
    <div class="admin-panel admin-form-panel">
        <div class="admin-panel-header">
            <h2>Edit: {{ $genre->name }}</h2>
            <span class="admin-count">{{ $genre->manhwas_count }} manhwa assigned</span>
        </div>
        <div class="admin-form-panel-body">
            <form action="{{ route('admin.genres.update', $genre) }}" method="POST" class="admin-profile-form">
                @method('PUT')
                @include('admin.genres._form', ['genre' => $genre])
            </form>
        </div>
    </div>
@endsection
