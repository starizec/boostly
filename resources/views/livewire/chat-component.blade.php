<div>
    @if ($isOpen)
        <div class="chat-container">
            <div class="chat-top">
                <h2 class="form-title">Start a Conversation</h2>
            </div>
            @if ($showInitialForm)
            
                <form wire:submit.prevent="startChat" class="contact-form">
                    <h2 class="form-title">Start a Conversation</h2>
                    <div class="form-group">
                        <input type="text" wire:model="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" wire:model="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea wire:model="message" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="submit-button">Start Chat</button>
                </form>
            @else
                <div class="messages">
                    @foreach ($messages as $message)
                        <div class="message {{ $message->type }}">
                            <div class="message-content">
                                {{ $message->message }}
                            </div>
                            <div class="message-time">
                                {{ $message->created_at->format('g:i A') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <form wire:submit.prevent="sendMessage" class="message-form">
                    <textarea wire:model="message" placeholder="Type your message" class="message-input" rows="1"></textarea>
                    <button type="submit" class="send-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            @endif

            <button wire:click="toggleChat" class="close-button">
                ✕
            </button>
        </div>
    @endif

    @if (!$isOpen)
        <div class="videoContainer">
            <div class="absolute inset-0">
                <video class="backgroundVideo" autoplay muted loop playsinline>
                    <source src="{{ Storage::url($media->url) }}" type="video/mp4">
                </video>
            </div>
        </div>

        <div class="action-button-container">
            <a href="{{ $widgetAction->url }}" class="action-button top-button" target="_blank"
                style="
                    color: {{ $widgetStyle->action_button_text_color }};
                    background-color: {{ $widgetStyle->action_button_background_color }};
                    border-radius: {{ $widgetStyle->action_button_border_radius }}px;
                    margin-bottom: 10px;
                    text-decoration: none;
                    text-align: center;
                    display: block;
                "
                onmouseover="this.style.color='{{ $widgetStyle->action_button_hover_text_color }}'; this.style.backgroundColor='{{ $widgetStyle->action_button_hover_background_color }}';"
                onmouseout="this.style.color='{{ $widgetStyle->action_button_text_color }}'; this.style.backgroundColor='{{ $widgetStyle->action_button_background_color }}';">
                {{ $widgetAction->button_text }}
            </a>

            <button wire:click="toggleChat" class="chat-button"
                style="
                    color: {{ $widgetStyle->chat_button_text_color }};
                    background-color: {{ $widgetStyle->chat_button_background_color }};
                    border-radius: {{ $widgetStyle->chat_button_border_radius }}px;
                "
                onmouseover="this.style.color='{{ $widgetStyle->chat_button_hover_text_color }}'; this.style.backgroundColor='{{ $widgetStyle->chat_button_hover_background_color }}';"
                onmouseout="this.style.color='{{ $widgetStyle->chat_button_text_color }}'; this.style.backgroundColor='{{ $widgetStyle->chat_button_background_color }}';">
                {{ $widget->button_text }}
            </button>
        </div>
    @endif

    <style>
        .videoContainer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .backgroundVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .action-button-container {
            position: fixed;
            bottom: 0;
            right: 0;
            left: 0;
            padding: 20px;
        }

        .action-button,
        .chat-button {
            width: 100%;
            padding: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            line-height: 1.5;
            display: block;
            box-sizing: border-box;
        }

        .chat-container {
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .chat-container input,
        .chat-container textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .messages {
            margin-bottom: 20px;
            margin-bottom: 18px;
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            width: 100%;
        }

        .message {
            padding: 8px 12px;
            margin: 8px 0;
            border-radius: 12px;
            max-width: 80%;
            position: relative;
        }

        .message-content {
            word-wrap: break-word;
        }

        .message-time {
            font-size: 0.75rem;
            color: #666;
            margin-top: 4px;
        }

        .message.user {
            background-color: {{ $widget->button_background_color }};
            color: {{ $widget->button_text_color }};
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .message.agent {
            background-color: #f0f0f0;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .contact-form {
            width: 100%;
            padding: 30px;
            background: white;
        }

        .form-title {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: {{ $widget->button_background_color }};
        }

        .contact-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-button {
            width: 100%;
            padding: 14px;
            background-color: {{ $widget->button_background_color }};
            color: {{ $widget->button_text_color }};
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-button:hover {
            background-color: {{ $widget->button_background_hover_color }};
            color: {{ $widget->button_text_hover_color }};
        }

        .close-button {
            position: absolute;
            top: 0px;
            right: 0px;
            padding: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
        }

        .close-button:hover {
            opacity: 0.7;
        }

        .message-form {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background-color: #fff;
            border-top: 1px solid #eee;
            width: 100%;
        }

        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #e1e1e1;
            border-radius: 20px;
            font-size: 14px;
            resize: none;
            max-height: 100px;
            overflow-y: auto;
        }

        .message-input:focus {
            outline: none;
            border-color: {{ $widget->button_background_color }};
        }

        .send-button {
            background-color: {{ $widget->button_background_color }};
            color: {{ $widget->button_text_color }};
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }

        .send-button:hover {
            background-color: {{ $widget->button_background_hover_color }};
            color: {{ $widget->button_text_hover_color }};
        }

        .send-button svg {
            width: 20px;
            height: 20px;
        }
    </style>
</div>
