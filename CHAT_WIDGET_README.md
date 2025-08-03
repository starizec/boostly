# Boostly Chat Widget Documentation

## Overview

The Boostly Chat Widget is a modern, feature-rich chat solution built with Laravel and JavaScript. It provides real-time customer communication capabilities with a beautiful, customizable interface.

## Features

- 💬 **Real-time Messaging**: Instant message delivery with automatic polling
- 🎨 **Customizable Design**: Fully customizable colors, fonts, and styling
- 📱 **Responsive Design**: Works on desktop, tablet, and mobile devices
- 🔒 **Secure & Reliable**: Built with Laravel backend and CSRF protection
- 💾 **Persistent Sessions**: Chat sessions saved and resumable after page refresh
- ⚡ **Fast & Lightweight**: Optimized JavaScript with minimal dependencies

## Installation

### 1. Prerequisites

- Laravel 10+ application
- Database with the required tables (chats, chat_messages, contacts, etc.)
- Filament admin panel configured

### 2. API Routes

The following routes are automatically added to your `routes/web.php`:

```php
// Chat Widget API Routes
Route::prefix('api/chat')->group(function () {
    Route::post('/start', [ChatController::class, 'startChat'])->name('chat.start');
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/messages/{chatId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/status/{chatId}', [ChatController::class, 'getChatStatus'])->name('chat.status');
});
```

### 3. Widget Script

Include the chat widget script in your HTML pages:

```html
<script src="/js/widget/chat-widget.js"></script>
```

## Configuration

### 1. Widget Setup in Filament Admin

1. Create a new Widget in the Filament admin panel
2. Configure the widget settings:
   - Button text
   - Background colors
   - Text colors
   - Border radius
   - Show/hide schedules

### 2. Domain Configuration

1. Add your domain in the Domains section
2. Create Widget URLs for specific pages
3. Configure visibility schedules

### 3. Widget Actions

1. Create widget actions for additional functionality
2. Configure action buttons and URLs

## API Endpoints

### Start Chat
```http
POST /api/chat/start
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "message": "Hello, I need help!",
    "phone": "1234567890" // optional
}
```

**Response:**
```json
{
    "success": true,
    "chat_id": 1,
    "message": {
        "id": 1,
        "chat_id": 1,
        "message": "Hello, I need help!",
        "type": "user",
        "created_at": "2024-01-01T12:00:00.000000Z"
    },
    "contact": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

### Send Message
```http
POST /api/chat/message
Content-Type: application/json

{
    "chat_id": 1,
    "message": "This is my message",
    "type": "user" // or "agent"
}
```

### Get Messages
```http
GET /api/chat/messages/{chatId}
```

### Get Chat Status
```http
GET /api/chat/status/{chatId}
```

## JavaScript API

The chat widget provides a JavaScript class with the following methods:

### Initialization
```javascript
// The widget automatically initializes when the script loads
// It checks domain permissions and widget visibility
```

### Methods

- `toggleChat()` - Open/close the chat window
- `startChat()` - Start a new chat session
- `sendMessage()` - Send a message
- `loadMessages()` - Load chat messages
- `startPolling()` - Start polling for new messages
- `stopPolling()` - Stop polling for new messages

## Customization

### Styling

The widget uses CSS custom properties and can be customized through the Filament admin panel:

- Button colors
- Background colors
- Text colors
- Border radius
- Font sizes

### Advanced Customization

You can override widget styles by adding custom CSS:

```css
#boostly-chat-widget {
    /* Custom styles */
}

#boostly-chat-button {
    /* Custom button styles */
}

#boostly-chat-window {
    /* Custom window styles */
}
```

## Database Schema

### Required Tables

1. **chats** - Main chat sessions
2. **chat_messages** - Individual messages
3. **contacts** - Customer contact information
4. **domains** - Allowed domains
5. **widgets** - Widget configurations
6. **widget_urls** - Widget URL mappings
7. **widget_styles** - Widget styling options
8. **widget_actions** - Widget action buttons

### Key Relationships

- `Chat` belongs to `Contact`
- `Chat` has many `ChatMessage`
- `Widget` has one `WidgetStyle`
- `Widget` has one `WidgetAction`

## Security Features

- CSRF protection on all API endpoints
- Domain validation for widget access
- Input validation and sanitization
- SQL injection prevention through Eloquent ORM
- XSS protection through proper output encoding

## Performance Optimization

- Efficient database queries with proper indexing
- Polling interval of 3 seconds for new messages
- Minimal JavaScript footprint
- Optimized CSS with efficient selectors
- Local storage for session persistence

## Troubleshooting

### Common Issues

1. **Widget not appearing**
   - Check domain configuration in admin panel
   - Verify widget visibility schedules
   - Check browser console for errors

2. **Messages not sending**
   - Verify CSRF token is present
   - Check API endpoint accessibility
   - Validate input data format

3. **Styling issues**
   - Check widget style configuration
   - Verify CSS custom properties
   - Test on different browsers

### Debug Mode

Enable debug mode by adding to your HTML:

```html
<script>
    window.BOOSTLY_DEBUG = true;
</script>
```

## Testing

### Test Page

Visit `/test-widget.html` to see a demonstration of the chat widget.

### Manual Testing

1. Configure a widget in the admin panel
2. Add your domain to the allowed domains
3. Create a widget URL for a specific page
4. Include the widget script on your page
5. Test the chat functionality

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Mobile Support

- iOS Safari 12+
- Chrome Mobile 60+
- Samsung Internet 7+

## License

This chat widget is part of the Boostly application and follows the same license terms.

## Support

For support and questions, please refer to the main application documentation or contact the development team. 