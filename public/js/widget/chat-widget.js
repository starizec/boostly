(function () {
    'use strict';

    class ChatWidget {
        constructor() {
            this.host = 'http://boostly.test';
            this.scriptTag = document.currentScript;
            this.clientDomain = `${window.location.protocol}//${window.location.host}`;
            this.widget = null;
            this.videoAspectRatio = 16/9; // Default aspect ratio
            this.initialWidth = 150; // Initial width in pixels
        }

        async init() {
            try {
                // Call to verify endpoint
                const response = await fetch(`${this.host}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        client_domain: this.clientDomain,
                        timestamp: Date.now()
                    })
                });

                if (!response.ok) {
                    throw new Error(`Verification failed: ${response.status}`);
                }

                const data = await response.json();
                console.log('Widget verification successful:', data);
                this.widget = data.widget;
                // Continue with widget initialization
                this.initializeWidget();
                
            } catch (error) {
                console.error('Widget verification failed:', error);
                // Handle verification failure - maybe show an error message or disable widget
            }
        }

        initializeWidget() {
            // Create the widget container with video background
            this.createWidget();
        }

        createWidget() {
            // Calculate height based on aspect ratio and initial width
            const initialHeight = this.initialWidth / this.videoAspectRatio;

            // Create main widget container
            this.widgetContainer = document.createElement('div');
            this.widgetContainer.id = 'boostly-chat-widget';
            this.widgetContainer.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                width: ${this.initialWidth}px;
                height: ${initialHeight}px;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                cursor: pointer;
            `;

            // Create video background div
            this.createVideoBackground();
            
            // Create hover button (initially hidden)
            this.createHoverButton();
            
            // Add hover and click event listeners
            this.addEventListeners();
            
            // Add to page
            document.body.appendChild(this.widgetContainer);
        }

        createVideoBackground() {
            // Create video background container
            this.videoContainer = document.createElement('div');
            this.videoContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #000;
                border-radius: 10px;
                overflow: hidden;
            `;

            // Create video element
            this.videoElement = document.createElement('video');
            this.videoElement.style.cssText = `
                width: 100%;
                height: 100%;
                object-fit: cover;
                opacity: 0.7;
            `;
            this.videoElement.autoplay = true;
            this.videoElement.muted = true;
            this.videoElement.loop = true;
            this.videoElement.playsInline = true;

            // Set video source from widget media
            if (this.widget && this.widget.media && this.widget.media.url) {
                const videoUrl = `${this.host}/storage/${this.widget.media.url}`;
                this.videoElement.src = videoUrl;
                console.log('Loading video from:', videoUrl);
                
                // Get video aspect ratio when metadata is loaded
                this.videoElement.addEventListener('loadedmetadata', () => {
                    this.videoAspectRatio = this.videoElement.videoWidth / this.videoElement.videoHeight;
                    console.log('Video aspect ratio:', this.videoAspectRatio);
                    
                    // Update widget dimensions with correct aspect ratio
                    const newHeight = this.initialWidth / this.videoAspectRatio;
                    this.widgetContainer.style.height = `${newHeight}px`;
                });
            } else {
                // Fallback to a default video or show error
                console.warn('No video media found in widget data');
                this.videoContainer.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            }

            // Add video to container
            this.videoContainer.appendChild(this.videoElement);
            this.widgetContainer.appendChild(this.videoContainer);
        }

        createHoverButton() {
            // Create hover button overlay
            this.hoverButton = document.createElement('div');
            this.hoverButton.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                padding: 8px;
                border-radius: 50%;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.2);
                opacity: 0;
                pointer-events: none;
                font-weight: bold;
                color: #333;
                font-size: 16px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            `;

            this.hoverButton.innerHTML = '⤢';

            this.widgetContainer.appendChild(this.hoverButton);
        }

        addEventListeners() {
            // Hover events to show/hide button
            this.widgetContainer.addEventListener('mouseenter', () => {
                this.hoverButton.style.opacity = '1';
                this.hoverButton.style.pointerEvents = 'auto';
                this.hoverButton.style.transform = 'scale(1.1)';
            });

            this.widgetContainer.addEventListener('mouseleave', () => {
                this.hoverButton.style.opacity = '0';
                this.hoverButton.style.pointerEvents = 'none';
                this.hoverButton.style.transform = 'scale(1)';
            });

            // Click event to expand video
            this.widgetContainer.addEventListener('click', () => {
                this.expandVideo();
            });
        }

        expandVideo() {
            const expandedWidth = 300;
            const expandedHeight = expandedWidth / this.videoAspectRatio;
            
            this.widgetContainer.style.width = `${expandedWidth}px`;
            this.widgetContainer.style.height = `${expandedHeight}px`;
            
            // Hide the hover button after expansion
            this.hoverButton.style.opacity = '0';
            this.hoverButton.style.pointerEvents = 'none';
            
            // Play sound if available
            this.playExpansionSound();
            
            // Show action and chat buttons
            this.createExpandedButtons();
            
            console.log('Video expanded to:', expandedWidth, 'x', expandedHeight);
        }

        playExpansionSound() {
            // Create audio element for expansion sound
            const audio = new Audio();
            
            // Set audio source if available in widget data
            if (this.widget && this.widget.media && this.widget.media.audio_url) {
                audio.src = `${this.host}/storage/${this.widget.media.audio_url}`;
            } else {
                // Fallback to a simple notification sound (you can replace with your own sound file)
                audio.src = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT';
            }
            
            audio.volume = 0.5; // Set volume to 50%
            audio.play().catch(error => {
                console.log('Audio playback failed:', error);
            });
        }

        createExpandedButtons() {
            // Create container for expanded buttons
            this.expandedButtonsContainer = document.createElement('div');
            this.expandedButtonsContainer.style.cssText = `
                position: absolute;
                bottom: 15px;
                left: 15px;
                right: 15px;
                display: flex;
                flex-direction: column;
                gap: 10px;
                z-index: 10;
            `;

            // Create Action Button
            if (this.widget && this.widget.widget_action) {
                this.actionButton = document.createElement('div');
                this.actionButton.style.cssText = `
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    padding: 12px 16px;
                    border-radius: 8px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    font-weight: bold;
                    color: #333;
                    font-size: 14px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                `;

                const actionText = this.widget.widget_action.button_text || 'Take Action';
                this.actionButton.innerHTML = actionText;

                // Add hover effect
                this.actionButton.addEventListener('mouseenter', () => {
                    this.actionButton.style.background = 'rgba(255, 255, 255, 1)';
                    this.actionButton.style.transform = 'translateY(-2px)';
                });

                this.actionButton.addEventListener('mouseleave', () => {
                    this.actionButton.style.background = 'rgba(255, 255, 255, 0.95)';
                    this.actionButton.style.transform = 'translateY(0)';
                });

                // Add click handler for action button
                this.actionButton.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent widget click event
                    if (this.widget.widget_action.url) {
                        window.open(this.widget.widget_action.url, '_blank');
                    }
                });

                this.expandedButtonsContainer.appendChild(this.actionButton);
            }

            // Create Start Chat Button
            this.startChatButton = document.createElement('div');
            this.startChatButton.style.cssText = `
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 12px 16px;
                border-radius: 8px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.2);
                font-weight: bold;
                color: #333;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;

            const chatText = this.widget && this.widget.button_text ? 
                this.widget.button_text : '💬 Start Chat';
            this.startChatButton.innerHTML = chatText;

            // Add hover effect
            this.startChatButton.addEventListener('mouseenter', () => {
                this.startChatButton.style.background = 'rgba(255, 255, 255, 1)';
                this.startChatButton.style.transform = 'translateY(-2px)';
            });

            this.startChatButton.addEventListener('mouseleave', () => {
                this.startChatButton.style.background = 'rgba(255, 255, 255, 0.95)';
                this.startChatButton.style.transform = 'translateY(0)';
            });

            // Add click handler for start chat button
            this.startChatButton.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent widget click event
                this.toggleChat();
            });

            this.expandedButtonsContainer.appendChild(this.startChatButton);
            this.widgetContainer.appendChild(this.expandedButtonsContainer);
        }

        // Method to expand widget (can be called later when needed)
        expandWidget() {
            this.widgetContainer.style.width = '350px';
            this.widgetContainer.style.height = '500px';
            this.createChatButton();
        }

        createChatButton() {
            // Create chat button overlay
            this.chatButton = document.createElement('div');
            this.chatButton.style.cssText = `
                position: absolute;
                bottom: 20px;
                left: 20px;
                right: 20px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                padding: 15px;
                border-radius: 8px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.2);
            `;

            // Set button text from widget data
            const buttonText = this.widget && this.widget.start_button_text ? 
                this.widget.start_button_text : '💬 Start Chat';

            this.chatButton.innerHTML = `
                <div style="font-weight: bold; color: #333; font-size: 16px;">
                    ${buttonText}
                </div>
            `;

            // Add hover effect
            this.chatButton.addEventListener('mouseenter', () => {
                this.chatButton.style.background = 'rgba(255, 255, 255, 1)';
                this.chatButton.style.transform = 'translateY(-2px)';
            });

            this.chatButton.addEventListener('mouseleave', () => {
                this.chatButton.style.background = 'rgba(255, 255, 255, 0.9)';
                this.chatButton.style.transform = 'translateY(0)';
            });

            // Add click handler
            this.chatButton.addEventListener('click', () => {
                this.toggleChat();
            });

            this.widgetContainer.appendChild(this.chatButton);
        }

        toggleChat() {
            // Toggle chat functionality
            console.log('Chat button clicked!');
            // You can expand this to show/hide chat interface
        }

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new ChatWidget().init();
        });
    } else {
        new ChatWidget().init();
    }
})();
