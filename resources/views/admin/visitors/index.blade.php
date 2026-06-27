@extends('admin.layouts.app')

@section('title', 'Visitor Analytics')
@section('page-title', 'Visitor Analytics')
@section('page-subtitle', 'Google Analytics 4 traffic overview')

@section('content')
    <div class="analytics-toolbar">
        <div>
            <span class="analytics-live-dot"></span>
            <strong>{{ number_format($report['realtime'] ?? 0) }}</strong>
            active visitor{{ ($report['realtime'] ?? 0) === 1 ? '' : 's' }} in the last 30 minutes
        </div>
        <form method="GET" action="{{ route('admin.visitors.index') }}">
            <label for="analytics-days">Date range</label>
            <select id="analytics-days" name="days" onchange="this.form.submit()">
                @foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $value => $label)
                    <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if (! config('services.google_analytics.measurement_id') || ! config('services.google_analytics.property_id') || ! config('services.google_analytics.credentials_base64'))
        <div class="analytics-setup">
            <i class="fa-brands fa-google"></i>
            <div>
                <h2>Connect Google Analytics</h2>
                <p>Add the three Google Analytics variables in Laravel Cloud, redeploy, and this page will begin showing visitor reports.</p>
                <code>GOOGLE_ANALYTICS_MEASUREMENT_ID</code>
                <code>GOOGLE_ANALYTICS_PROPERTY_ID</code>
                <code>GOOGLE_ANALYTICS_CREDENTIALS_BASE64</code>
            </div>
        </div>
    @elseif ($analyticsError)
        <div class="admin-alert admin-alert-error">{{ $analyticsError }}</div>
    @else
        <div class="admin-stats-grid analytics-stats">
            @foreach ([
                ['Active users', 'activeUsers', 'fa-user-group'],
                ['New users', 'newUsers', 'fa-user-plus'],
                ['Sessions', 'sessions', 'fa-clock'],
                ['Page views', 'screenPageViews', 'fa-eye'],
            ] as [$label, $metric, $icon])
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid {{ $icon }}"></i></div>
                    <div>
                        <p class="admin-stat-label">{{ $label }}</p>
                        <p class="admin-stat-value">{{ number_format($report['summary'][$metric]) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="analytics-grid">
            <section class="admin-panel analytics-panel analytics-trend">
                <div class="admin-panel-header">
                    <h2>Daily visitors</h2>
                </div>
                @php($maxVisitors = max(1, collect($report['daily'])->max('activeUsers')))
                <div class="analytics-bars" aria-label="Daily active visitors">
                    @forelse ($report['daily'] as $day)
                        <div class="analytics-bar-column" title="{{ \Carbon\Carbon::createFromFormat('Ymd', $day['date'])->format('M j') }}: {{ number_format($day['activeUsers']) }} visitors">
                            <span class="analytics-bar-value">{{ number_format($day['activeUsers']) }}</span>
                            <span class="analytics-bar" style="height: {{ max(3, ($day['activeUsers'] / $maxVisitors) * 100) }}%"></span>
                            <span class="analytics-bar-label">{{ \Carbon\Carbon::createFromFormat('Ymd', $day['date'])->format($days > 30 ? 'M' : 'j') }}</span>
                        </div>
                    @empty
                        <p class="analytics-empty">No visitor data is available for this period.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-panel analytics-panel">
                <div class="admin-panel-header">
                    <h2>Top countries</h2>
                </div>
                <div class="analytics-ranking">
                    @forelse ($report['countries'] as $country)
                        <div>
                            <span>{{ $country['country'] ?: 'Unknown' }}</span>
                            <strong>{{ number_format($country['activeUsers']) }}</strong>
                        </div>
                    @empty
                        <p class="analytics-empty">No country data available.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="admin-panel analytics-panel">
            <div class="admin-panel-header">
                <h2>Most visited pages</h2>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Path</th>
                            <th>Visitors</th>
                            <th>Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['pages'] as $page)
                            <tr>
                                <td>{{ $page['pageTitle'] ?: 'Untitled page' }}</td>
                                <td><code>{{ $page['pagePath'] }}</code></td>
                                <td>{{ number_format($page['activeUsers']) }}</td>
                                <td>{{ number_format($page['screenPageViews']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="admin-table-empty">No page data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
