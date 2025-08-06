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
            this.isExpanded = false; // Track expansion state
            this.isMuted = true; // Track mute state
            this.isChatFormVisible = false; // Track chat form visibility
            this.chatExist = false; // Track if chat exists in localStorage
            this.currentChat = null; // Store current chat data from response
            this.currentChatId = null; // Store current chat ID
            this.currentContact = null; // Store current contact ID
            this.widgetId = null; // Store widget ID
        }

        async init() {
            try {
                // Check if chat exists in localStorage
                const bcId = localStorage.getItem('bc_id');
                this.widgetId = localStorage.getItem('bw_id') || null;
                this.chatExist = bcId !== null;
                
                if (this.chatExist) {
                    console.log('Existing chat found with ID:', bcId);
                }

                // Call to verify endpoint
                const response = await fetch(`${this.host}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        client_domain: this.clientDomain,
                        bc_id: this.chatExist ? localStorage.getItem('bc_id') : null,
                        bw_id: this.widgetId,
                        timestamp: Date.now()
                    })
                });

                if (!response.ok) {
                    throw new Error(`Verification failed: ${response.status}`);
                }

                const data = await response.json();
                console.log('Widget verification successful:', data);
                this.widget = data.widget;
                this.widgetId = data.widget.id;
                localStorage.setItem('bw_id', this.widgetId);
                
                // Check if response contains chat data and chat exists
                if (data.chat && this.chatExist) {
                    console.log('Existing chat found in response, opening chat interface');
                    this.currentChatId = data.chat.id;
                    this.currentContact = data.chat.contact_id;
                    this.currentChat = data.chat; // Store the entire chat object
                    
                    // Initialize widget first
                    this.initializeWidget();
                    
                    // Then open chat interface directly
                    setTimeout(() => {
                        this.expandVideo();
                        this.showChatInterface();
                    }, 1000);
                } else {
                    // Continue with normal widget initialization
                    this.initializeWidget();
                }
                
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
            
            // Create mute button (initially hidden)
            this.createMuteButton();
            
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

        createMuteButton() {
            // Create mute button
            this.muteButton = document.createElement('div');
            this.muteButton.style.cssText = `
                position: absolute;
                top: 10px;
                left: 10px;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(10px);
                padding: 8px;
                border-radius: 50%;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: #fff;
                font-size: 16px;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
                opacity: 0;
                pointer-events: none;
            `;

            this.muteButton.innerHTML = '🔇'; // Muted icon
            this.isMuted = true;

            // Add click handler for mute/unmute
            this.muteButton.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent widget click event
                this.toggleMute();
            });

            this.widgetContainer.appendChild(this.muteButton);
        }

        toggleMute() {
            if (this.isMuted) {
                this.videoElement.muted = false;
                this.muteButton.innerHTML = '🔊'; // Unmuted icon
                this.isMuted = false;
            } else {
                this.videoElement.muted = true;
                this.muteButton.innerHTML = '🔇'; // Muted icon
                this.isMuted = true;
            }
        }

        showMuteButton() {
            this.muteButton.style.opacity = '1';
            this.muteButton.style.pointerEvents = 'auto';
        }

        hideMuteButton() {
            this.muteButton.style.opacity = '0';
            this.muteButton.style.pointerEvents = 'none';
        }

        createHoverButton() {
            // Create hover button overlay
            this.hoverButton = document.createElement('div');
            this.hoverButton.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                padding: 8px;
                border-radius: 50%;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                opacity: 0;
                pointer-events: none;
                font-weight: bold;
                color: white;
                font-size: 20px;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
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

            // Click event to expand video (only when collapsed)
            this.widgetContainer.addEventListener('click', () => {
                if (!this.isExpanded) {
                    this.expandVideo();
                }
            });

            // Add click handler for expand/collapse button
            this.hoverButton.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent widget click event
                if (this.isExpanded) {
                    this.collapseVideo();
                } else {
                    this.expandVideo();
                }
            });
        }

        expandVideo() {
            const expandedWidth = 300;
            const expandedHeight = expandedWidth / this.videoAspectRatio;
            
            this.widgetContainer.style.width = `${expandedWidth}px`;
            this.widgetContainer.style.height = `${expandedHeight}px`;
            
            // Change expand button to collapse button
            this.hoverButton.innerHTML = '⤵';
            this.hoverButton.style.transform = 'rotate(0deg)';
            
            // Show mute button and unmute video when expanded
            this.showMuteButton();
            this.videoElement.muted = false;
            this.isMuted = false;
            this.muteButton.innerHTML = '🔊'; // Unmuted icon
            
            // Play sound if available
            this.playExpansionSound();
            
            // Show action and chat buttons
            this.createExpandedButtons();
            
            this.isExpanded = true;
            
            console.log('Video expanded to:', expandedWidth, 'x', expandedHeight);
        }

        collapseVideo() {
            const collapsedWidth = this.initialWidth;
            const collapsedHeight = collapsedWidth / this.videoAspectRatio;
            
            this.widgetContainer.style.width = `${collapsedWidth}px`;
            this.widgetContainer.style.height = `${collapsedHeight}px`;
            
            // Change collapse button back to expand button
            this.hoverButton.innerHTML = '⤢';
            this.hoverButton.style.transform = 'rotate(0deg)';
            
            // Hide mute button and mute video when collapsed
            this.hideMuteButton();
            this.videoElement.muted = true;
            this.isMuted = true;
            
            // Remove expanded buttons if they exist
            if (this.expandedButtonsContainer) {
                this.expandedButtonsContainer.remove();
                this.expandedButtonsContainer = null;
            }
            
            this.isExpanded = false;
            
            console.log('Video collapsed to:', collapsedWidth, 'x', collapsedHeight);
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
                this.showChatForm();
            });

            this.expandedButtonsContainer.appendChild(this.startChatButton);
            this.widgetContainer.appendChild(this.expandedButtonsContainer);
        }

        showChatForm() {
            // Hide video and buttons
            this.videoContainer.style.display = 'none';
            this.expandedButtonsContainer.style.display = 'none';
            this.muteButton.style.display = 'none';
            
            // Create and show chat form
            this.createChatForm();
            
            this.isChatFormVisible = true;
        }

        createChatForm() {
            // Create chat form container
            this.chatFormContainer = document.createElement('div');
            this.chatFormContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 10px;
                padding: 20px;
                display: flex;
                flex-direction: column;
                z-index: 15;
            `;

            // Create form header
            const formHeader = document.createElement('div');
            formHeader.style.cssText = `
                text-align: center;
                margin-bottom: 20px;
                color: white;
            `;
            formHeader.innerHTML = `
                <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold;">Start a Conversation</h3>
                <p style="margin: 0; font-size: 14px; opacity: 0.9;">We'd love to hear from you!</p>
            `;

            // Create form
            const form = document.createElement('form');
            form.style.cssText = `
                display: flex;
                flex-direction: column;
                gap: 15px;
                flex: 1;
            `;

            // Name field
            const nameField = document.createElement('div');
            nameField.innerHTML = `
                <label style="display: block; color: white; font-size: 14px; margin-bottom: 5px; font-weight: 500;">Name *</label>
                <input type="text" id="chat-name" required style="
                    width: 100%;
                    padding: 12px;
                    border: none;
                    border-radius: 6px;
                    font-size: 14px;
                    background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(10px);
                    color: #333;
                    box-sizing: border-box;
                " placeholder="Your name">
            `;

            // Email field
            const emailField = document.createElement('div');
            emailField.innerHTML = `
                <label style="display: block; color: white; font-size: 14px; margin-bottom: 5px; font-weight: 500;">Email *</label>
                <input type="email" id="chat-email" required style="
                    width: 100%;
                    padding: 12px;
                    border: none;
                    border-radius: 6px;
                    font-size: 14px;
                    background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(10px);
                    color: #333;
                    box-sizing: border-box;
                " placeholder="your.email@example.com">
            `;

            // Message field
            const messageField = document.createElement('div');
            messageField.innerHTML = `
                <label style="display: block; color: white; font-size: 14px; margin-bottom: 5px; font-weight: 500;">Message *</label>
                <textarea id="chat-message" required rows="4" style="
                    width: 100%;
                    padding: 12px;
                    border: none;
                    border-radius: 6px;
                    font-size: 14px;
                    background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(10px);
                    color: #333;
                    box-sizing: border-box;
                    resize: vertical;
                    font-family: inherit;
                " placeholder="Tell us how we can help you..."></textarea>
            `;

            // Submit button
            const submitButton = document.createElement('button');
            submitButton.type = 'submit';
            submitButton.style.cssText = `
                background: rgba(255, 255, 255, 0.95);
                color: #333;
                border: none;
                padding: 12px 20px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: auto;
            `;
            submitButton.innerHTML = 'Send Message';

            // Add hover effect to submit button
            submitButton.addEventListener('mouseenter', () => {
                submitButton.style.background = 'rgba(255, 255, 255, 1)';
                submitButton.style.transform = 'translateY(-2px)';
            });

            submitButton.addEventListener('mouseleave', () => {
                submitButton.style.background = 'rgba(255, 255, 255, 0.95)';
                submitButton.style.transform = 'translateY(0)';
            });

            // Back button
            const backButton = document.createElement('button');
            backButton.type = 'button';
            backButton.style.cssText = `
                background: rgba(255, 255, 255, 0.2);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 10px;
            `;
            backButton.innerHTML = '← Back to Video';

            // Add hover effect to back button
            backButton.addEventListener('mouseenter', () => {
                backButton.style.background = 'rgba(255, 255, 255, 0.3)';
            });

            backButton.addEventListener('mouseleave', () => {
                backButton.style.background = 'rgba(255, 255, 255, 0.2)';
            });

            // Add click handler for back button
            backButton.addEventListener('click', () => {
                this.hideChatForm();
            });

            // Form submit handler
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitChatForm();
            });

            // Assemble form
            form.appendChild(nameField);
            form.appendChild(emailField);
            form.appendChild(messageField);
            form.appendChild(submitButton);

            // Assemble container
            this.chatFormContainer.appendChild(formHeader);
            this.chatFormContainer.appendChild(form);
            this.chatFormContainer.appendChild(backButton);

            this.widgetContainer.appendChild(this.chatFormContainer);
        }

        hideChatForm() {
            // Show video and buttons
            this.videoContainer.style.display = 'block';
            this.expandedButtonsContainer.style.display = 'flex';
            this.muteButton.style.display = 'flex';
            
            // Remove chat form
            if (this.chatFormContainer) {
                this.chatFormContainer.remove();
                this.chatFormContainer = null;
            }
            
            this.isChatFormVisible = false;
        }

        submitChatForm() {
            const name = document.getElementById('chat-name').value;
            const email = document.getElementById('chat-email').value;
            const message = document.getElementById('chat-message').value;

            if (!name || !email || !message) {
                alert('Please fill in all required fields.');
                return;
            }

            // Show loading state
            const submitButton = document.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = 'Sending...';
            submitButton.disabled = true;

            // Make API call to Laravel backend
            fetch(`${this.host}/api/chat/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    message: message,
                    client_domain: this.clientDomain
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('data', data);
                    // Store chat_id to localStorage
                    localStorage.setItem('bc_id', data.chat_id);
                    
                    // Store chat data for future use
                    this.currentChatId = data.chat_id;
                    this.currentContact = data.contact;
                    
                    // Show chat interface instead of hiding form
                    this.showChatInterface();
                } else {
                    // Error from server
                    alert('Error: ' + (data.message || 'Failed to send message. Please try again.'));
                }
            })
            .catch(error => {
                console.error('Error submitting chat form:', error);
                alert('Network error. Please check your connection and try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        }

        showChatInterface() {
            // Hide form and show chat interface
            if (this.chatFormContainer) {
                this.chatFormContainer.style.display = 'none';
            }
            
            // Create chat interface
            this.createChatInterface();
            
            // Check if we have messages from the response
            if (this.currentChat && this.currentChat.messages) {
                console.log('Using messages from response:', this.currentChat.messages);
                this.displayMessages(this.currentChat.messages);
            } else {
                // Load messages from API
                this.loadMessages();
            }
        }

        createChatInterface() {
            // Create chat interface container
            this.chatInterfaceContainer = document.createElement('div');
            this.chatInterfaceContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                z-index: 15;
            `;

            // Create chat header
            const chatHeader = document.createElement('div');
            chatHeader.style.cssText = `
                padding: 15px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            `;
            chatHeader.innerHTML = `
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold;">Chat Support</h3>
                    <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">We're here to help!</p>
                </div>
                <button id="back-to-video-btn" style="
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    padding: 6px 12px;
                    border-radius: 4px;
                    font-size: 11px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                ">← Back</button>
            `;

            // Create messages container
            this.messagesContainer = document.createElement('div');
            this.messagesContainer.style.cssText = `
                flex: 1;
                padding: 15px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;

            // Create input container
            const inputContainer = document.createElement('div');
            inputContainer.style.cssText = `
                padding: 15px 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
                display: flex;
                gap: 10px;
                align-items: flex-end;
            `;

            // Create message input
            this.messageInput = document.createElement('textarea');
            this.messageInput.style.cssText = `
                flex: 1;
                padding: 10px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                color: #333;
                resize: none;
                font-family: inherit;
                min-height: 40px;
                max-height: 100px;
            `;
            this.messageInput.placeholder = 'Type your message...';
            this.messageInput.rows = 1;

            // Auto-resize textarea
            this.messageInput.addEventListener('input', () => {
                this.messageInput.style.height = 'auto';
                this.messageInput.style.height = Math.min(this.messageInput.scrollHeight, 100) + 'px';
            });

            // Create send button
            const sendButton = document.createElement('button');
            sendButton.style.cssText = `
                background: rgba(255, 255, 255, 0.95);
                color: #333;
                border: none;
                padding: 10px 15px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s ease;
                white-space: nowrap;
            `;
            sendButton.innerHTML = 'Send';

            // Add hover effect to send button
            sendButton.addEventListener('mouseenter', () => {
                sendButton.style.background = 'rgba(255, 255, 255, 1)';
                sendButton.style.transform = 'translateY(-1px)';
            });

            sendButton.addEventListener('mouseleave', () => {
                sendButton.style.background = 'rgba(255, 255, 255, 0.95)';
                sendButton.style.transform = 'translateY(0)';
            });

            // Send message on button click
            sendButton.addEventListener('click', () => {
                this.sendMessage();
            });

            // Send message on Enter key (but allow Shift+Enter for new line)
            this.messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Assemble input container
            inputContainer.appendChild(this.messageInput);
            inputContainer.appendChild(sendButton);

            // Assemble chat interface
            this.chatInterfaceContainer.appendChild(chatHeader);
            this.chatInterfaceContainer.appendChild(this.messagesContainer);
            this.chatInterfaceContainer.appendChild(inputContainer);

            // Add back button functionality
            const backButton = chatHeader.querySelector('#back-to-video-btn');
            if (backButton) {
                backButton.addEventListener('click', () => {
                    this.hideChatInterface();
                });
            }

            this.widgetContainer.appendChild(this.chatInterfaceContainer);
        }

        loadMessages() {
            if (!this.currentChatId) {
                console.error('No chat ID available');
                return;
            }

            console.log('this.currentChatId', this.currentChatId);

            fetch(`${this.host}/api/chat/messages/${this.currentChatId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('data', data);
                        this.displayMessages(data.messages);
                    } else {
                        console.error('Failed to load messages:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
        }

        displayMessages(messages) {
            this.messagesContainer.innerHTML = '';
            
            messages.forEach(message => {
                const messageElement = this.createMessageElement(message);
                this.messagesContainer.appendChild(messageElement);
            });
            
            // Scroll to bottom
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }

        createMessageElement(message) {
            const messageDiv = document.createElement('div');
            const isUser = message.type === 'user';
            
            messageDiv.style.cssText = `
                display: flex;
                justify-content: ${isUser ? 'flex-end' : 'flex-start'};
                margin-bottom: 8px;
            `;

            const messageBubble = document.createElement('div');
            messageBubble.style.cssText = `
                max-width: 80%;
                padding: 10px 12px;
                border-radius: 12px;
                font-size: 14px;
                line-height: 1.4;
                word-wrap: break-word;
                background: ${isUser ? 'rgba(255, 255, 255, 0.95)' : 'rgba(255, 255, 255, 0.2)'};
                color: ${isUser ? '#333' : 'white'};
                backdrop-filter: blur(10px);
            `;
            
            messageBubble.textContent = message.message;
            messageDiv.appendChild(messageBubble);
            
            return messageDiv;
        }

        sendMessage() {
            const message = this.messageInput.value.trim();
            
            if (!message) {
                return;
            }

            // Clear input
            this.messageInput.value = '';
            this.messageInput.style.height = 'auto';

            // Add message to UI immediately (optimistic update)
            const tempMessage = {
                id: Date.now(),
                message: message,
                type: 'user',
                created_at: new Date().toISOString()
            };
            
            const messageElement = this.createMessageElement(tempMessage);
            this.messagesContainer.appendChild(messageElement);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;

            // Send to backend
            fetch(`${this.host}/api/chat/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    chat_id: this.currentChatId,
                    message: message,
                    type: 'user'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to send message:', data.message);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
        }

        hideChatInterface() {
            // Remove chat interface
            if (this.chatInterfaceContainer) {
                this.chatInterfaceContainer.remove();
                this.chatInterfaceContainer = null;
            }
            
            // Show video and buttons
            this.videoContainer.style.display = 'block';
            this.expandedButtonsContainer.style.display = 'flex';
            this.muteButton.style.display = 'flex';
            
            // Remove chat form if it exists
            if (this.chatFormContainer) {
                this.chatFormContainer.remove();
                this.chatFormContainer = null;
            }
            
            this.isChatFormVisible = false;
        }

        // Method to expand widget (can be called later when needed)
        expandWidget() {
            const expandedWidth = 350;
            const expandedHeight = expandedWidth / this.videoAspectRatio;
            
            this.widgetContainer.style.width = `${expandedWidth}px`;
            this.widgetContainer.style.height = `${expandedHeight}px`;
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
