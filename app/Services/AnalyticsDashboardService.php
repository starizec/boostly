<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Analytics;
use App\Models\User;
use App\Models\Widget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardService
{
    /**
     * @return array{
     *     widgets: Collection<int, Widget>,
     *     analyticsCounts: array<int, array<string, int>>,
     *     eventTypes: array<int, string>,
     *     chartCategories: array<int, string>,
     *     chartSeriesByEvent: array<string, array{name: string, data: array<int, int>}>,
     *     eventStats: array<string, array{all_time: int, current_month: int, previous_month: int, mom_change: float|null}>
     * }
     */
    public function getDashboardData(User $user): array
    {
        $widgets = $this->getAccessibleWidgets($user);
        $widgetIds = $widgets->pluck('id');
        $eventTypes = Analytics::getEventTypes();
        $keyEvents = $eventTypes;

        $analyticsCounts = Analytics::query()
            ->selectRaw('widget_id, event, COUNT(*) as total')
            ->whereIn('widget_id', $widgetIds)
            ->groupBy('widget_id', 'event')
            ->get()
            ->groupBy('widget_id')
            ->map(function (SupportCollection $rows): array {
                $perEvent = [];

                foreach ($rows as $row) {
                    $perEvent[$row->event] = (int) $row->total;
                }

                return $perEvent;
            })
            ->all();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfRange = Carbon::now()->endOfDay();

        $dateLabels = [];
        $cursor = $startOfMonth->copy();

        while ($cursor->lte($endOfRange)) {
            $dateLabels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $rawDaily = Analytics::query()
            ->selectRaw('DATE(created_at) as day, event, COUNT(*) as total')
            ->whereIn('widget_id', $widgetIds)
            ->whereBetween('created_at', [$startOfMonth, $endOfRange])
            ->groupBy(DB::raw('DATE(created_at)'), 'event')
            ->get()
            ->groupBy('event');

        $chartSeriesByEvent = [];

        foreach ($eventTypes as $eventType) {
            $perDayCounts = array_fill(0, count($dateLabels), 0);
            $rows = $rawDaily->get($eventType, collect());

            foreach ($rows as $row) {
                $idx = array_search(Carbon::parse($row->day)->format('Y-m-d'), $dateLabels, true);

                if ($idx !== false) {
                    $perDayCounts[$idx] = (int) $row->total;
                }
            }

            $chartSeriesByEvent[$eventType] = [
                'name' => str_replace('_', ' ', $eventType),
                'data' => $perDayCounts,
            ];
        }

        $allTime = Analytics::query()
            ->selectRaw('event, COUNT(*) as total')
            ->whereIn('widget_id', $widgetIds)
            ->whereIn('event', $keyEvents)
            ->groupBy('event')
            ->get()
            ->pluck('total', 'event');

        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentMonth = Analytics::query()
            ->selectRaw('event, COUNT(*) as total')
            ->whereIn('widget_id', $widgetIds)
            ->whereIn('event', $keyEvents)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->groupBy('event')
            ->get()
            ->pluck('total', 'event');

        $previousMonth = Analytics::query()
            ->selectRaw('event, COUNT(*) as total')
            ->whereIn('widget_id', $widgetIds)
            ->whereIn('event', $keyEvents)
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->groupBy('event')
            ->get()
            ->pluck('total', 'event');

        $eventStats = [];

        foreach ($keyEvents as $keyEvent) {
            $curr = (int) ($currentMonth[$keyEvent] ?? 0);
            $prev = (int) ($previousMonth[$keyEvent] ?? 0);
            $momChange = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : null;

            $eventStats[$keyEvent] = [
                'all_time' => (int) ($allTime[$keyEvent] ?? 0),
                'current_month' => $curr,
                'previous_month' => $prev,
                'mom_change' => $momChange,
            ];
        }

        return [
            'widgets' => $widgets,
            'analyticsCounts' => $analyticsCounts,
            'eventTypes' => $eventTypes,
            'chartCategories' => $dateLabels,
            'chartSeriesByEvent' => $chartSeriesByEvent,
            'eventStats' => $eventStats,
        ];
    }

    /**
     * @return Collection<int, Widget>
     */
    public function getAccessibleWidgets(User $user): Collection
    {
        $query = Widget::query()->with('user');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('id')->get();
    }
}
