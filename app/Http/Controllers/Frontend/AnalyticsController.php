<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Analytics;
use App\Models\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AnalyticsController extends Controller
{
    public function index()
    {
        $widgets = Widget::where('user_id', Auth::user()->id)->get();

        // Aggregate analytics counts per widget and per event
        $analyticsCounts = Analytics::query()
            ->selectRaw('widget_id, event, COUNT(*) as total')
            ->whereIn('widget_id', $widgets->pluck('id'))
            ->groupBy('widget_id', 'event')
            ->get()
            ->groupBy('widget_id')
            ->map(function ($rows) {
                $perEvent = [];
                foreach ($rows as $row) {
                    $perEvent[$row->event] = (int) $row->total;
                }
                return $perEvent;
            });
       
        $eventTypes = Analytics::getEventTypes();

        // Build daily counts per event for the current month across user's widgets
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfRange = Carbon::now()->endOfDay();

        $dateLabels = [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfRange)) {
            $dateLabels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $rawDaily = Analytics::query()
            ->selectRaw("DATE(created_at) as day, event, COUNT(*) as total")
            ->whereIn('widget_id', $widgets->pluck('id'))
            ->whereBetween('created_at', [$startOfMonth, $endOfRange])
            ->groupBy(DB::raw('DATE(created_at)'), 'event')
            ->get()
            ->groupBy('event');

        $series = [];
        $seriesByEvent = [];
        foreach ($eventTypes as $eventType) {
            $perDayCounts = array_fill(0, count($dateLabels), 0);
            $rows = $rawDaily->get($eventType, collect());
            foreach ($rows as $row) {
                $idx = array_search(Carbon::parse($row->day)->format('Y-m-d'), $dateLabels, true);
                if ($idx !== false) {
                    $perDayCounts[$idx] = (int) $row->total;
                }
            }
            $entry = [
                'name' => str_replace('_', ' ', $eventType),
                'data' => $perDayCounts,
            ];
            $series[] = $entry; // keep combined series if needed elsewhere
            $seriesByEvent[$eventType] = $entry;
        }

        // Prepare single-series datasets for specific events
        $chartSeriesOpened = isset($seriesByEvent['opened']) ? [$seriesByEvent['opened']] : [[
            'name' => 'opened',
            'data' => array_fill(0, count($dateLabels), 0),
        ]];
        $chartSeriesLoaded = isset($seriesByEvent['loaded']) ? [$seriesByEvent['loaded']] : [[
            'name' => 'loaded',
            'data' => array_fill(0, count($dateLabels), 0),
        ]];
        $chartSeriesActionClicked = isset($seriesByEvent['action_clicked']) ? [$seriesByEvent['action_clicked']] : [[
            'name' => 'action clicked',
            'data' => array_fill(0, count($dateLabels), 0),
        ]];

        // All-time totals and month-over-month change for key events
        $widgetIds = $widgets->pluck('id');
        $keyEvents = ['loaded', 'opened', 'action_clicked'];

        // All-time totals
        $allTime = Analytics::query()
            ->selectRaw('event, COUNT(*) as total')
            ->whereIn('widget_id', $widgetIds)
            ->whereIn('event', $keyEvents)
            ->groupBy('event')
            ->get()
            ->pluck('total', 'event');

        // Current and previous month ranges
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

        return view('frontend.analytics.index', [
            'widgets' => $widgets,
            'analyticsCounts' => $analyticsCounts,
            'eventTypes' => $eventTypes,
            'chartCategories' => $dateLabels,
            'chartSeries' => $series,
            'chartSeriesOpened' => $chartSeriesOpened,
            'chartSeriesLoaded' => $chartSeriesLoaded,
            'chartSeriesActionClicked' => $chartSeriesActionClicked,
            'eventStats' => $eventStats,
        ]);
    }

    public function widgets()
    {
        return view('frontend.analytics.widgets');
    }

    public function actions()
    {
        return view('frontend.analytics.actions');
    }
}
