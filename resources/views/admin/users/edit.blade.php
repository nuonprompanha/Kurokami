@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', 'Update account for '.$user->name)

@section('content')
    <div class="admin-panel admin-form-panel">
        <div class="admin-panel-header">
            <h2>Edit User Account</h2>
        </div>
        <div class="admin-form-panel-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-profile-form">
                @csrf
                @method('PUT')
                @include('admin.users._form', ['user' => $user, 'roles' => $roles])
            </form>
        </div>
    </div>
@endsection
