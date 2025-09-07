<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Analytics;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Track a widget analytics event
     */
    public function track(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'event' => 'required|string|in:' . implode(',', Analytics::getEventTypes()),
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                $request->string('event')->toString(),
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track widget loaded event
     */
    public function trackLoaded(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                Analytics::EVENT_LOADED,
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Loaded event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track loaded event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track widget opened event
     */
    public function trackOpened(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                Analytics::EVENT_OPENED,
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Opened event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track opened event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track action button clicked event
     */
    public function trackActionClicked(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                Analytics::EVENT_ACTION_CLICKED,
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Action clicked event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track action clicked event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track chat button clicked event
     */
    public function trackChatClicked(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                Analytics::EVENT_CHAT_CLICKED,
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Chat clicked event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track chat clicked event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track chat started event
     */
    public function trackChatStarted(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                Analytics::EVENT_CHAT_STARTED,
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Chat started event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track chat started event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track conversion event
     */
    public function trackConversion(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_id' => 'required|integer|exists:widgets,id',
            'url' => 'required|string|max:2048',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->track(
                $request->integer('widget_id'),
                Analytics::EVENT_CONVERSION,
                $request->string('url')->toString(),
                $request->array('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversion event tracked successfully',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track conversion event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get analytics for a specific widget
     */
    public function getWidgetAnalytics(Request $request, int $widgetId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event' => 'nullable|string|in:' . implode(',', Analytics::getEventTypes()),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $analytics = $this->analyticsService->getWidgetAnalytics(
                $widgetId,
                $request->string('event')?->toString()
            );

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get widget analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get analytics summary for a widget
     */
    public function getWidgetAnalyticsSummary(int $widgetId): JsonResponse
    {
        try {
            $summary = $this->analyticsService->getWidgetAnalyticsSummary($widgetId);
            $conversionRate = $this->analyticsService->getConversionRate($widgetId);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'conversion_rate' => $conversionRate,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get widget analytics summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
