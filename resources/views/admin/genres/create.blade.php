@extends('admin.layouts.app')

@section('title', 'Add Genre')
@section('page-title', 'Add Genre')
@section('page-subtitle', 'Create a new genre for manhwa')

@section('content')
    <div class="admin-panel admin-form-panel">
        <div class="admin-panel-header">
            <h2>New Genre</h2>
        </div>
        <div class="admin-form-panel-body">
            <form action="{{ route('admin.genres.store') }}" method="POST" class="admin-profile-form">
                @include('admin.genres._form')
            </form>
        </div>
    </div>
@endsection
