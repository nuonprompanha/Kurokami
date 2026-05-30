@extends('admin.layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')
@section('page-subtitle', 'Create a new user account')

@section('content')
    <div class="admin-panel admin-form-panel">
        <div class="admin-panel-header">
            <h2>New User Account</h2>
        </div>
        <div class="admin-form-panel-body">
            <form action="{{ route('admin.users.store') }}" method="POST" class="admin-profile-form">
                @include('admin.users._form', ['roles' => $roles])
            </form>
        </div>
    </div>
@endsection
