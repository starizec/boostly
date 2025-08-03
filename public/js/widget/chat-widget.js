(function () {
    'use strict';

    class ChatWidget {
        constructor() {
            this.scriptTag = document.currentScript;
            this.host = `${window.location.protocol}//${window.location.host}`;
            this.widget = null;
            this.widgetStyle = null;
            this.chatId = null;
            this.isOpen = false;
            this.isInitialized = false;
            this.pollingInterval = null;
            this.messages = [];
            this.contact = null;
            
            this.init();
        }

        async init() {
            try {
                // Check if domain is allowed and widget is visible
                const response = await fetch(`${this.host}/verify`);
                const data = await response.json();
                
                if (data.allowed && data.visible) {
                    this.widget = data.widget;
                    this.widgetStyle = data.widgetStyle;
                    this.createWidget();
                    this.isInitialized = true;
                }
            } catch (error) {
                console.error('Error initializing chat widget:', error);
            }
        }

        createWidget() {
            // Create main widget container
            this.widgetContainer = document.createElement('div');
            this.widgetContainer.id = 'boostly-chat-widget';
            this.widgetContainer.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            `;

            // Create chat button
            this.createChatButton();
            
            // Create chat window
            this.createChatWindow();
            
            // Add to page
            document.body.appendChild(this.widgetContainer);
        }

        createChatButton() {
            this.chatButton = document.createElement('div');
            this.chatButton.id = 'boostly-chat-button';
            this.chatButton.style.cssText = `
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: ${this.widgetStyle?.start_button_background_color || '#007bff'};
                color: ${this.widgetStyle?.start_button_text_color || '#ffffff'};
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
                font-size: 24px;
                font-weight: bold;
            `;
            
            this.chatButton.innerHTML = '💬';
            
            // Add hover effects
            this.chatButton.addEventListener('mouseenter', () => {
                this.chatButton.style.transform = 'scale(1.1)';
                this.chatButton.style.background = this.widgetStyle?.start_button_hover_background_color || '#0056b3';
            });
            
            this.chatButton.addEventListener('mouseleave', () => {
                this.chatButton.style.transform = 'scale(1)';
                this.chatButton.style.background = this.widgetStyle?.start_button_background_color || '#007bff';
            });
            
            this.chatButton.addEventListener('click', () => this.toggleChat());
            
            this.widgetContainer.appendChild(this.chatButton);
        }

        createChatWindow() {
            this.chatWindow = document.createElement('div');
            this.chatWindow.id = 'boostly-chat-window';
            this.chatWindow.style.cssText = `
                position: fixed;
                bottom: 100px;
                right: 20px;
                width: 350px;
                height: 500px;
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                display: none;
                flex-direction: column;
                overflow: hidden;
                z-index: 10000;
                border: 1px solid #e1e5e9;
            `;
            
            // Create header
            this.createChatHeader();
            
            // Create body
            this.createChatBody();
            
            // Create footer
            this.createChatFooter();
            
            this.widgetContainer.appendChild(this.chatWindow);
        }

        createChatHeader() {
            this.chatHeader = document.createElement('div');
            this.chatHeader.style.cssText = `
                background: ${this.widgetStyle?.start_button_background_color || '#007bff'};
                color: ${this.widgetStyle?.start_button_text_color || '#ffffff'};
                padding: 16px 20px;
                font-weight: 600;
                font-size: 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            `;
            
            this.chatHeader.innerHTML = `
                <span>${this.widget?.button_text || 'Chat with us'}</span>
                <button id="boostly-close-chat" style="
                    background: none;
                    border: none;
                    color: inherit;
                    cursor: pointer;
                    font-size: 18px;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">✕</button>
            `;
            
            document.getElementById('boostly-close-chat').addEventListener('click', () => this.toggleChat());
            
            this.chatWindow.appendChild(this.chatHeader);
        }

        createChatBody() {
            this.chatBody = document.createElement('div');
            this.chatBody.style.cssText = `
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            `;
            
            // Create messages container
            this.messagesContainer = document.createElement('div');
            this.messagesContainer.id = 'boostly-messages';
            this.messagesContainer.style.cssText = `
                flex: 1;
                overflow-y: auto;
                padding: 20px;
                background: #f8f9fa;
            `;
            
            // Create initial form
            this.createInitialForm();
            
            this.chatBody.appendChild(this.messagesContainer);
            this.chatWindow.appendChild(this.chatBody);
        }

        createInitialForm() {
            this.initialForm = document.createElement('div');
            this.initialForm.id = 'boostly-initial-form';
            this.initialForm.style.cssText = `
                background: #ffffff;
                padding: 24px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            `;
            
            this.initialForm.innerHTML = `
                <h3 style="margin: 0 0 16px 0; color: #333; font-size: 18px;">Start a conversation</h3>
                <form id="boostly-contact-form">
                    <div style="margin-bottom: 16px;">
                        <input type="text" id="boostly-name" placeholder="Your name" required style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ddd;
                            border-radius: 6px;
                            font-size: 14px;
                            box-sizing: border-box;
                        ">
                    </div>
                    <div style="margin-bottom: 16px;">
                        <input type="email" id="boostly-email" placeholder="Your email" required style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ddd;
                            border-radius: 6px;
                            font-size: 14px;
                            box-sizing: border-box;
                        ">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <textarea id="boostly-initial-message" placeholder="How can we help you?" required style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ddd;
                            border-radius: 6px;
                            font-size: 14px;
                            min-height: 80px;
                            resize: vertical;
                            box-sizing: border-box;
                        "></textarea>
                    </div>
                    <button type="submit" style="
                        width: 100%;
                        padding: 12px;
                        background: ${this.widgetStyle?.start_button_background_color || '#007bff'};
                        color: ${this.widgetStyle?.start_button_text_color || '#ffffff'};
                        border: none;
                        border-radius: 6px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: background 0.3s ease;
                    ">Start Chat</button>
                </form>
            `;
            
            document.getElementById('boostly-contact-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.startChat();
            });
            
            this.messagesContainer.appendChild(this.initialForm);
        }

        createChatFooter() {
            this.chatFooter = document.createElement('div');
            this.chatFooter.style.cssText = `
                padding: 16px 20px;
                border-top: 1px solid #e1e5e9;
                background: #ffffff;
                display: none;
            `;
            
            this.chatFooter.innerHTML = `
                <div style="display: flex; gap: 12px; align-items: flex-end;">
                    <textarea id="boostly-message-input" placeholder="Type your message..." style="
                        flex: 1;
                        padding: 12px;
                        border: 1px solid #ddd;
                        border-radius: 20px;
                        font-size: 14px;
                        resize: none;
                        min-height: 20px;
                        max-height: 100px;
                        outline: none;
                        font-family: inherit;
                    "></textarea>
                    <button id="boostly-send-button" style="
                        background: ${this.widgetStyle?.start_button_background_color || '#007bff'};
                        color: ${this.widgetStyle?.start_button_text_color || '#ffffff'};
                        border: none;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: background 0.3s ease;
                    ">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            `;
            
            // Add event listeners
            const messageInput = document.getElementById('boostly-message-input');
            const sendButton = document.getElementById('boostly-send-button');
            
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            sendButton.addEventListener('click', () => this.sendMessage());
            
            this.chatWindow.appendChild(this.chatFooter);
        }

        async startChat() {
            const name = document.getElementById('boostly-name').value;
            const email = document.getElementById('boostly-email').value;
            const message = document.getElementById('boostly-initial-message').value;
            
            if (!name || !email || !message) {
                this.showError('Please fill in all fields');
                return;
            }
            
            try {
                const response = await fetch(`${this.host}/api/chat/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ name, email, message })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.chatId = data.chat_id;
                    this.contact = data.contact;
                    this.messages = [data.message];
                    
                    // Store chat ID in localStorage
                    localStorage.setItem('boostly_chat_id', this.chatId);
                    
                    // Show chat interface
                    this.showChatInterface();
                    
                    // Start polling for new messages
                    this.startPolling();
                    
                } else {
                    this.showError('Failed to start chat. Please try again.');
                }
                
            } catch (error) {
                console.error('Error starting chat:', error);
                this.showError('Failed to start chat. Please try again.');
            }
        }

        showChatInterface() {
            // Hide initial form
            this.initialForm.style.display = 'none';
            
            // Show footer
            this.chatFooter.style.display = 'block';
            
            // Display initial message
            this.displayMessages();
        }

        async sendMessage() {
            const messageInput = document.getElementById('boostly-message-input');
            const message = messageInput.value.trim();
            
            if (!message || !this.chatId) return;
            
            // Clear input
            messageInput.value = '';
            
            // Add message to local array immediately for instant feedback
            const tempMessage = {
                id: Date.now(),
                message: message,
                type: 'user',
                created_at: new Date().toISOString(),
                is_temp: true
            };
            
            this.messages.push(tempMessage);
            this.displayMessages();
            
            try {
                const response = await fetch(`${this.host}/api/chat/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        chat_id: this.chatId,
                        message: message,
                        type: 'user'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Replace temp message with real message
                    const tempIndex = this.messages.findIndex(m => m.is_temp);
                    if (tempIndex !== -1) {
                        this.messages[tempIndex] = data.message;
                    }
                } else {
                    this.showError('Failed to send message. Please try again.');
                }
                
            } catch (error) {
                console.error('Error sending message:', error);
                this.showError('Failed to send message. Please try again.');
            }
        }

        displayMessages() {
            this.messagesContainer.innerHTML = '';
            
            this.messages.forEach(message => {
                const messageElement = this.createMessageElement(message);
                this.messagesContainer.appendChild(messageElement);
            });
            
            // Scroll to bottom
            this.scrollToBottom();
        }

        createMessageElement(message) {
            const messageDiv = document.createElement('div');
            const isUser = message.type === 'user';
            
            messageDiv.style.cssText = `
                margin-bottom: 12px;
                display: flex;
                justify-content: ${isUser ? 'flex-end' : 'flex-start'};
            `;
            
            const messageContent = document.createElement('div');
            messageContent.style.cssText = `
                max-width: 80%;
                padding: 12px 16px;
                border-radius: 18px;
                background: ${isUser ? (this.widgetStyle?.start_button_background_color || '#007bff') : '#ffffff'};
                color: ${isUser ? (this.widgetStyle?.start_button_text_color || '#ffffff') : '#333333'};
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                word-wrap: break-word;
                font-size: 14px;
                line-height: 1.4;
            `;
            
            messageContent.textContent = message.message;
            
            const timeDiv = document.createElement('div');
            timeDiv.style.cssText = `
                font-size: 11px;
                color: #999;
                margin-top: 4px;
                text-align: ${isUser ? 'right' : 'left'};
            `;
            
            const messageTime = new Date(message.created_at);
            timeDiv.textContent = messageTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            messageContent.appendChild(timeDiv);
            messageDiv.appendChild(messageContent);
            
            return messageDiv;
        }

        scrollToBottom() {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }

        startPolling() {
            this.pollingInterval = setInterval(async () => {
                if (this.chatId) {
                    try {
                        const response = await fetch(`${this.host}/api/chat/messages/${this.chatId}`);
                        const data = await response.json();
                        
                        if (data.success && data.messages.length !== this.messages.length) {
                            this.messages = data.messages;
                            this.displayMessages();
                        }
                    } catch (error) {
                        console.error('Error polling messages:', error);
                    }
                }
            }, 3000); // Poll every 3 seconds
        }

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        }

        showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                background: #fee;
                color: #c33;
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 16px;
                font-size: 14px;
                border: 1px solid #fcc;
            `;
            errorDiv.textContent = message;
            
            this.messagesContainer.insertBefore(errorDiv, this.messagesContainer.firstChild);
            
            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        toggleChat() {
            this.isOpen = !this.isOpen;
            
            if (this.isOpen) {
                this.chatWindow.style.display = 'flex';
                this.chatButton.innerHTML = '✕';
                this.chatButton.style.background = '#dc3545';
                
                // Focus on input if chat is active
                if (this.chatId) {
                    setTimeout(() => {
                        document.getElementById('boostly-message-input')?.focus();
                    }, 100);
                }
            } else {
                this.chatWindow.style.display = 'none';
                this.chatButton.innerHTML = '💬';
                this.chatButton.style.background = this.widgetStyle?.start_button_background_color || '#007bff';
            }
        }

        // Check for existing chat on page load
        checkExistingChat() {
            const existingChatId = localStorage.getItem('boostly_chat_id');
            if (existingChatId) {
                this.chatId = existingChatId;
                this.loadExistingChat();
            }
        }

        async loadExistingChat() {
            try {
                const response = await fetch(`${this.host}/api/chat/status/${this.chatId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.contact = data.chat.contact;
                    await this.loadMessages();
                    this.showChatInterface();
                    this.startPolling();
                } else {
                    localStorage.removeItem('boostly_chat_id');
                }
            } catch (error) {
                console.error('Error loading existing chat:', error);
                localStorage.removeItem('boostly_chat_id');
            }
        }

        async loadMessages() {
            try {
                const response = await fetch(`${this.host}/api/chat/messages/${this.chatId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.messages = data.messages;
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }
    }

    // Initialize widget when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new ChatWidget();
        });
    } else {
        new ChatWidget();
    }

})();
