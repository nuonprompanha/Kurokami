@extends('Layouts.Home_Layouts')

@section('title', 'Kurokami')

@section('content')
    <section class="manhwa-section">
        <div class="container">
            <div class="manhwa-section-header">
                <h2 class="manhwa-section-title">
                    @if ($activeGenre)
                        {{ $activeGenre->name }} Manhwa
                    @else
                        @switch($tab)
                            @case('popular')
                                Popular Manhwa
                                @break
                            @case('last-update')
                                Last Update
                                @break
                            @default
                                New Manhwa
                        @endswitch
                    @endif
                </h2>
                <nav class="manhwa-tabs" aria-label="Manhwa filters">
                    @php
                        $homeQuery = $activeGenre ? ['genre' => $activeGenre->slug] : [];
                    @endphp
                    <a
                        href="{{ route('home', array_merge($homeQuery, ['tab' => 'new'])) }}"
                        @class(['manhwa-tab', 'active' => $tab === 'new'])
                    >New</a>
                    <a
                        href="{{ route('home', array_merge($homeQuery, ['tab' => 'popular'])) }}"
                        @class(['manhwa-tab', 'active' => $tab === 'popular'])
                    >Popular</a>
                    <a
                        href="{{ route('home', array_merge($homeQuery, ['tab' => 'last-update'])) }}"
                        @class(['manhwa-tab', 'active' => $tab === 'last-update'])
                    >Last Update</a>
                </nav>
            </div>

            @include('Home-Pages.partials.manhwa-grid', ['manhwas' => $manhwas])
        </div>
    </section>
@endsection
