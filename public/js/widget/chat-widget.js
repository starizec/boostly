(function () {
  "use strict";

  class ChatWidget {
    constructor() {
      this.host = "http://boostly.test";
      this.scriptTag = document.currentScript;
      this.clientDomain = `${window.location.protocol}//${window.location.host}`;
      this.clientUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname}`;
      this.widget = null;
      this.videoAspectRatio = 16 / 9; // Default aspect ratio
      this.initialWidth = 150; // Initial width in pixels (will be updated from widget settings)
      this.isExpanded = false; // Track expansion state
      this.isMuted = true; // Track mute state
      this.isChatFormVisible = false; // Track chat form visibility
      this.chatExist = false; // Track if chat exists in localStorage
      this.currentChat = null; // Store current chat data from response
      this.currentChatId = null; // Store current chat ID
      this.currentContact = null; // Store current contact ID
      this.widgetId = null; // Store widget ID
      this.echo = null; // Echo instance for real-time communication

      // Icon definitions using Heroicons (16px size)
      this.icons = {
        mute: `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path>
                </svg>`,
        unmute: `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                </svg>`,
        expand: `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                </svg>`,
        collapse: `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6L12 12L6 6"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L12 12L18 18"></path>
                </svg>`,
      };
    }

    async init() {
      try {
        // Check if chat exists in localStorage
        const bcId = localStorage.getItem("bc_id");
        this.widgetId = localStorage.getItem("bw_id") || null;
        this.chatExist = bcId !== null;

        if (this.chatExist) {
          console.log("Existing chat found with ID:", bcId);
        }

        // Call to verify endpoint
        const response = await fetch(`${this.host}/verify`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            client_domain: this.clientDomain,
            client_url: this.clientUrl,
            bc_id: this.chatExist ? localStorage.getItem("bc_id") : null,
            bw_id: this.widgetId,
            timestamp: Date.now(),
          }),
        });

        if (!response.ok) {
          throw new Error(`Verification failed: ${response.status}`);
        }

        const data = await response.json();
        console.log("Widget verification successful:", data);
        this.widget = data.widget;
        this.widgetId = data.widget.id;
        localStorage.setItem("bw_id", this.widgetId);

        // Check if response contains chat data and chat exists
        if (data.chat && this.chatExist) {
          console.log(
            "Existing chat found in response, opening chat interface"
          );
          console.log("Chat data:", data.chat);
          this.currentChatId = data.chat.id;
          this.currentContact = data.chat.contact_id;
          this.currentChat = data.chat; // Store the entire chat object

          // Initialize widget first
          console.log("Initializing widget...");
          this.initializeWidget();

          // Initialize Echo for real-time communication
          console.log("Initializing Echo...");
          this.initializeEcho();

          // For existing chats, show chat interface immediately without video expansion delay
          console.log(
            "Showing chat interface immediately for existing chat..."
          );
          this.showChatInterface();
          console.log("showChatInterface() called");
        } else {
          // Continue with normal widget initialization
          this.initializeWidget();
        }
      } catch (error) {
        console.error("Widget verification failed:", error);
        // Handle verification failure - maybe show an error message or disable widget
      }
    }

    initializeWidget() {
      // Create the widget container with video background
      this.createWidget();

      // Apply widget styles after creation to ensure they're applied
      this.applyWidgetStyles();
    }

    applyWidgetStyles() {
      if (!this.widgetContainer || !this.widget || !this.widget.style) {
        console.log(
          "Cannot apply widget styles: missing container or widget data"
        );
        console.log("Widget object:", this.widget);
        return;
      }

      // Check if this is a button widget (no media)
      const hasMedia =
        this.widget && this.widget.media && this.widget.media.url;

      const widgetStyles = this.widget.style;
      console.log("Raw widget styles object:", widgetStyles);
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetTextColor = widgetStyles.widget_text_color || "#000000";

      // Initialize backgroundStyle variable
      let backgroundStyle = "transparent";

      // Apply styles to widget container
      this.widgetContainer.style.borderRadius = `${widgetBorderRadius}px`;
      this.widgetContainer.style.color = widgetTextColor;

      // Only apply background styles for video widgets, not button widgets
      if (hasMedia) {
        const widgetBackgroundColor1 =
          widgetStyles.widget_background_color_1 || "#FFFFFF";
        const widgetBackgroundColor2 =
          widgetStyles.widget_background_color_2 || null;
        const widgetBackgroundUrl = widgetStyles.widget_background_url || null;

        // Set background based on available options
        backgroundStyle = widgetBackgroundColor1;
        if (widgetBackgroundUrl) {
          // Check if it's an external URL (starts with http/https) or local path
          if (
            widgetBackgroundUrl.startsWith("http://") ||
            widgetBackgroundUrl.startsWith("https://")
          ) {
            backgroundStyle = `url(${widgetBackgroundUrl})`;
          } else {
            backgroundStyle = `url(${this.host}/storage/${widgetBackgroundUrl})`;
          }
        } else if (widgetBackgroundColor2) {
          backgroundStyle = `linear-gradient(135deg, ${widgetBackgroundColor1} 0%, ${widgetBackgroundColor2} 100%)`;
        }

        this.widgetContainer.style.background = backgroundStyle;
      } else {
        // For button widgets, ensure no background is set
        this.widgetContainer.style.background = "transparent";
        backgroundStyle = "transparent";
      }

      // Apply border radius to video container as well
      if (this.videoContainer) {
        this.videoContainer.style.borderRadius = `${widgetBorderRadius}px`;
      }

      console.log("Widget styles applied:", {
        borderRadius: widgetBorderRadius,
        background: backgroundStyle,
        textColor: widgetTextColor,
      });
    }

    initializeEcho() {
      // Simple WebSocket implementation for real-time communication
      try {
        const wsProtocol =
          window.location.protocol === "https:" ? "wss:" : "ws:";
        const wsHost = this.host.replace("http://", "").replace("https://", "");
        const wsUrl = `${wsProtocol}//${wsHost}:8080`;

        // Prevent multiple connection attempts
        if (this.websocket && this.websocket.readyState !== WebSocket.CLOSED) {
          console.log("WebSocket already exists, skipping initialization");
          return;
        }

        this.websocket = new WebSocket(wsUrl);

        this.websocket.onopen = () => {
          console.log("WebSocket connected for real-time communication");
          this.reconnectAttempts = 0; // Reset reconnect attempts

          // Subscribe to the chat channel
          if (this.currentChatId) {
            this.websocket.send(
              JSON.stringify({
                event: "subscribe",
                channel: `chat.${this.currentChatId}`,
              })
            );
          }
        };

        this.websocket.onmessage = (event) => {
          try {
            const data = JSON.parse(event.data);
            if (data.event === "MessageSent" && data.data) {
              console.log("Received real-time message:", data.data);

              // Only display messages from agent (admin), not from user
              if (data.data.type === "agent") {
                this.displayNewMessage(data.data);
              }
            }
          } catch (error) {
            console.error("Error parsing WebSocket message:", error);
          }
        };

        this.websocket.onerror = (error) => {
          console.error("WebSocket error:", error);
        };

        this.websocket.onclose = () => {
          console.log("WebSocket connection closed");

          // Only attempt reconnection if we have a chat ID and haven't exceeded max attempts
          if (
            this.currentChatId &&
            (!this.reconnectAttempts || this.reconnectAttempts < 3)
          ) {
            this.reconnectAttempts = (this.reconnectAttempts || 0) + 1;
            console.log(
              `Attempting reconnection ${this.reconnectAttempts}/3...`
            );

            setTimeout(() => {
              if (this.currentChatId) {
                this.initializeEcho();
              }
            }, 5000);
          } else if (this.reconnectAttempts >= 3) {
            console.log(
              "Max reconnection attempts reached. Real-time messaging disabled."
            );
          }
        };
      } catch (error) {
        console.warn(
          "WebSocket not available for real-time communication:",
          error
        );
      }
    }

    listenForMessages() {
      if (!this.currentChatId) {
        console.warn("Cannot listen for messages: No chat ID");
        return;
      }

      console.log(
        "Listening for messages on chat channel:",
        this.currentChatId
      );

      // Try WebSocket first
      if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
        this.websocket.send(
          JSON.stringify({
            event: "subscribe",
            channel: `chat.${this.currentChatId}`,
          })
        );
      } else {
        // Fallback to polling if WebSocket is not available
        this.startPolling();
      }
    }

    startPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }

      console.log("Starting polling for new messages...");

      this.pollingInterval = setInterval(() => {
        this.checkForNewMessages();
      }, 2000); // Check every 2 seconds
    }

    checkForNewMessages() {
      if (!this.currentChatId) return;

      fetch(`${this.host}/api/chat/messages/${this.currentChatId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.messages) {
            // Get the last message timestamp we've seen
            const lastMessageTime = this.lastMessageTime || 0;

            // Check for new messages
            const newMessages = data.messages.filter((msg) => {
              const messageTime = new Date(msg.created_at).getTime();
              return messageTime > lastMessageTime && msg.type === "agent";
            });

            if (newMessages.length > 0) {
              console.log("Found new messages via polling:", newMessages);

              // Update last message time
              this.lastMessageTime = new Date(
                newMessages[newMessages.length - 1].created_at
              ).getTime();

              // Display new messages
              newMessages.forEach((message) => {
                this.displayNewMessage(message);
              });
            }
          }
        })
        .catch((error) => {
          console.error("Error polling for messages:", error);
        });
    }

    displayNewMessage(messageData) {
      // Create message element
      const messageElement = this.createMessageElement(messageData);

      // Add to messages container
      this.messagesContainer.appendChild(messageElement);

      // Scroll to bottom
      this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;

      // Play notification sound
      this.playNotificationSound();

      // Show notification if widget is collapsed
      if (!this.isExpanded) {
        this.showMessageNotification(messageData.message);
      }

      // Update agent name in header if this is an agent message
      if (messageData.type === "agent" && messageData.agent) {
        this.updateAgentNameInHeader(messageData.agent.name);
      }
    }

    playNotificationSound() {
      // Create audio element for notification sound
      const audio = new Audio();

      // Set audio source if available in widget data
      if (
        this.widget &&
        this.widget.media &&
        this.widget.media.notification_sound_url
      ) {
        audio.src = `${this.host}/storage/${this.widget.media.notification_sound_url}`;
      } else {
        // Fallback to a simple notification sound
        audio.src =
          "data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT";
      }

      audio.volume = 0.3; // Set volume to 30%
      audio.play().catch((error) => {
        console.log("Notification sound playback failed:", error);
      });
    }

    showMessageNotification(message) {
      // Create notification element
      const notification = document.createElement("div");
      notification.style.cssText = `
                position: absolute;
                top: -60px;
                right: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 10px 15px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                color: #333;
                font-size: 12px;
                max-width: 200px;
                word-wrap: break-word;
                z-index: 1000;
                animation: slideDown 0.3s ease;
            `;

      notification.innerHTML = `
                <div style="font-weight: bold; margin-bottom: 5px;">New Message</div>
                <div>${message.substring(0, 50)}${
        message.length > 50 ? "..." : ""
      }</div>
            `;

      // Add CSS animation
      const style = document.createElement("style");
      style.textContent = `
                @keyframes slideDown {
                    from { transform: translateY(-20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            `;
      document.head.appendChild(style);

      this.widgetContainer.appendChild(notification);

      // Remove notification after 5 seconds
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }

    createWidget() {
      // Check if widget has media
      const hasMedia =
        this.widget && this.widget.media && this.widget.media.url;

      if (hasMedia) {
        this.createVideoWidget();
      } else {
        this.createButtonWidget();
      }
    }

    createVideoWidget() {
      // Calculate height based on aspect ratio and initial width
      const initialHeight = this.initialWidth / this.videoAspectRatio;

      // Apply widget styles from widget style object
      const widgetStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetBackgroundColor1 =
        widgetStyles.widget_background_color_1 || "#FFFFFF";
      const widgetBackgroundColor2 =
        widgetStyles.widget_background_color_2 || null;
      const widgetBackgroundUrl = widgetStyles.widget_background_url || null;
      const widgetTextColor = widgetStyles.widget_text_color || "#000000";
      const widgetWidth = widgetStyles.widget_width || "350px";

      // Store the full widget width from settings
      this.widgetWidth = parseInt(widgetWidth.replace("px", ""));
      // Keep initial width as collapsed size (150px)
      this.initialWidth = 150;

      // Debug log to verify widget styles are being applied
      console.log("Widget styles:", {
        widgetStyles,
        widgetBorderRadius,
        widgetBackgroundColor1,
        widgetTextColor,
      });
      console.log("Full widget data:", this.widget);

      // Create main widget container
      this.widgetContainer = document.createElement("div");
      this.widgetContainer.id = "boostly-chat-widget";

      // Set background based on available options
      let backgroundStyle = widgetBackgroundColor1;
      if (widgetBackgroundUrl) {
        // Check if it's an external URL (starts with http/https) or local path
        if (
          widgetBackgroundUrl.startsWith("http://") ||
          widgetBackgroundUrl.startsWith("https://")
        ) {
          backgroundStyle = `url(${widgetBackgroundUrl})`;
        } else {
          backgroundStyle = `url(${this.host}/storage/${widgetBackgroundUrl})`;
        }
      } else if (widgetBackgroundColor2) {
        backgroundStyle = `linear-gradient(135deg, ${widgetBackgroundColor1} 0%, ${widgetBackgroundColor2} 100%)`;
      }

      this.widgetContainer.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                width: ${this.initialWidth}px;
                height: ${initialHeight}px;
                border-radius: ${widgetBorderRadius}px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                cursor: pointer;
                background: ${backgroundStyle};
                color: ${widgetTextColor};
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

    createButtonWidget() {
      // Apply widget styles from widget style object
      const widgetStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const startButtonBorderRadius =
        widgetStyles.start_button_border_radius ?? 5;
      const startButtonBackgroundColor =
        widgetStyles.start_button_background_color || "#007bff";
      const startButtonTextColor =
        widgetStyles.start_button_text_color || "#ffffff";
      const startButtonHoverBackgroundColor =
        widgetStyles.start_button_hover_background_color || "#0056b3";
      const startButtonHoverTextColor =
        widgetStyles.start_button_hover_text_color || "#ffffff";

      // Get widget dimensions from configuration
      const widgetWidth = widgetStyles.widget_width || "300px";
      const widgetHeight = widgetStyles.widget_height || "500px";

      // Store the widget dimensions for chat interface
      this.widgetWidth = parseInt(widgetWidth.replace("px", ""));
      this.widgetHeight = parseInt(widgetHeight.replace("px", ""));

      // Get button text
      const buttonText =
        this.widget && this.widget.button_text
          ? this.widget.button_text
          : "💬 Start Chat";

      // Create main widget container
      this.widgetContainer = document.createElement("div");
      this.widgetContainer.id = "boostly-chat-widget";

      this.widgetContainer.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                width: auto;
                height: auto;
                opacity: 0;
                transform: translateY(20px) scale(0.9);
                transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            `;

      // Create the chat button
      this.chatButton = document.createElement("div");
      this.chatButton.style.cssText = `
                background: ${startButtonBackgroundColor};
                color: ${startButtonTextColor};
                padding: 12px 20px;
                border-radius: ${startButtonBorderRadius}px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: bold;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border: none;
                outline: none;
                min-width: 120px;
                min-height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                white-space: nowrap;
            `;

      this.chatButton.innerHTML = buttonText;

      // Add hover effect
      this.chatButton.addEventListener("mouseenter", () => {
        this.chatButton.style.background = startButtonHoverBackgroundColor;
        this.chatButton.style.color = startButtonHoverTextColor;
        this.chatButton.style.transform = "translateY(-2px)";
      });

      this.chatButton.addEventListener("mouseleave", () => {
        this.chatButton.style.background = startButtonBackgroundColor;
        this.chatButton.style.color = startButtonTextColor;
        this.chatButton.style.transform = "translateY(0)";
      });

      // Add click handler - same behavior as video start chat
      this.chatButton.addEventListener("click", () => {
        this.showChatForm();
      });

      this.widgetContainer.appendChild(this.chatButton);

      // Add to page
      document.body.appendChild(this.widgetContainer);

      // Add CSS animation keyframes
      this.addButtonAnimationStyles();

      // Trigger entrance animation
      setTimeout(() => {
        this.widgetContainer.style.opacity = "1";
        this.widgetContainer.style.transform = "translateY(0) scale(1)";
      }, 100);
    }

    addButtonAnimationStyles() {
      // Check if animation styles already exist
      if (document.getElementById("boostly-button-animations")) {
        return;
      }

      // Create style element for button animations
      const style = document.createElement("style");
      style.id = "boostly-button-animations";
      style.textContent = `
                @keyframes buttonEntrance {
                    0% {
                        opacity: 0;
                        transform: translateY(20px) scale(0.9);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
            `;

      document.head.appendChild(style);
    }

    createVideoBackground() {
      // Get widget border radius for consistency
      const widgetStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;

      // Create video background container
      this.videoContainer = document.createElement("div");
      this.videoContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #000;
                border-radius: ${widgetBorderRadius}px;
                overflow: hidden;
            `;

      // Create video element
      this.videoElement = document.createElement("video");
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
        console.log("Loading video from:", videoUrl);

        // Get video aspect ratio when metadata is loaded
        this.videoElement.addEventListener("loadedmetadata", () => {
          this.videoAspectRatio =
            this.videoElement.videoWidth / this.videoElement.videoHeight;
          console.log("Video aspect ratio:", this.videoAspectRatio);

          // Only update widget dimensions if we're not showing chat interface
          // (chat interface should use its own configured dimensions)
          if (!this.chatInterfaceContainer) {
            // Update widget dimensions with correct aspect ratio
            const newHeight = this.initialWidth / this.videoAspectRatio;
            this.widgetContainer.style.height = `${newHeight}px`;
          }
        });
      } else {
        // Fallback to a default video or show error
        console.warn("No video media found in widget data");
        this.videoContainer.style.background =
          "linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
      }

      // Add video to container
      this.videoContainer.appendChild(this.videoElement);
      this.widgetContainer.appendChild(this.videoContainer);
    }

    createMuteButton() {
      // Create mute button
      this.muteButton = document.createElement("div");
      this.muteButton.style.cssText = `
                position: absolute;
                top: 10px;
                left: 10px;
                padding: 8px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
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

      this.muteButton.innerHTML = this.icons.mute; // Muted icon
      this.isMuted = true;

      // Add click handler for mute/unmute
      this.muteButton.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent widget click event
        this.toggleMute();
      });

      this.widgetContainer.appendChild(this.muteButton);
    }

    toggleMute() {
      if (!this.videoElement || !this.muteButton) return; // Only work if video exists

      if (this.isMuted) {
        this.videoElement.muted = false;
        this.muteButton.innerHTML = this.icons.unmute; // Unmuted icon
        this.isMuted = false;
      } else {
        this.videoElement.muted = true;
        this.muteButton.innerHTML = this.icons.mute; // Muted icon
        this.isMuted = true;
      }
    }

    createHoverButton() {
      // Create hover button overlay
      this.hoverButton = document.createElement("div");
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

      this.hoverButton.innerHTML = this.icons.expand;

      this.widgetContainer.appendChild(this.hoverButton);
    }

    addEventListeners() {
      // Hover events to show/hide buttons
      this.widgetContainer.addEventListener("mouseenter", () => {
        this.hoverButton.style.opacity = "1";
        this.hoverButton.style.pointerEvents = "auto";
        this.hoverButton.style.transform = "scale(1.1)";
        this.muteButton.style.opacity = "1";
        this.muteButton.style.pointerEvents = "auto";
      });

      this.widgetContainer.addEventListener("mouseleave", () => {
        this.hoverButton.style.opacity = "0";
        this.hoverButton.style.pointerEvents = "none";
        this.hoverButton.style.transform = "scale(1)";
        this.muteButton.style.opacity = "0";
        this.muteButton.style.pointerEvents = "none";
      });

      // Click event to expand video (only when collapsed)
      this.widgetContainer.addEventListener("click", () => {
        if (!this.isExpanded) {
          this.expandVideo();
        }
      });

      // Add click handler for expand/collapse button
      this.hoverButton.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent widget click event
        if (this.isExpanded) {
          this.collapseVideo();
        } else {
          this.expandVideo();
        }
      });
    }

    expandVideo() {
      const expandedWidth = this.widgetWidth; // Use the full widget width from settings
      const expandedHeight = expandedWidth / this.videoAspectRatio;

      this.widgetContainer.style.width = `${expandedWidth}px`;
      this.widgetContainer.style.height = `${expandedHeight}px`;

      // Change expand button to collapse button
      this.hoverButton.innerHTML = this.icons.collapse;
      this.hoverButton.style.transform = "rotate(0deg)";

      // Unmute video when expanded (only if video exists)
      if (this.videoElement) {
        this.videoElement.muted = false;
        this.isMuted = false;
      }
      if (this.muteButton) {
        this.muteButton.innerHTML = this.icons.unmute; // Unmuted icon
      }

      // Play sound if available
      this.playExpansionSound();

      // Show action and chat buttons
      this.createExpandedButtons();

      this.isExpanded = true;

      console.log("Video expanded to:", expandedWidth, "x", expandedHeight);
    }

    collapseVideo() {
      const collapsedWidth = this.initialWidth;
      const collapsedHeight = collapsedWidth / this.videoAspectRatio;

      this.widgetContainer.style.width = `${collapsedWidth}px`;
      this.widgetContainer.style.height = `${collapsedHeight}px`;

      // Change collapse button back to expand button
      this.hoverButton.innerHTML = this.icons.expand;
      this.hoverButton.style.transform = "rotate(0deg)";

      // Mute video when collapsed (only if video exists)
      if (this.videoElement) {
        this.videoElement.muted = true;
        this.isMuted = true;
      }

      // Remove expanded buttons if they exist
      if (this.expandedButtonsContainer) {
        this.expandedButtonsContainer.remove();
        this.expandedButtonsContainer = null;
      }

      this.isExpanded = false;

      console.log("Video collapsed to:", collapsedWidth, "x", collapsedHeight);
    }

    playExpansionSound() {
      // Create audio element for expansion sound
      const audio = new Audio();

      // Set audio source if available in widget data
      if (this.widget && this.widget.media && this.widget.media.audio_url) {
        audio.src = `${this.host}/storage/${this.widget.media.audio_url}`;
      } else {
        // Fallback to a simple notification sound (you can replace with your own sound file)
        audio.src =
          "data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT";
      }

      audio.volume = 0.5; // Set volume to 50%
      audio.play().catch((error) => {
        console.log("Audio playback failed:", error);
      });
    }

    createExpandedButtons() {
      // Create container for expanded buttons
      this.expandedButtonsContainer = document.createElement("div");
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
        this.actionButton = document.createElement("div");

        // Apply action button styles from widget style object
        const actionButtonStyles =
          this.widget && this.widget.style ? this.widget.style : {};
        const actionBorderRadius =
          actionButtonStyles.action_button_border_radius ?? 8;
        const actionBackgroundColor =
          actionButtonStyles.action_button_background_color ||
          "rgba(255, 255, 255, 0.95)";
        const actionTextColor =
          actionButtonStyles.action_button_text_color || "#333";
        const actionHoverBackgroundColor =
          actionButtonStyles.action_button_hover_background_color ||
          "rgba(255, 255, 255, 1)";
        const actionHoverTextColor =
          actionButtonStyles.action_button_hover_text_color || "#333";

        this.actionButton.style.cssText = `
                    background: ${actionBackgroundColor};
                    backdrop-filter: blur(10px);
                    padding: 8px 16px;
                    border-radius: ${actionBorderRadius}px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    font-weight: bold;
                    color: ${actionTextColor};
                    font-size: 14px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                `;

        const actionText =
          this.widget.widget_action.button_text || "Take Action";
        this.actionButton.innerHTML = actionText;

        // Add hover effect with custom colors
        this.actionButton.addEventListener("mouseenter", () => {
          this.actionButton.style.background = actionHoverBackgroundColor;
          this.actionButton.style.color = actionHoverTextColor;
          this.actionButton.style.transform = "translateY(-2px)";
        });

        this.actionButton.addEventListener("mouseleave", () => {
          this.actionButton.style.background = actionBackgroundColor;
          this.actionButton.style.color = actionTextColor;
          this.actionButton.style.transform = "translateY(0)";
        });

        // Add click handler for action button
        this.actionButton.addEventListener("click", (e) => {
          e.stopPropagation(); // Prevent widget click event
          if (this.widget.widget_action.url) {
            window.open(this.widget.widget_action.url, "_blank");
          }
        });

        this.expandedButtonsContainer.appendChild(this.actionButton);
      }

      // Create Start Chat Button
      this.startChatButton = document.createElement("div");

      // Apply chat button styles from widget style object
      const chatButtonStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const borderRadius = chatButtonStyles.chat_button_border_radius ?? 8;
      const backgroundColor =
        chatButtonStyles.chat_button_background_color ||
        "rgba(255, 255, 255, 0.95)";
      const textColor = chatButtonStyles.chat_button_text_color || "#333";
      const hoverBackgroundColor =
        chatButtonStyles.chat_button_hover_background_color ||
        "rgba(255, 255, 255, 1)";
      const hoverTextColor =
        chatButtonStyles.chat_button_hover_text_color || "#333";

      this.startChatButton.style.cssText = `
                background: ${backgroundColor};
                backdrop-filter: blur(10px);
                padding: 8px 16px;
                border-radius: ${borderRadius}px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.2);
                font-weight: bold;
                color: ${textColor};
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;

      const chatText =
        this.widget && this.widget.button_text
          ? this.widget.button_text
          : "💬 Start Chat";
      this.startChatButton.innerHTML = chatText;

      // Add hover effect with custom colors
      this.startChatButton.addEventListener("mouseenter", () => {
        this.startChatButton.style.background = hoverBackgroundColor;
        this.startChatButton.style.color = hoverTextColor;
        this.startChatButton.style.transform = "translateY(-2px)";
      });

      this.startChatButton.addEventListener("mouseleave", () => {
        this.startChatButton.style.background = backgroundColor;
        this.startChatButton.style.color = textColor;
        this.startChatButton.style.transform = "translateY(0)";
      });

      // Add click handler for start chat button
      this.startChatButton.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent widget click event
        this.showChatForm();
      });

      this.expandedButtonsContainer.appendChild(this.startChatButton);
      this.widgetContainer.appendChild(this.expandedButtonsContainer);
    }

    showChatForm() {
      // Hide chat button immediately when clicked (for button widgets without media)
      if (this.chatButton) {
        this.chatButton.style.display = "none";
      }

      // Resize widget container to use configured dimensions for chat interface
      if (this.widgetWidth && this.widgetHeight) {
        this.widgetContainer.style.width = `${this.widgetWidth}px`;
        this.widgetContainer.style.height = `${this.widgetHeight}px`;
      }

      // Check if there's a previous chat (either from localStorage or current session)
      const bcId = localStorage.getItem("bc_id");
      const hasExistingChat = (this.chatExist && this.currentChatId) || bcId;

      if (hasExistingChat) {
        console.log("Previous chat exists, opening chat interface directly");

        // If we have a chat ID from localStorage but not in current session, restore it
        if (bcId && !this.currentChatId) {
          this.currentChatId = bcId;
          this.chatExist = true;
        }

        // Hide video and buttons (only if they exist - for video widgets)
        if (this.videoContainer) {
          this.videoContainer.style.display = "none";
        }
        if (this.expandedButtonsContainer) {
          this.expandedButtonsContainer.style.display = "none";
        }

        // Mute and pause video when chat interface is shown (only if video exists)
        if (this.videoElement) {
          this.videoElement.muted = true;
          this.videoElement.pause();
          this.isMuted = true;
        }
        if (this.muteButton) {
          this.muteButton.innerHTML = this.icons.mute; // Muted icon
        }

        // Show chat interface (this will create the interface, initialize Echo, and load messages)
        this.showChatInterface();
      } else {
        // Hide video and buttons (only if they exist - for video widgets)
        if (this.videoContainer) {
          this.videoContainer.style.display = "none";
        }
        if (this.expandedButtonsContainer) {
          this.expandedButtonsContainer.style.display = "none";
        }

        // Mute and pause video when chat form is shown (only if video exists)
        if (this.videoElement) {
          this.videoElement.muted = true;
          this.videoElement.pause();
          this.isMuted = true;
        }
        if (this.muteButton) {
          this.muteButton.innerHTML = this.icons.mute; // Muted icon
        }

        // Create and show chat form
        this.createChatForm();

        this.isChatFormVisible = true;
      }
    }

    createChatForm() {
      // Get widget styles for background
      const widgetStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetBackgroundColor1 =
        widgetStyles.widget_background_color_1 || "#667eea";
      const widgetBackgroundUrl = widgetStyles.widget_background_url || null;

      // Set background based on available options
      let backgroundStyle = widgetBackgroundColor1;
      if (widgetBackgroundUrl) {
        // Check if it's an external URL (starts with http/https) or local path
        if (
          widgetBackgroundUrl.startsWith("http://") ||
          widgetBackgroundUrl.startsWith("https://")
        ) {
          backgroundStyle = `url(${widgetBackgroundUrl})`;
        } else {
          backgroundStyle = `url(${this.host}/storage/${widgetBackgroundUrl})`;
        }
      }

      // Create chat form container
      this.chatFormContainer = document.createElement("div");
      this.chatFormContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: ${backgroundStyle};
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                border-radius: ${widgetBorderRadius}px;
                padding: 15px;
                display: flex;
                flex-direction: column;
                box-sizing: border-box;
                z-index: 15;
                opacity: 0;
                transform: scale(0.95) translateY(10px);
                transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            `;

      // Create form header
      const formHeader = document.createElement("div");
      formHeader.style.cssText = `
                text-align: center;
                margin-bottom: 20px;
                color: white;
            `;
      // Get form title from widget data
      const formTitle =
        this.widget && this.widget.form_title
          ? this.widget.form_title
          : "Start a Conversation";

      formHeader.innerHTML = `
                <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold;">${formTitle}</h3>
                <p style="margin: 0; font-size: 14px; opacity: 0.9;">We'd love to hear from you!</p>
            `;

      // Create form
      const form = document.createElement("form");
      form.style.cssText = `
                display: flex;
                flex-direction: column;
                gap: 12px;
                flex: 1;
                min-height: 0;
                overflow: hidden;
            `;

      // Get form field visibility settings
      const formShowName =
        this.widget && this.widget.form_show_name !== undefined
          ? this.widget.form_show_name
          : true;
      const formShowEmail =
        this.widget && this.widget.form_show_email !== undefined
          ? this.widget.form_show_email
          : true;
      const formShowMessage =
        this.widget && this.widget.form_show_message !== undefined
          ? this.widget.form_show_message
          : true;

      // Name field (conditional)
      let nameField = null;
      if (formShowName) {
        nameField = document.createElement("div");
        nameField.innerHTML = `
                    <input type="text" id="chat-name" required style="
                        width: 100%;
                        padding: 10px;
                        border: none;
                        border-radius: 6px;
                        font-size: 14px;
                        background: rgba(255, 255, 255, 0.9);
                        backdrop-filter: blur(10px);
                        color: #333;
                        box-sizing: border-box;
                        outline: none;
                    " placeholder="Your name">
                `;
      }

      // Email field (conditional)
      let emailField = null;
      if (formShowEmail) {
        emailField = document.createElement("div");
        emailField.innerHTML = `
                    <input type="email" id="chat-email" required style="
                        width: 100%;
                        padding: 10px;
                        border: none;
                        border-radius: 6px;
                        font-size: 14px;
                        background: rgba(255, 255, 255, 0.9);
                        backdrop-filter: blur(10px);
                        color: #333;
                        box-sizing: border-box;
                        outline: none;
                    " placeholder="your.email@example.com">
                `;
      }

      // Message field (conditional)
      let messageField = null;
      if (formShowMessage) {
        messageField = document.createElement("div");
        messageField.innerHTML = `
                    <textarea id="chat-message" required rows="3" style="
                        width: 100%;
                        padding: 10px;
                        border: none;
                        border-radius: 6px;
                        font-size: 14px;
                        background: rgba(255, 255, 255, 0.9);
                        backdrop-filter: blur(10px);
                        color: #333;
                        box-sizing: border-box;
                        resize: vertical;
                        font-family: inherit;
                        outline: none;
                        min-height: 60px;
                    " placeholder="Tell us how we can help you..."></textarea>
                `;
      }

      // Submit button
      const submitButton = document.createElement("button");
      submitButton.type = "submit";
      submitButton.style.cssText = `
                background: rgba(255, 255, 255, 0.95);
                color: #333;
                border: none;
                padding: 10px 16px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: auto;
                outline: none;
            `;

      // Get button text from widget data
      const start_button_text =
        this.widget && this.widget.send_button_text
          ? this.widget.send_button_text
          : "Send Message";
      submitButton.innerHTML = start_button_text;

      // Add hover effect to submit button
      submitButton.addEventListener("mouseenter", () => {
        submitButton.style.background = "rgb(68, 40, 40)";
        submitButton.style.transform = "translateY(-2px)";
      });

      submitButton.addEventListener("mouseleave", () => {
        submitButton.style.background = "rgba(255, 255, 255, 0.95)";
        submitButton.style.transform = "translateY(0)";
      });

      // Back button
      const backButton = document.createElement("button");
      backButton.type = "button";
      backButton.style.cssText = `
                background: rgba(255, 255, 255, 0.2);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 8px;
                outline: none;
            `;
      backButton.innerHTML =
        this.widget && this.widget.back_button_text
          ? this.widget.back_button_text
          : "← Back";

      // Add hover effect to back button
      backButton.addEventListener("mouseenter", () => {
        backButton.style.background = "rgba(255, 255, 255, 0.3)";
      });

      backButton.addEventListener("mouseleave", () => {
        backButton.style.background = "rgba(255, 255, 255, 0.2)";
      });

      // Add click handler for back button
      backButton.addEventListener("click", () => {
        this.hideChatForm();
      });

      // Form submit handler
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        this.submitChatForm();
      });

      // Assemble form (only append fields that are enabled)
      if (nameField) form.appendChild(nameField);
      if (emailField) form.appendChild(emailField);
      if (messageField) form.appendChild(messageField);
      form.appendChild(submitButton);

      // Assemble container
      this.chatFormContainer.appendChild(formHeader);
      this.chatFormContainer.appendChild(form);
      this.chatFormContainer.appendChild(backButton);

      this.widgetContainer.appendChild(this.chatFormContainer);

      // Trigger entrance animation
      setTimeout(() => {
        this.chatFormContainer.style.opacity = "1";
        this.chatFormContainer.style.transform = "scale(1) translateY(0)";
      }, 50);
    }

    hideChatForm() {
      // Restore button widget size and show chat button (for button widgets without media)
      if (!this.videoContainer && this.chatButton) {
        this.widgetContainer.style.width = "auto";
        this.widgetContainer.style.height = "auto";
        this.chatButton.style.display = "flex"; // Show chat button again
      }

      // Show video and buttons (only if they exist - for video widgets)
      if (this.videoContainer) {
        this.videoContainer.style.display = "block";
      }
      if (this.expandedButtonsContainer) {
        this.expandedButtonsContainer.style.display = "flex";
      }

      // Resume video playback when returning to video interface (only if video exists)
      if (this.isExpanded && this.videoElement) {
        this.videoElement.muted = false;
        this.videoElement.play();
        this.isMuted = false;
        if (this.muteButton) {
          this.muteButton.innerHTML = this.icons.unmute; // Unmuted icon
        }
      }

      // Remove chat form
      if (this.chatFormContainer) {
        this.chatFormContainer.remove();
        this.chatFormContainer = null;
      }

      this.isChatFormVisible = false;
    }

    submitChatForm() {
      // Get form field visibility settings
      const formShowName =
        this.widget && this.widget.form_show_name !== undefined
          ? this.widget.form_show_name
          : true;
      const formShowEmail =
        this.widget && this.widget.form_show_email !== undefined
          ? this.widget.form_show_email
          : true;
      const formShowMessage =
        this.widget && this.widget.form_show_message !== undefined
          ? this.widget.form_show_message
          : true;

      // Get values from enabled fields only
      const name = formShowName
        ? document.getElementById("chat-name")?.value || ""
        : "";
      const email = formShowEmail
        ? document.getElementById("chat-email")?.value || ""
        : "";
      const message = formShowMessage
        ? document.getElementById("chat-message")?.value || ""
        : "";

      // Validate required fields
      const missingFields = [];
      if (formShowName && !name) missingFields.push("Name");
      if (formShowEmail && !email) missingFields.push("Email");
      if (formShowMessage && !message) missingFields.push("Message");

      if (missingFields.length > 0) {
        alert(
          `Please fill in all required fields: ${missingFields.join(", ")}`
        );
        return;
      }

      // Show loading state
      const submitButton = document.querySelector('button[type="submit"]');
      const originalText = submitButton.innerHTML;
      submitButton.innerHTML = "Sending...";
      submitButton.disabled = true;
      // Prepare data object with only enabled fields
      const formData = {
        client_domain: this.clientDomain,
        bw_id: this.widgetId,
        client_url: this.clientUrl,
      };

      if (formShowName) formData.name = name;
      if (formShowEmail) formData.email = email;
      if (formShowMessage) formData.message = message;

      console.log("Form data being sent:", JSON.stringify(formData));

      // Make API call to Laravel backend
      fetch(`${this.host}/api/chat/start`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(formData),
      })
        .then((response) => response.json())
        .then((data) => {
          console.log("data", data);
          if (data.success) {
            console.log("data", data);
            // Store chat_id to localStorage
            localStorage.setItem("bc_id", data.chat_id);

            // Store chat data for future use
            this.currentChatId = data.chat_id;
            this.currentContact = data.contact;

            // Show chat interface instead of hiding form
            this.showChatInterface();
          } else {
            // Error from server
            alert(
              "Error: " +
                (data.message || "Failed to send message. Please try again.")
            );
          }
        })
        .catch((error) => {
          console.error("Error submitting chat form:", error);
          alert("Network error. Please check your connection and try again.");
        })
        .finally(() => {
          // Reset button state
          submitButton.innerHTML = originalText;
          submitButton.disabled = false;
        });
    }

    showChatInterface() {
      console.log("showChatInterface() called");
      console.log("Current chat ID:", this.currentChatId);
      console.log("Current chat object:", this.currentChat);

      // Resize widget container to use configured dimensions for chat interface
      // Get dimensions directly from widget data to ensure they're available
      const widgetStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const widgetWidth = widgetStyles.widget_width || "300px";
      const widgetHeight = widgetStyles.widget_height || "500px";

      // Parse dimensions and apply them
      const width = parseInt(widgetWidth.replace("px", ""));
      const height = parseInt(widgetHeight.replace("px", ""));

      this.widgetContainer.style.width = `${width}px`;
      this.widgetContainer.style.height = `${height}px`;

      // Store dimensions for future use
      this.widgetWidth = width;
      this.widgetHeight = height;

      // Hide form and show chat interface
      if (this.chatFormContainer) {
        console.log("Hiding chat form container");
        this.chatFormContainer.style.display = "none";
      }

      // Hide video and buttons (only if they exist - for video widgets)
      if (this.videoContainer) {
        this.videoContainer.style.display = "none";
      }
      if (this.expandedButtonsContainer) {
        this.expandedButtonsContainer.style.display = "none";
      }

      // Mute and pause video when chat interface is shown (only if video exists)
      if (this.videoElement) {
        console.log("Muting and pausing video");
        this.videoElement.muted = true;
        this.videoElement.pause();
        this.isMuted = true;
      }
      if (this.muteButton) {
        this.muteButton.innerHTML = "🔇"; // Muted icon
      }

      // Create chat interface
      console.log("Creating chat interface...");
      this.createChatInterface();
      console.log("Chat interface created");

      // Initialize Echo if not already done
      if (!this.echo) {
        console.log("Initializing Echo...");
        this.initializeEcho();
      }

      // Start listening for real-time messages
      console.log("Starting to listen for messages...");
      this.listenForMessages();

      // Check if we have messages from the response
      if (this.currentChat && this.currentChat.messages) {
        console.log("Using messages from response:", this.currentChat.messages);
        this.displayMessages(this.currentChat.messages);
      } else {
        console.log("Loading messages from API...");
        // Load messages from API
        this.loadMessages();
      }
    }

    createChatInterface() {
      console.log("createChatInterface() called");
      console.log("Widget container exists:", !!this.widgetContainer);
      console.log("Widget object:", this.widget);

      // Get widget styles for background
      const widgetStyles =
        this.widget && this.widget.style ? this.widget.style : {};
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetBackgroundColor1 =
        widgetStyles.widget_background_color_1 || "#667eea";
      const widgetBackgroundUrl = widgetStyles.widget_background_url || null;

      // Set background based on available options
      let backgroundStyle = widgetBackgroundColor1;
      if (widgetBackgroundUrl) {
        // Check if it's an external URL (starts with http/https) or local path
        if (
          widgetBackgroundUrl.startsWith("http://") ||
          widgetBackgroundUrl.startsWith("https://")
        ) {
          backgroundStyle = `url(${widgetBackgroundUrl})`;
        } else {
          backgroundStyle = `url(${this.host}/storage/${widgetBackgroundUrl})`;
        }
      }

      // Create chat interface container
      this.chatInterfaceContainer = document.createElement("div");
      this.chatInterfaceContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: ${backgroundStyle};
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                border-radius: ${widgetBorderRadius}px;
                display: flex;
                flex-direction: column;
                z-index: 15;
                opacity: 0;
                transform: scale(0.95) translateY(10px);
                transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            `;

      // Create chat header
      const chatHeader = document.createElement("div");
      
      // Get chat header styles from widget style object
      const chatHeaderStyles = this.widget && this.widget.style ? this.widget.style : {};
      const chatHeaderBackgroundColor = chatHeaderStyles.chat_header_background_color || 'rgba(0, 0, 0, 0.8)';
      const chatHeaderTextColor = chatHeaderStyles.chat_header_text_color || 'white';
      const chatHeaderBackgroundImage = chatHeaderStyles.chat_header_background_image;
      
      // Build background style
      let headerBackgroundStyle = chatHeaderBackgroundColor;
      if (chatHeaderBackgroundImage) {
        let backgroundImageUrl = chatHeaderBackgroundImage;
        if (backgroundImageUrl.startsWith("http://") || backgroundImageUrl.startsWith("https://")) {
          backgroundImageUrl = `url(${backgroundImageUrl})`;
        } else {
          backgroundImageUrl = `url(${this.host}/storage/${backgroundImageUrl})`;
        }
        headerBackgroundStyle = `${backgroundImageUrl}, ${chatHeaderBackgroundColor}`;
      }
      
      chatHeader.style.cssText = `
                padding: 6px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                color: ${chatHeaderTextColor};
                background: ${headerBackgroundStyle};
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                border-radius: ${widgetBorderRadius}px ${widgetBorderRadius}px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            `;
      
      // Get the last agent who responded
      const lastAgent = this.getLastAgent();
      const agentName = lastAgent ? lastAgent.name : "Support Agent";
      const agentPlaceholder = this.widget && this.widget.agent_placeholder 
        ? this.widget.agent_placeholder 
        : "We're here to help!";
      
      chatHeader.innerHTML = `
                <div>
                <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8; color: ${chatHeaderTextColor};">${agentPlaceholder}</p>
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: ${chatHeaderTextColor};">${agentName}</h3>
                    
                </div>
                <button id="back-to-video-btn" style="
                    background: rgba(255, 255, 255, 0.2);
                    color: ${chatHeaderTextColor};
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    padding: 6px 12px;
                    border-radius: 4px;
                    font-size: 11px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                ">${
                  this.widget && this.widget.back_button_text
                    ? this.widget.back_button_text
                    : "← Back"
                }</button>
            `;

      // Create messages container
      this.messagesContainer = document.createElement("div");
      
      // Get chat body styles from widget style object
      const chatBodyStyles = this.widget && this.widget.style ? this.widget.style : {};
      const chatBodyBackgroundColor = chatBodyStyles.chat_body_background_color || 'transparent';
      const chatBodyBackgroundImage = chatBodyStyles.chat_body_background_image;
      
      // Build background style for chat body
      let bodyBackgroundStyle = chatBodyBackgroundColor;
      if (chatBodyBackgroundImage) {
        let backgroundImageUrl = chatBodyBackgroundImage;
        if (backgroundImageUrl.startsWith("http://") || backgroundImageUrl.startsWith("https://")) {
          backgroundImageUrl = `url(${backgroundImageUrl})`;
        } else {
          backgroundImageUrl = `url(${this.host}/storage/${backgroundImageUrl})`;
        }
        bodyBackgroundStyle = `${backgroundImageUrl}, ${chatBodyBackgroundColor}`;
      }
      
      this.messagesContainer.style.cssText = `
                flex: 1;
                padding: 15px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 10px;
                background: ${bodyBackgroundStyle};
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            `;

      // Create input container
      const inputContainer = document.createElement("div");
      
      // Get chat footer styles from widget style object
      const chatFooterStyles = this.widget && this.widget.style ? this.widget.style : {};
      const chatFooterBackgroundColor = chatFooterStyles.chat_footer_background_color || 'rgba(0, 0, 0, 0.8)';
      const chatFooterBackgroundImage = chatFooterStyles.chat_footer_background_image;
      
      // Build background style for chat footer
      let footerBackgroundStyle = chatFooterBackgroundColor;
      if (chatFooterBackgroundImage) {
        let backgroundImageUrl = chatFooterBackgroundImage;
        if (backgroundImageUrl.startsWith("http://") || backgroundImageUrl.startsWith("https://")) {
          backgroundImageUrl = `url(${backgroundImageUrl})`;
        } else {
          backgroundImageUrl = `url(${this.host}/storage/${backgroundImageUrl})`;
        }
        footerBackgroundStyle = `${backgroundImageUrl}, ${chatFooterBackgroundColor}`;
      }
      
      inputContainer.style.cssText = `
                padding: 15px 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
                display: flex;
                gap: 10px;
                align-items: flex-end;
                background: ${footerBackgroundStyle};
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                border-radius: 0 0 ${widgetBorderRadius}px ${widgetBorderRadius}px;
            `;

      // Create message input
      this.messageInput = document.createElement("textarea");
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
            `;
      this.messageInput.placeholder =
        this.widget && this.widget.message_input_placeholder
          ? this.widget.message_input_placeholder
          : "Type your message...";
      this.messageInput.rows = 1;

      // Auto-resize textarea
      this.messageInput.addEventListener("input", () => {
        this.messageInput.style.height = "auto";
        this.messageInput.style.height =
          Math.min(this.messageInput.scrollHeight, 100) + "px";
      });

      // Create send button
      const sendButton = document.createElement("button");
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
      sendButton.innerHTML =
        this.widget && this.widget.send_button_text
          ? this.widget.send_button_text
          : "Send";

      // Add hover effect to send button
      sendButton.addEventListener("mouseenter", () => {
        sendButton.style.background = "rgba(255, 255, 255, 1)";
        sendButton.style.transform = "translateY(-1px)";
      });

      sendButton.addEventListener("mouseleave", () => {
        sendButton.style.background = "rgba(255, 255, 255, 0.95)";
        sendButton.style.transform = "translateY(0)";
      });

      // Send message on button click
      sendButton.addEventListener("click", () => {
        this.sendMessage();
      });

      // Send message on Enter key (but allow Shift+Enter for new line)
      this.messageInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
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
      const backButton = chatHeader.querySelector("#back-to-video-btn");
      if (backButton) {
        backButton.addEventListener("click", () => {
          this.hideChatInterface();
        });
      }

      console.log("Appending chat interface container to widget container");
      this.widgetContainer.appendChild(this.chatInterfaceContainer);
      console.log("Chat interface container appended successfully");

      // Trigger entrance animation
      setTimeout(() => {
        console.log("Triggering entrance animation");
        this.chatInterfaceContainer.style.opacity = "1";
        this.chatInterfaceContainer.style.transform = "scale(1) translateY(0)";
        console.log("Entrance animation triggered");
      }, 50);
    }

    loadMessages() {
      if (!this.currentChatId) {
        console.error("No chat ID available");
        return;
      }

      console.log("this.currentChatId", this.currentChatId);

      fetch(`${this.host}/api/chat/messages/${this.currentChatId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            console.log("data", data);
            this.displayMessages(data.messages);
          } else {
            console.error("Failed to load messages:", data.message);
          }
        })
        .catch((error) => {
          console.error("Error loading messages:", error);
        });
    }

    displayMessages(messages) {
      this.messagesContainer.innerHTML = "";

      messages.forEach((message) => {
        const messageElement = this.createMessageElement(message);
        this.messagesContainer.appendChild(messageElement);
      });

      // Set the last message time for polling
      if (messages.length > 0) {
        this.lastMessageTime = new Date(
          messages[messages.length - 1].created_at
        ).getTime();
      }

      // Update agent name in header based on last agent message
      const lastAgent = this.getLastAgent();
      if (lastAgent && this.chatInterfaceContainer) {
        this.updateAgentNameInHeader(lastAgent.name);
      }

      // Scroll to bottom
      this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    createMessageElement(message) {
      const messageDiv = document.createElement("div");
      const isUser = message.type === "user";

      messageDiv.style.cssText = `
                display: flex;
                justify-content: ${isUser ? "flex-end" : "flex-start"};
                margin-bottom: 8px;
            `;

      const messageBubble = document.createElement("div");
      messageBubble.style.cssText = `
                max-width: 80%;
                padding: 10px 12px;
                border-radius: 12px;
                font-size: 14px;
                line-height: 1.4;
                word-wrap: break-word;
                background: ${
                  isUser
                    ? "rgba(255, 255, 255, 0.95)"
                    : "rgba(255, 255, 255, 0.2)"
                };
                color: ${isUser ? "#333" : "white"};
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
      this.messageInput.value = "";
      this.messageInput.style.height = "auto";

      // Add message to UI immediately (optimistic update)
      const tempMessage = {
        id: Date.now(),
        message: message,
        type: "user",
        created_at: new Date().toISOString(),
      };

      const messageElement = this.createMessageElement(tempMessage);
      this.messagesContainer.appendChild(messageElement);
      this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;

      // Send to backend
      fetch(`${this.host}/api/chat/message`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          chat_id: this.currentChatId,
          message: message,
          type: "user",
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (!data.success) {
            console.error("Failed to send message:", data.message);
          }
        })
        .catch((error) => {
          console.error("Error sending message:", error);
        });
    }

    hideChatInterface() {
      // Remove chat interface
      if (this.chatInterfaceContainer) {
        this.chatInterfaceContainer.remove();
        this.chatInterfaceContainer = null;
      }

      // Restore button widget size and show chat button (for button widgets without media)
      if (!this.videoContainer && this.chatButton) {
        this.widgetContainer.style.width = "auto";
        this.widgetContainer.style.height = "auto";
        this.chatButton.style.display = "flex"; // Show chat button again
      }

      // Show video and buttons (only if they exist - for video widgets)
      if (this.videoContainer) {
        this.videoContainer.style.display = "block";
      }
      if (this.expandedButtonsContainer) {
        this.expandedButtonsContainer.style.display = "flex";
      }

      // Resume video playback when returning to video interface (only if video exists)
      if (this.isExpanded && this.videoElement) {
        this.videoElement.muted = false;
        this.videoElement.play();
        this.isMuted = false;
        if (this.muteButton) {
          this.muteButton.innerHTML = this.icons.unmute; // Unmuted icon
        }
      }

      // Remove chat form if it exists
      if (this.chatFormContainer) {
        this.chatFormContainer.remove();
        this.chatFormContainer = null;
      }

      this.isChatFormVisible = false;
    }

    // Method to expand widget (can be called later when needed)
    expandWidget() {
      const expandedWidth = this.widgetWidth;
      const expandedHeight = expandedWidth / this.videoAspectRatio;

      this.widgetContainer.style.width = `${expandedWidth}px`;
      this.widgetContainer.style.height = `${expandedHeight}px`;
      this.createChatButton();
    }

    createChatButton() {
      // Create chat button overlay
      this.chatButton = document.createElement("div");
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
      const buttonText =
        this.widget && this.widget.button_text
          ? this.widget.button_text
          : "💬 Start Chat";

      this.chatButton.innerHTML = `
                <div style="font-weight: bold; color: #333; font-size: 16px;">
                    ${buttonText}
                </div>
            `;

      // Add hover effect
      this.chatButton.addEventListener("mouseenter", () => {
        this.chatButton.style.background = "rgba(255, 255, 255, 1)";
        this.chatButton.style.transform = "translateY(-2px)";
      });

      this.chatButton.addEventListener("mouseleave", () => {
        this.chatButton.style.background = "rgba(255, 255, 255, 0.9)";
        this.chatButton.style.transform = "translateY(0)";
      });

      // Add click handler
      this.chatButton.addEventListener("click", () => {
        this.toggleChat();
      });

      this.widgetContainer.appendChild(this.chatButton);
    }

    toggleChat() {
      // Toggle chat functionality
      console.log("Chat button clicked!");
      // You can expand this to show/hide chat interface
    }

    // Helper method to get the last agent who responded
    getLastAgent() {
      if (!this.currentChat || !this.currentChat.messages) {
        return null;
      }

      // Find the last message from an agent
      for (let i = this.currentChat.messages.length - 1; i >= 0; i--) {
        const message = this.currentChat.messages[i];
        if (message.type === "agent" && message.agent) {
          return message.agent;
        }
      }

      return null;
    }

    // Helper method to update agent name in chat header
    updateAgentNameInHeader(agentName) {
      if (!this.chatInterfaceContainer) {
        return;
      }

      const chatHeader = this.chatInterfaceContainer.querySelector('h3');
      if (chatHeader) {
        chatHeader.textContent = agentName;
      }
    }

    // Cleanup method
    destroy() {
      // Clear polling interval
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }

      // Close WebSocket connection
      if (this.websocket) {
        this.websocket.close();
        this.websocket = null;
      }

      // Remove widget from DOM
      if (this.widgetContainer) {
        this.widgetContainer.remove();
      }
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      new ChatWidget().init();
    });
  } else {
    new ChatWidget().init();
  }
})();
