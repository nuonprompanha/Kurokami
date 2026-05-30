@extends('Layouts.Home_Layouts')

@section('title', 'Login')

@section('content')
    <section class="auth-section">
        <div class="container">
            <div class="auth-card">
                <div class="auth-card-header">
                    <h1>Login</h1>
                    <p>Sign in with your Google account to read manhwa</p>
                </div>

                @if ($errors->any())
                    <div class="auth-alert auth-alert-error">{{ $errors->first() }}</div>
                @endif

                <a href="{{ route('auth.google') }}" class="google-login-btn">
                    <i class="fab fa-google"></i>
                    Continue with Google
                </a>
            </div>
        </div>
    </section>
@endsection
