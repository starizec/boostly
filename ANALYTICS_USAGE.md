# Widget Analytics System

This document explains how to use the widget analytics tracking system to monitor user interactions with your chat widgets.

## Overview

The analytics system tracks the following events:
- **loaded** - When widget is loaded on a page
- **opened** - When widget is maximized/opened
- **action_clicked** - When action button is clicked
- **chat_clicked** - When chat button is clicked
- **chat_started** - When chat is started
- **conversion** - When widget converted (form submission, etc.)

## API Endpoints

### General Tracking Endpoint
```
POST /api/analytics/track
```

**Request Body:**
```json
{
    "widget_id": 1,
    "event": "loaded",
    "url": "https://example.com/page",
    "data": {
        "user_agent": "Mozilla/5.0...",
        "timestamp": "2024-01-01T12:00:00Z"
    }
}
```

### Specific Event Endpoints

#### Track Widget Loaded
```
POST /api/analytics/track/loaded
```

#### Track Widget Opened
```
POST /api/analytics/track/opened
```

#### Track Action Clicked
```
POST /api/analytics/track/action-clicked
```

#### Track Chat Clicked
```
POST /api/analytics/track/chat-clicked
```

#### Track Chat Started
```
POST /api/analytics/track/chat-started
```

#### Track Conversion
```
POST /api/analytics/track/conversion
```

**Request Body for specific endpoints:**
```json
{
    "widget_id": 1,
    "url": "https://example.com/page",
    "data": {
        "additional_info": "value"
    }
}
```

### Analytics Retrieval Endpoints

#### Get Widget Analytics
```
GET /api/analytics/widget/{widgetId}?event=loaded
```

#### Get Widget Analytics Summary
```
GET /api/analytics/widget/{widgetId}/summary
```

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "loaded": 150,
            "opened": 75,
            "action_clicked": 30,
            "chat_clicked": 45,
            "chat_started": 20,
            "conversion": 5,
            "total_events": 325
        },
        "conversion_rate": 3.33
    }
}
```

## Usage Examples

### JavaScript Widget Integration

```javascript
class WidgetAnalytics {
    constructor(widgetId, baseUrl) {
        this.widgetId = widgetId;
        this.baseUrl = baseUrl;
    }

    async trackEvent(event, url, data = {}) {
        try {
            const response = await fetch(`${this.baseUrl}/api/analytics/track`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    widget_id: this.widgetId,
                    event: event,
                    url: url,
                    data: {
                        timestamp: new Date().toISOString(),
                        user_agent: navigator.userAgent,
                        ...data
                    }
                })
            });

            if (!response.ok) {
                console.error('Analytics tracking failed:', response.status);
            }
        } catch (error) {
            console.error('Analytics tracking error:', error);
        }
    }

    trackLoaded(url, data = {}) {
        return this.trackEvent('loaded', url, data);
    }

    trackOpened(url, data = {}) {
        return this.trackEvent('opened', url, data);
    }

    trackActionClicked(url, data = {}) {
        return this.trackEvent('action_clicked', url, data);
    }

    trackChatClicked(url, data = {}) {
        return this.trackEvent('chat_clicked', url, data);
    }

    trackChatStarted(url, data = {}) {
        return this.trackEvent('chat_started', url, data);
    }

    trackConversion(url, data = {}) {
        return this.trackEvent('conversion', url, data);
    }
}

// Usage in your widget
const analytics = new WidgetAnalytics(widgetId, 'https://your-domain.com');

// Track when widget loads
analytics.trackLoaded(window.location.href);

// Track when user opens widget
document.getElementById('widget-toggle').addEventListener('click', () => {
    analytics.trackOpened(window.location.href);
});

// Track when action button is clicked
document.getElementById('action-button').addEventListener('click', () => {
    analytics.trackActionClicked(window.location.href, {
        action_type: 'contact_form'
    });
});

// Track when chat is started
analytics.trackChatStarted(window.location.href, {
    chat_id: chatId
});

// Track conversion
analytics.trackConversion(window.location.href, {
    conversion_type: 'form_submission',
    form_data: formData
});
```

### PHP Backend Usage

```php
use App\Services\AnalyticsService;
use App\Http\Controllers\WidgetAnalyticsHelper;

// Using AnalyticsService directly
$analyticsService = app(AnalyticsService::class);

$analyticsService->track(
    widgetId: 1,
    event: 'loaded',
    url: 'https://example.com/page',
    data: ['source' => 'homepage']
);

// Using WidgetAnalyticsHelper
$helper = app(WidgetAnalyticsHelper::class);

$helper->trackWidgetLoaded(
    widgetId: 1,
    url: 'https://example.com/page',
    additionalData: ['source' => 'homepage']
);

// Get analytics summary
$summary = $analyticsService->getWidgetAnalyticsSummary(1);
$conversionRate = $analyticsService->getConversionRate(1);
```

### Laravel Controller Integration

```php
use App\Services\AnalyticsService;

class ChatController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function startChat(Request $request)
    {
        // Your existing chat logic...
        
        // Track chat started event
        $this->analyticsService->track(
            widgetId: $request->integer('widget_id'),
            event: 'chat_started',
            url: $request->string('url'),
            data: [
                'chat_id' => $chat->id,
                'user_type' => 'visitor'
            ]
        );

        return response()->json(['success' => true]);
    }
}
```

## Database Schema

The analytics are stored in the `analytics` table with the following structure:

```sql
CREATE TABLE analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    widget_id BIGINT UNSIGNED NOT NULL,
    event VARCHAR(255) NOT NULL,
    url VARCHAR(2048) NOT NULL,
    data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);
```

## Event Types

All available event types are defined as constants in the `Analytics` model:

- `Analytics::EVENT_LOADED` = 'loaded'
- `Analytics::EVENT_OPENED` = 'opened'
- `Analytics::EVENT_ACTION_CLICKED` = 'action_clicked'
- `Analytics::EVENT_CHAT_CLICKED` = 'chat_clicked'
- `Analytics::EVENT_CHAT_STARTED` = 'chat_started'
- `Analytics::EVENT_CONVERSION` = 'conversion'

## Error Handling

The system includes comprehensive error handling:

- Validation errors return HTTP 422 with detailed error messages
- Server errors return HTTP 500 with error details
- All errors are logged for debugging
- Failed analytics tracking won't break your widget functionality

## Performance Considerations

- Analytics tracking is asynchronous and non-blocking
- Failed tracking attempts are logged but don't affect user experience
- Consider implementing client-side batching for high-traffic scenarios
- The system uses JSON for flexible data storage

## Security

- All endpoints require CORS middleware
- Widget ID validation ensures only valid widgets can be tracked
- IP addresses and user agents are automatically captured for security analysis
- No sensitive data should be included in the `data` field
