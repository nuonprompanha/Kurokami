@extends('Layouts.Home_Layouts')

@section('title', 'Contact - Kurokami')

@section('content')
    <section class="static-page-hero">
        <div class="container">
            <h1 class="static-page-title">Contact Us</h1>
            <p class="static-page-lead">
                Have a question, suggestion, or issue? Send us a message and we will respond as soon as we can.
            </p>
        </div>
    </section>

    <section class="static-page-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="static-page-card">
                        @if (session('success'))
                            <div class="static-page-alert static-page-alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <h2 class="static-page-heading">Send a Message</h2>

                        <form action="{{ route('contact.send') }}" method="POST" class="auth-form">
                            @csrf

                            <div class="auth-form-group">
                                <label for="name">Name</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    autocomplete="name"
                                >
                                @error('name')
                                    <span class="static-page-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="auth-form-group">
                                <label for="email">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', auth()->user()?->email) }}"
                                    required
                                    autocomplete="email"
                                >
                                @error('email')
                                    <span class="static-page-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="auth-form-group">
                                <label for="subject">Subject</label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                >
                                @error('subject')
                                    <span class="static-page-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="auth-form-group">
                                <label for="message">Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="6"
                                    required
                                >{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="static-page-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="auth-btn">Send Message</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="static-page-card static-page-card-accent h-100">
                        <h2 class="static-page-heading">Get in Touch</h2>
                        <p>
                            For general inquiries, feedback about the site, or reporting a problem with a chapter,
                            use the form and include as much detail as you can.
                        </p>

                        <ul class="static-page-contact-list">
                            <li>
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>Email</strong>
                                    <span>{{ config('mail.from.address') }}</span>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Response Time</strong>
                                    <span>Usually within 1–2 business days</span>
                                </div>
                            </li>
                        </ul>

                        <h3 class="static-page-subheading">Follow Us</h3>
                        <div class="static-page-social">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                            <a href="#" aria-label="Discord"><i class="fab fa-discord"></i></a>
                            <a href="#" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
