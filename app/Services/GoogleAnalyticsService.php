<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class GoogleAnalyticsService
{
    public function configured(): bool
    {
        return filled(config('services.google_analytics.measurement_id'))
            && filled(config('services.google_analytics.property_id'))
            && filled(config('services.google_analytics.credentials_base64'));
    }

    public function report(int $days): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Google Analytics is not configured.');
        }

        return Cache::remember("google-analytics.visitors.{$days}", now()->addMinutes(10), function () use ($days) {
            $client = $this->client();
            $property = 'properties/'.config('services.google_analytics.property_id');
            $dateRange = [new DateRange(['start_date' => ($days - 1).'daysAgo', 'end_date' => 'today'])];

            try {
                $summary = $client->runReport(new RunReportRequest([
                    'property' => $property,
                    'date_ranges' => $dateRange,
                    'metrics' => $this->metrics(['activeUsers', 'newUsers', 'sessions', 'screenPageViews']),
                ]));

                $daily = $client->runReport(new RunReportRequest([
                    'property' => $property,
                    'date_ranges' => $dateRange,
                    'dimensions' => $this->dimensions(['date']),
                    'metrics' => $this->metrics(['activeUsers', 'sessions', 'screenPageViews']),
                    'order_bys' => [new OrderBy([
                        'dimension' => new OrderBy\DimensionOrderBy([
                            'dimension_name' => 'date',
                        ]),
                    ])],
                ]));

                $pages = $client->runReport(new RunReportRequest([
                    'property' => $property,
                    'date_ranges' => $dateRange,
                    'dimensions' => $this->dimensions(['pageTitle', 'pagePath']),
                    'metrics' => $this->metrics(['screenPageViews', 'activeUsers']),
                    'order_bys' => [new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
                        'desc' => true,
                    ])],
                    'limit' => 10,
                ]));

                $countries = $client->runReport(new RunReportRequest([
                    'property' => $property,
                    'date_ranges' => $dateRange,
                    'dimensions' => $this->dimensions(['country']),
                    'metrics' => $this->metrics(['activeUsers']),
                    'order_bys' => [new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
                        'desc' => true,
                    ])],
                    'limit' => 10,
                ]));

                $realtime = $client->runRealtimeReport(new RunRealtimeReportRequest([
                    'property' => $property,
                    'metrics' => $this->metrics(['activeUsers']),
                ]));

                return [
                    'summary' => $this->firstMetrics($summary, ['activeUsers', 'newUsers', 'sessions', 'screenPageViews']),
                    'daily' => $this->rows($daily, ['date'], ['activeUsers', 'sessions', 'screenPageViews']),
                    'pages' => $this->rows($pages, ['pageTitle', 'pagePath'], ['screenPageViews', 'activeUsers']),
                    'countries' => $this->rows($countries, ['country'], ['activeUsers']),
                    'realtime' => $this->firstMetrics($realtime, ['activeUsers'])['activeUsers'],
                ];
            } finally {
                $client->close();
            }
        });
    }

    private function client(): BetaAnalyticsDataClient
    {
        $json = base64_decode((string) config('services.google_analytics.credentials_base64'), true);
        $credentials = $json === false ? null : json_decode($json, true);

        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException('GOOGLE_ANALYTICS_CREDENTIALS_BASE64 is not valid service-account JSON.');
        }

        return new BetaAnalyticsDataClient([
            'credentials' => new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/analytics.readonly'],
                $credentials,
            ),
            'transport' => 'rest',
        ]);
    }

    private function dimensions(array $names): array
    {
        return array_map(fn (string $name) => new Dimension(['name' => $name]), $names);
    }

    private function metrics(array $names): array
    {
        return array_map(fn (string $name) => new Metric(['name' => $name]), $names);
    }

    private function firstMetrics(object $response, array $metricNames): array
    {
        $row = $response->getRows()[0] ?? null;
        $values = $row?->getMetricValues() ?? [];

        return collect($metricNames)
            ->mapWithKeys(fn (string $name, int $index) => [$name => (int) ($values[$index]?->getValue() ?? 0)])
            ->all();
    }

    private function rows(object $response, array $dimensionNames, array $metricNames): array
    {
        $rows = [];

        foreach ($response->getRows() as $row) {
            $item = [];

            foreach ($dimensionNames as $index => $name) {
                $item[$name] = $row->getDimensionValues()[$index]?->getValue() ?? '';
            }

            foreach ($metricNames as $index => $name) {
                $item[$name] = (int) ($row->getMetricValues()[$index]?->getValue() ?? 0);
            }

            $rows[] = $item;
        }

        return $rows;
    }
}
