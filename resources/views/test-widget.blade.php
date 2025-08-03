<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Widget Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .feature-card h3 {
            margin-top: 0;
            color: #007bff;
        }
        
        .demo-section {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        
        .demo-section p {
            margin-bottom: 20px;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #0056b3;
        }

        .setup-instructions {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }

        .setup-instructions h3 {
            color: #1976d2;
            margin-top: 0;
        }

        .setup-instructions ol {
            margin: 0;
            padding-left: 20px;
        }

        .setup-instructions li {
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Boostly Chat Widget Demo</h1>
        
        <p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 30px;">
            Experience the power of real-time customer communication with our modern chat widget.
        </p>
        
        <div class="feature-grid">
            <div class="feature-card">
                <h3>💬 Real-time Messaging</h3>
                <p>Instant message delivery with automatic polling for new messages every 3 seconds.</p>
            </div>
            
            <div class="feature-card">
                <h3>🎨 Customizable Design</h3>
                <p>Fully customizable colors, fonts, and styling to match your brand identity.</p>
            </div>
            
            <div class="feature-card">
                <h3>📱 Responsive Design</h3>
                <p>Works perfectly on desktop, tablet, and mobile devices with touch-friendly interface.</p>
            </div>
            
            <div class="feature-card">
                <h3>🔒 Secure & Reliable</h3>
                <p>Built with Laravel backend, CSRF protection, and proper error handling.</p>
            </div>
            
            <div class="feature-card">
                <h3>💾 Persistent Sessions</h3>
                <p>Chat sessions are saved and can be resumed even after page refresh.</p>
            </div>
            
            <div class="feature-card">
                <h3>⚡ Fast & Lightweight</h3>
                <p>Optimized JavaScript with minimal dependencies and fast loading times.</p>
            </div>
        </div>
        
        <div class="demo-section">
            <h3>Try the Chat Widget</h3>
            <p>Click the chat button in the bottom-right corner to start a conversation!</p>
            <p><strong>Note:</strong> Make sure you have the widget configured in your Laravel admin panel.</p>
        </div>

        <div class="setup-instructions">
            <h3>Setup Instructions</h3>
            <ol>
                <li><strong>Configure Widget:</strong> Go to your Filament admin panel and create a new Widget</li>
                <li><strong>Add Domain:</strong> Add your current domain to the allowed domains list</li>
                <li><strong>Create Widget URL:</strong> Create a widget URL for this page (e.g., "/test-widget")</li>
                <li><strong>Configure Styles:</strong> Set up widget colors, fonts, and styling preferences</li>
                <li><strong>Test:</strong> Refresh this page and look for the chat button in the bottom-right corner</li>
            </ol>
        </div>
        
        <div style="margin-top: 40px; text-align: center; color: #666;">
            <p>This is a demonstration page for the Boostly Chat Widget.</p>
            <p>The widget will appear in the bottom-right corner if properly configured.</p>
        </div>
    </div>

    <!-- Chat Widget Script -->
    <script src="{{ asset('js/widget/chat-widget.js') }}"></script>
</body>
</html> 