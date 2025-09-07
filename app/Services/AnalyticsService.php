<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Analytics;
use App\Models\Widget;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
     * Track a widget analytics event
     */
    public function track(
        int $widgetId,
        string $event,
        string $url,
        array $data = []
    ): Analytics {
        try {
            // Validate event type
            if (!in_array($event, Analytics::getEventTypes())) {
                throw new \InvalidArgumentException("Invalid event type: {$event}");
            }

            // Validate widget exists
            $widget = Widget::find($widgetId);
            if (!$widget) {
                throw new \InvalidArgumentException("Widget not found: {$widgetId}");
            }

            return Analytics::create([
                'widget_id' => $widgetId,
                'event' => $event,
                'url' => $url,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics tracking failed', [
                'widget_id' => $widgetId,
                'event' => $event,
                'url' => $url,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get analytics for a specific widget
     */
    public function getWidgetAnalytics(int $widgetId, ?string $event = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Analytics::where('widget_id', $widgetId);

        if ($event) {
            $query->where('event', $event);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get analytics summary for a widget
     */
    public function getWidgetAnalyticsSummary(int $widgetId): array
    {
        $analytics = Analytics::where('widget_id', $widgetId)
            ->selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->get()
            ->pluck('count', 'event')
            ->toArray();

        return [
            'loaded' => $analytics[Analytics::EVENT_LOADED] ?? 0,
            'opened' => $analytics[Analytics::EVENT_OPENED] ?? 0,
            'action_clicked' => $analytics[Analytics::EVENT_ACTION_CLICKED] ?? 0,
            'chat_clicked' => $analytics[Analytics::EVENT_CHAT_CLICKED] ?? 0,
            'chat_started' => $analytics[Analytics::EVENT_CHAT_STARTED] ?? 0,
            'conversion' => $analytics[Analytics::EVENT_CONVERSION] ?? 0,
            'total_events' => array_sum($analytics),
        ];
    }

    /**
     * Get conversion rate for a widget
     */
    public function getConversionRate(int $widgetId): float
    {
        $summary = $this->getWidgetAnalyticsSummary($widgetId);

        if ($summary['loaded'] === 0) {
            return 0.0;
        }

        return round(($summary['conversion'] / $summary['loaded']) * 100, 2);
    }
}
