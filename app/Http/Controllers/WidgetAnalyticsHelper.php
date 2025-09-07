<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AnalyticsService;

/**
 * Helper class for widget analytics tracking
 * This can be used to easily track analytics events from various parts of the application
 */
class WidgetAnalyticsHelper
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Track widget loaded event
     */
    public function trackWidgetLoaded(int $widgetId, string $url, array $additionalData = []): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ], $additionalData);

        $this->analyticsService->track($widgetId, 'loaded', $url, $data);
    }

    /**
     * Track widget opened event
     */
    public function trackWidgetOpened(int $widgetId, string $url, array $additionalData = []): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ], $additionalData);

        $this->analyticsService->track($widgetId, 'opened', $url, $data);
    }

    /**
     * Track action button clicked event
     */
    public function trackActionClicked(int $widgetId, string $url, array $additionalData = []): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ], $additionalData);

        $this->analyticsService->track($widgetId, 'action_clicked', $url, $data);
    }

    /**
     * Track chat button clicked event
     */
    public function trackChatClicked(int $widgetId, string $url, array $additionalData = []): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ], $additionalData);

        $this->analyticsService->track($widgetId, 'chat_clicked', $url, $data);
    }

    /**
     * Track chat started event
     */
    public function trackChatStarted(int $widgetId, string $url, array $additionalData = []): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'chat_id' => $additionalData['chat_id'] ?? null,
        ], $additionalData);

        $this->analyticsService->track($widgetId, 'chat_started', $url, $data);
    }

    /**
     * Track conversion event
     */
    public function trackConversion(int $widgetId, string $url, array $additionalData = []): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'conversion_type' => $additionalData['conversion_type'] ?? 'form_submission',
        ], $additionalData);

        $this->analyticsService->track($widgetId, 'conversion', $url, $data);
    }
}
