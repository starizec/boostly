<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Analytics;
use App\Services\AnalyticsDashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $data = app(AnalyticsDashboardService::class)->getDashboardData($user);

        $labels = [
            Analytics::EVENT_LOADED => 'Pojavljivanja',
            Analytics::EVENT_OPENED => 'Otvaranja',
            Analytics::EVENT_ACTION_CLICKED => 'Klikovi',
            Analytics::EVENT_CHAT_CLICKED => 'Chat klikovi',
            Analytics::EVENT_CHAT_STARTED => 'Chat startovi',
            Analytics::EVENT_CONVERSION => 'Konverzije',
        ];

        $stats = [];

        foreach (Analytics::getEventTypes() as $eventType) {
            $eventStat = $data['eventStats'][$eventType] ?? ['all_time' => 0, 'mom_change' => null];
            $momChange = $eventStat['mom_change'];
            $isPositive = is_null($momChange) ? true : $momChange >= 0;
            $chartData = $data['chartSeriesByEvent'][$eventType]['data'] ?? [];

            $description = is_null($momChange)
                ? 'Nema podataka za prethodni mjesec'
                : sprintf('%s%s%% u odnosu na prošli mjesec', $isPositive ? '+' : '', number_format($momChange, 1));

            $stats[] = Stat::make($labels[$eventType] ?? $eventType, number_format($eventStat['all_time']))
                ->description($description)
                ->descriptionIcon($isPositive ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData)
                ->color($isPositive ? 'success' : 'danger');
        }

        return $stats;
    }
}
