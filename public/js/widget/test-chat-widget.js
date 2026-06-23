(function () {
  "use strict";

  const STORAGE_KEYS = {
    CHAT_ID: "bc_id",
    WIDGET_ID: "bw_id",
  };

  const WIDGET_ID = "boostly-chat-widget";
  const DEFAULT_HOST = "http://localhost:8000";
  const FALLBACK_AUDIO =
    "data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT";

  const ICONS = {
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
    minimize: `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
    </svg>`,
    close: `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>`,
  };

  function resolveHost(scriptTag, fallback) {
    if (scriptTag && scriptTag.dataset && scriptTag.dataset.host) {
      return scriptTag.dataset.host;
    }

    if (scriptTag && scriptTag.src) {
      try {
        return new URL(scriptTag.src).origin;
      } catch (error) {
        // Ignore invalid script URLs.
      }
    }

    return fallback;
  }

  function parsePx(value, fallback) {
    if (!value) {
      return fallback;
    }

    const parsed = parseInt(String(value).replace("px", ""), 10);
    return Number.isNaN(parsed) ? fallback : parsed;
  }

  function isExternalUrl(path) {
    return path.indexOf("http://") === 0 || path.indexOf("https://") === 0;
  }

  function resolveAssetUrl(host, assetPath) {
    if (!assetPath) {
      return null;
    }

    return isExternalUrl(assetPath) ? assetPath : host + "/storage/" + assetPath;
  }

  function resolveBackgroundStyle(host, styles, options) {
    options = options || {};
    const colorKey = options.colorKey || "widget_background_color_1";
    const color2Key = options.color2Key || "widget_background_color_2";
    const urlKey = options.urlKey || "widget_background_url";
    const fallbackColor = options.fallbackColor || "#FFFFFF";
    const includeGradient = options.includeGradient !== false;

    const color1 = styles[colorKey] || fallbackColor;
    const color2 = styles[color2Key] || null;
    const backgroundUrl = styles[urlKey] || null;

    if (backgroundUrl) {
      return "url(" + resolveAssetUrl(host, backgroundUrl) + ")";
    }

    if (includeGradient && color2) {
      return "linear-gradient(135deg, " + color1 + " 0%, " + color2 + " 100%)";
    }

    return color1;
  }

  function resolveLayerBackground(host, color, imagePath) {
    if (!imagePath) {
      return color;
    }

    return "url(" + resolveAssetUrl(host, imagePath) + "), " + color;
  }

  function jsonHeaders() {
    return {
      "Content-Type": "application/json",
      Accept: "application/json",
    };
  }

  function playAudio(src, volume) {
    const audio = new Audio(src);
    audio.volume = volume;
    audio.play().catch(function () {
      // Autoplay may be blocked by the browser.
    });
  }

  class ChatWidget {
    constructor() {
      this.scriptTag = document.currentScript;
      this.host = resolveHost(this.scriptTag, DEFAULT_HOST);
      this.clientDomain = window.location.protocol + "//" + window.location.host;
      this.clientUrl = this.clientDomain + window.location.pathname;
      this.widget = null;
      this.videoAspectRatio = 16 / 9;
      this.initialWidth = 150;
      this.isExpanded = false;
      this.isMuted = true;
      this.isChatFormVisible = false;
      this.chatExist = false;
      this.currentChat = null;
      this.currentChatId = null;
      this.currentContact = null;
      this.widgetId = null;
      this.echo = null;
      this.isWidgetClosed = false;
      this.icons = ICONS;
      this.isDragging = false;
      this.dragMoved = false;
      this.dragPointerId = null;
      this.dragListenersBound = false;
      this.reopenDragBound = false;
      this.dragElement = null;
    }
    getStyles() {
      return (this.widget && this.widget.style) ? this.widget.style : {};
    }

    hasMediaUrl() {
      return !!(this.widget && this.widget.media && this.widget.media.url);
    }

    getWidgetValue(key, fallback) {
      if (fallback === undefined) {
        fallback = "";
      }

      return (this.widget && this.widget[key] !== undefined && this.widget[key] !== null)
        ? this.widget[key]
        : fallback;
    }

    assetUrl(path) {
      return resolveAssetUrl(this.host, path);
    }

    getBackgroundStyle(options) {
      return resolveBackgroundStyle(this.host, this.getStyles(), options);
    }

    getFormFieldSettings() {
      return {
        showName: this.getWidgetValue("form_show_name", true),
        showEmail: this.getWidgetValue("form_show_email", true),
        showMessage: this.getWidgetValue("form_show_message", true),
      };
    }

    applyChatDimensions() {
      const styles = this.getStyles();
      const width = parsePx(styles.widget_width, 300);
      const height = parsePx(styles.widget_height, 500);

      this.widgetWidth = width;
      this.widgetHeight = height;
      this.widgetContainer.style.width = width + "px";
      this.widgetContainer.style.height = height + "px";
    }

    hideMediaUi() {
      if (this.videoContainer) {
        this.videoContainer.style.display = "none";
      }

      if (this.expandedButtonsContainer) {
        this.expandedButtonsContainer.style.display = "none";
      }
    }

    pauseVideo() {
      if (!this.videoElement) {
        return;
      }

      this.videoElement.muted = true;
      this.videoElement.pause();
      this.isMuted = true;

      if (this.muteButton) {
        this.muteButton.innerHTML = this.icons.mute;
      }
    }

    showCloseButtonInHeader() {
      if (!this.hoverButton) {
        return;
      }

      this.hoverButton.style.opacity = "1";
      this.hoverButton.style.pointerEvents = "auto";
      this.hoverButton.innerHTML = this.icons.close;
    }

    prepareForChatView(options) {
      options = options || {};

      this.hideMediaUi();
      this.pauseVideo();

      if (options.hideMuteButton && this.muteButton) {
        this.muteButton.style.display = "none";
      }

      this.showCloseButtonInHeader();
    }

    api(path, options) {
      options = options || {};
      const fetchOptions = {
        headers: jsonHeaders(),
      };

      Object.keys(options).forEach(function (key) {
        fetchOptions[key] = options[key];
      });

      return fetch(this.host + path, fetchOptions).then(function (response) {
        if (!response.ok) {
          throw new Error("Request failed: " + response.status);
        }

        return response.json();
      });
    }

    isDraggableState() {
      return !!(
        this.widgetContainer &&
        !this.isWidgetClosed &&
        this.widgetContainer.style.display !== "none"
      );
    }

    isDragBlockedTarget(target) {
      const controls = [
        this.muteButton,
        this.hoverButton,
        this.widgetCloseButton,
        this.actionButton,
        this.startChatButton,
        this.expandedButtonsContainer,
        this.messagesContainer,
      ].filter(Boolean);

      if (controls.some(function (control) {
        return control.contains(target);
      })) {
        return true;
      }

      return !!target.closest("input, textarea, button, a, select, label");
    }

    ensureElementPosition(element) {
      const rect = element.getBoundingClientRect();

      element.style.top = rect.top + "px";
      element.style.left = rect.left + "px";
      element.style.bottom = "auto";
      element.style.right = "auto";

      if (element === this.widgetContainer) {
        element.style.transform = "none";
      }
    }

    ensureWidgetPosition() {
      if (this.widgetContainer) {
        this.ensureElementPosition(this.widgetContainer);
      }
    }

    updateDragCursor() {
      if (this.widgetContainer && this.isDraggableState()) {
        this.widgetContainer.style.cursor = "grab";
        this.widgetContainer.style.touchAction = "none";
      }

      if (this.reopenButton && this.isWidgetClosed) {
        this.reopenButton.style.cursor = "grab";
        this.reopenButton.style.touchAction = "none";
      }
    }

    consumeDragClick() {
      if (!this.dragMoved) {
        return false;
      }

      this.dragMoved = false;
      return true;
    }

    bindDragListeners() {
      if (this.dragListenersBound || !this.widgetContainer) {
        return;
      }

      this.dragListenersBound = true;
      this.handleDragStart = this.onWidgetDragStart.bind(this);
      this.handleDragMove = this.onDragMove.bind(this);
      this.handleDragEnd = this.onDragEnd.bind(this);

      this.widgetContainer.addEventListener("pointerdown", this.handleDragStart);
      document.addEventListener("pointermove", this.handleDragMove);
      document.addEventListener("pointerup", this.handleDragEnd);
      document.addEventListener("pointercancel", this.handleDragEnd);
      this.updateDragCursor();
    }

    bindReopenDragListeners() {
      if (!this.reopenButton || this.reopenDragBound) {
        return;
      }

      this.reopenDragBound = true;
      this.handleReopenDragStart = this.onReopenDragStart.bind(this);

      this.reopenButton.addEventListener("pointerdown", this.handleReopenDragStart);
    }

    beginDrag(element, e) {
      this.ensureElementPosition(element);
      this.isDragging = true;
      this.dragMoved = false;
      this.dragElement = element;
      this.dragPointerId = e.pointerId;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;

      const rect = element.getBoundingClientRect();
      this.dragOffsetX = e.clientX - rect.left;
      this.dragOffsetY = e.clientY - rect.top;
      element.style.transition = "none";
      element.style.cursor = "grabbing";

      if (element.setPointerCapture) {
        element.setPointerCapture(e.pointerId);
      }
    }

    onWidgetDragStart(e) {
      if (!this.isDraggableState() || e.button !== 0) {
        return;
      }

      if (this.isDragBlockedTarget(e.target)) {
        return;
      }

      this.beginDrag(this.widgetContainer, e);
      e.preventDefault();
    }

    onReopenDragStart(e) {
      if (!this.reopenButton || e.button !== 0) {
        return;
      }

      this.beginDrag(this.reopenButton, e);
      e.preventDefault();
    }

    onDragMove(e) {
      if (!this.isDragging || !this.dragElement || e.pointerId !== this.dragPointerId) {
        return;
      }

      const width = this.dragElement.offsetWidth;
      const height = this.dragElement.offsetHeight;
      const peek = 48;
      const maxX = window.innerWidth - peek;
      const maxY = window.innerHeight - peek;
      const minX = peek - width;
      const minY = peek - height;
      const x = Math.max(minX, Math.min(e.clientX - this.dragOffsetX, maxX));
      const y = Math.max(minY, Math.min(e.clientY - this.dragOffsetY, maxY));

      this.dragElement.style.left = x + "px";
      this.dragElement.style.top = y + "px";

      if (Math.abs(e.clientX - this.dragStartX) > 5 || Math.abs(e.clientY - this.dragStartY) > 5) {
        this.dragMoved = true;
      }
    }

    onDragEnd(e) {
      if (!this.isDragging || !this.dragElement || e.pointerId !== this.dragPointerId) {
        return;
      }

      const dragElement = this.dragElement;
      this.isDragging = false;
      this.dragPointerId = null;
      this.dragElement = null;
      dragElement.style.transition = "";
      this.updateDragCursor();

      if (dragElement.releasePointerCapture && dragElement.hasPointerCapture(e.pointerId)) {
        dragElement.releasePointerCapture(e.pointerId);
      }
    }

    unbindDragListeners() {
      if (!this.dragListenersBound) {
        return;
      }

      this.widgetContainer.removeEventListener("pointerdown", this.handleDragStart);
      document.removeEventListener("pointermove", this.handleDragMove);
      document.removeEventListener("pointerup", this.handleDragEnd);
      document.removeEventListener("pointercancel", this.handleDragEnd);
      this.dragListenersBound = false;
    }


    /**
     * Track analytics events
     */
    async trackAnalytics(event, data = {}) {
      if (!this.widgetId) {
        console.warn('Cannot track analytics: widget ID not available');
        return;
      }

      try {
        const response = await fetch(`${this.host}/api/analytics/track`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            widget_id: this.widgetId,
            event: event,
            url: this.clientUrl,
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

    async init() {
      try {
        const bcId = localStorage.getItem(STORAGE_KEYS.CHAT_ID);
        this.widgetId = localStorage.getItem(STORAGE_KEYS.WIDGET_ID) || null;
        this.chatExist = bcId !== null;

        const data = await this.api("/verify", {
          method: "POST",
          body: JSON.stringify({
            client_domain: this.clientDomain,
            client_url: this.clientUrl,
            bc_id: this.chatExist ? bcId : null,
            bw_id: this.widgetId,
            timestamp: Date.now(),
          }),
        });

        this.widget = data.widget;
        this.widgetId = data.widget.id;
        localStorage.setItem(STORAGE_KEYS.WIDGET_ID, this.widgetId);

        if (data.chat && this.chatExist) {
          this.currentChatId = data.chat.id;
          this.currentContact = data.chat.contact_id;
          this.currentChat = data.chat;
          this.initializeWidget();
          this.initializeEcho();
          this.showChatInterface();
          return;
        }

        this.initializeWidget();
      } catch (error) {
        console.error("Widget verification failed:", error);
      }
    }

    initializeWidget() {
      // Create the widget container with video background
      this.createWidget();

      // Apply widget styles after creation to ensure they're applied
      this.applyWidgetStyles();

      // Track widget loaded event
      this.trackAnalytics('loaded', {
        widget_type: this.hasMediaUrl() ? 'video' : 'button',
        has_chat: this.chatExist
      });
    }

    applyWidgetStyles() {
      if (!this.widgetContainer || !this.widget || !this.widget.style) {
        return;
      }

      const widgetStyles = this.getStyles();
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetTextColor = widgetStyles.widget_text_color || "#000000";

      this.widgetContainer.style.borderRadius = widgetBorderRadius + "px";
      this.widgetContainer.style.color = widgetTextColor;
      this.widgetContainer.style.background = this.hasMediaUrl()
        ? this.getBackgroundStyle()
        : "transparent";

      if (this.videoContainer) {
        this.videoContainer.style.borderRadius = widgetBorderRadius + "px";
      }
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

          return;
        }

        this.websocket = new WebSocket(wsUrl);

        this.websocket.onopen = () => {

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

          // Only attempt reconnection if we have a chat ID and haven't exceeded max attempts
          if (
            this.currentChatId &&
            (!this.reconnectAttempts || this.reconnectAttempts < 3)
          ) {
            this.reconnectAttempts = (this.reconnectAttempts || 0) + 1;

            setTimeout(() => {
              if (this.currentChatId) {
                this.initializeEcho();
              }
            }, 5000);
          } else if (this.reconnectAttempts >= 3) {

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
      const soundUrl = this.widget && this.widget.media && this.widget.media.notification_sound_url
        ? this.assetUrl(this.widget.media.notification_sound_url)
        : FALLBACK_AUDIO;

      playAudio(soundUrl, 0.3);
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
      if (this.hasMediaUrl()) {
        this.createVideoWidget();
      } else {
        this.createButtonWidget();
      }
    }

    createVideoWidget() {
      const initialHeight = this.initialWidth / this.videoAspectRatio;
      const widgetStyles = this.getStyles();
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetTextColor = widgetStyles.widget_text_color || "#000000";
      const backgroundStyle = this.getBackgroundStyle();

      this.widgetWidth = parsePx(widgetStyles.widget_width, 350);
      this.initialWidth = 150;

      this.widgetContainer = document.createElement("div");
      this.widgetContainer.id = WIDGET_ID;

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
                cursor: grab;
                background: ${backgroundStyle};
                color: ${widgetTextColor};
            `;

      // Create video background div
      this.createVideoBackground();

      // Create mute button (initially hidden)
      this.createMuteButton();

      // Create hover button (initially hidden)
      this.createHoverButton();

      // Create widget close button (initially hidden)
      this.createWidgetCloseButton();

      // Add hover and click event listeners
      this.addEventListeners();
      this.bindDragListeners();

      // Add to page
      document.body.appendChild(this.widgetContainer);
    }

    createButtonWidget() {
      // Apply widget styles from widget style object
      const widgetStyles =
        this.getStyles();
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
      this.widgetWidth = parsePx(widgetStyles.widget_width, 300);
      this.widgetHeight = parsePx(widgetStyles.widget_height, 500);

      // Get button text
      const buttonText =
        this.widget && this.widget.button_text
          ? this.widget.button_text
          : "💬 Start Chat";

      // Create main widget container
      this.widgetContainer = document.createElement("div");
      this.widgetContainer.id = WIDGET_ID;

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
                cursor: grab;
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
        if (this.consumeDragClick()) {
          return;
        }

        this.showChatForm();
      });

      this.widgetContainer.appendChild(this.chatButton);

      // Add to page
      document.body.appendChild(this.widgetContainer);
      this.bindDragListeners();

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
        this.getStyles();
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
      if (this.hasMediaUrl()) {
        const videoUrl = this.assetUrl(this.widget.media.url);
        this.videoElement.src = videoUrl;

        // Get video aspect ratio when metadata is loaded
        this.videoElement.addEventListener("loadedmetadata", () => {
          this.videoAspectRatio =
            this.videoElement.videoWidth / this.videoElement.videoHeight;

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
      // Create hover button overlay (minimize button)
      this.hoverButton = document.createElement("div");
      this.hoverButton.style.cssText = `
                position: absolute;
                top: 10px;
                right: 50px;
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

    createWidgetCloseButton() {
      // Create widget close button (X) on the far right
      this.widgetCloseButton = document.createElement("div");
      this.widgetCloseButton.style.cssText = `
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
                z-index: 11;
            `;

      this.widgetCloseButton.innerHTML = this.icons.close;

      // Add click handler to close widget completely
      this.widgetCloseButton.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent widget click event
        this.closeWidget();
      });

      this.widgetContainer.appendChild(this.widgetCloseButton);
    }

    addEventListeners() {
      // Hover events to show/hide buttons
      this.widgetContainer.addEventListener("mouseenter", () => {
        this.hoverButton.style.opacity = "1";
        this.hoverButton.style.pointerEvents = "auto";
        this.hoverButton.style.transform = "scale(1.1)";
        this.muteButton.style.opacity = "1";
        this.muteButton.style.pointerEvents = "auto";
        if (this.widgetCloseButton) {
          this.widgetCloseButton.style.opacity = "1";
          this.widgetCloseButton.style.pointerEvents = "auto";
        }
      });

      this.widgetContainer.addEventListener("mouseleave", () => {
        // Keep buttons visible when expanded
        if (!this.isExpanded) {
          this.hoverButton.style.opacity = "0";
          this.hoverButton.style.pointerEvents = "none";
          this.hoverButton.style.transform = "scale(1)";
          this.muteButton.style.opacity = "0";
          this.muteButton.style.pointerEvents = "none";
          if (this.widgetCloseButton) {
            this.widgetCloseButton.style.opacity = "0";
            this.widgetCloseButton.style.pointerEvents = "none";
          }
        }
      });

      // Click event to expand video (only when collapsed)
      this.widgetContainer.addEventListener("click", () => {
        if (this.consumeDragClick()) {
          return;
        }

        if (!this.isExpanded) {
          this.expandVideo();
        }
      });

      // Add click handler for expand/collapse button
      this.hoverButton.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent widget click event
        
        // If chat interface is visible, close it
        if (this.chatInterfaceContainer) {
          this.hideChatInterface();
        } else if (this.chatFormContainer) {
          // If chat form is visible, close it
          this.hideChatInterface();
        } else if (this.isExpanded) {
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

      // Change expand button to minimize button
      this.hoverButton.innerHTML = this.icons.minimize;
      this.hoverButton.style.transform = "rotate(0deg)";

      // Make buttons always visible when expanded
      this.hoverButton.style.opacity = "1";
      this.hoverButton.style.pointerEvents = "auto";
      this.muteButton.style.opacity = "1";
      this.muteButton.style.pointerEvents = "auto";
      if (this.widgetCloseButton) {
        this.widgetCloseButton.style.opacity = "1";
        this.widgetCloseButton.style.pointerEvents = "auto";
      }

      // Restart video from beginning and unmute when expanded (only if video exists)
      if (this.videoElement) {
        this.videoElement.currentTime = 0;
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
      this.updateDragCursor();

      // Track widget opened event
      this.trackAnalytics('opened', {
        expanded_width: expandedWidth,
        expanded_height: expandedHeight,
        widget_type: this.hasMediaUrl() ? 'video' : 'button'
      });

    }

    collapseVideo() {
      const collapsedWidth = this.initialWidth;
      const collapsedHeight = collapsedWidth / this.videoAspectRatio;

      this.widgetContainer.style.width = `${collapsedWidth}px`;
      this.widgetContainer.style.height = `${collapsedHeight}px`;

      // Change collapse button back to expand button
      this.hoverButton.innerHTML = this.icons.expand;
      this.hoverButton.style.transform = "rotate(0deg)";

      // Hide buttons when collapsed
      this.hoverButton.style.opacity = "0";
      this.hoverButton.style.pointerEvents = "none";
      this.muteButton.style.opacity = "0";
      this.muteButton.style.pointerEvents = "none";
      if (this.widgetCloseButton) {
        this.widgetCloseButton.style.opacity = "0";
        this.widgetCloseButton.style.pointerEvents = "none";
      }

      // Mute video when collapsed (only if video exists)
      if (this.videoElement) {
        this.videoElement.muted = true;
        this.isMuted = true;
      }
      if (this.muteButton) {
        this.muteButton.innerHTML = this.icons.mute; // Update to muted icon
      }

      // Remove expanded buttons if they exist
      if (this.expandedButtonsContainer) {
        this.expandedButtonsContainer.remove();
        this.expandedButtonsContainer = null;
      }

      this.isExpanded = false;
      this.updateDragCursor();

    }

    playExpansionSound() {
      const soundUrl = this.widget && this.widget.media && this.widget.media.audio_url
        ? this.assetUrl(this.widget.media.audio_url)
        : FALLBACK_AUDIO;

      playAudio(soundUrl, 0.5);
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
          this.getStyles();
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
          
          // Track action button clicked event
          this.trackAnalytics('action_clicked', {
            action_url: this.widget.widget_action.url,
            action_text: this.widget.widget_action.button_text || "Take Action",
            widget_type: this.hasMediaUrl() ? 'video' : 'button'
          });
          
          if (this.widget.widget_action.url) {
            window.location.href = this.widget.widget_action.url;
          }
        });

        this.expandedButtonsContainer.appendChild(this.actionButton);
      }

      // Create Start Chat Button
      this.startChatButton = document.createElement("div");

      // Apply chat button styles from widget style object
      const chatButtonStyles =
        this.getStyles();
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

      //this.expandedButtonsContainer.appendChild(this.startChatButton);
      this.widgetContainer.appendChild(this.expandedButtonsContainer);
    }

    showChatForm() {
      if (this.chatButton) {
        this.chatButton.style.display = "none";
      }

      if (this.widgetWidth && this.widgetHeight) {
        this.widgetContainer.style.width = this.widgetWidth + "px";
        this.widgetContainer.style.height = this.widgetHeight + "px";
      }

      const bcId = localStorage.getItem(STORAGE_KEYS.CHAT_ID);
      const hasExistingChat = (this.chatExist && this.currentChatId) || bcId;

      if (hasExistingChat) {
        if (bcId && !this.currentChatId) {
          this.currentChatId = bcId;
          this.chatExist = true;
        }

        this.prepareForChatView();
        this.showChatInterface();
        return;
      }

      this.prepareForChatView({ hideMuteButton: true });
      this.createChatForm();
      this.isChatFormVisible = true;
      this.updateDragCursor();
    }

    createChatForm() {
      const widgetStyles = this.getStyles();
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const backgroundStyle = this.getBackgroundStyle({
        fallbackColor: "#667eea",
        includeGradient: false,
      });

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
                cursor: grab;
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

      const { showName, showEmail, showMessage } = this.getFormFieldSettings();

      let nameField = null;
      if (showName) {
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
      if (showEmail) {
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
      if (showMessage) {
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
      this.updateDragCursor();
    }

    submitChatForm() {
      const { showName, showEmail, showMessage } = this.getFormFieldSettings();

      const name = showName
        ? document.getElementById("chat-name")?.value || ""
        : "";
      const email = showEmail
        ? document.getElementById("chat-email")?.value || ""
        : "";
      const message = showMessage
        ? document.getElementById("chat-message")?.value || ""
        : "";

      const missingFields = [];
      if (showName && !name) missingFields.push("Name");
      if (showEmail && !email) missingFields.push("Email");
      if (showMessage && !message) missingFields.push("Message");

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

      if (showName) formData.name = name;
      if (showEmail) formData.email = email;
      if (showMessage) formData.message = message;

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

          if (data.success) {

            // Store chat_id to localStorage
            localStorage.setItem(STORAGE_KEYS.CHAT_ID, data.chat_id);

            // Store chat data for future use
            this.currentChatId = data.chat_id;
            this.currentContact = data.contact;

            // Track chat started event
            this.trackAnalytics('chat_started', {
              chat_id: data.chat_id,
              contact_id: data.contact,
              widget_type: this.hasMediaUrl() ? 'video' : 'button',
              form_data: {
                has_name: formData.name ? true : false,
                has_email: formData.email ? true : false,
                has_message: formData.message ? true : false
              }
            });

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
      this.applyChatDimensions();

      if (this.chatFormContainer) {
        this.chatFormContainer.style.display = "none";
      }

      this.prepareForChatView({ hideMuteButton: true });
      this.createChatInterface();

      if (!this.echo) {
        this.initializeEcho();
      }

      this.listenForMessages();

      if (this.currentChat && this.currentChat.messages) {
        this.displayMessages(this.currentChat.messages);
      } else {
        this.loadMessages();
      }

      this.updateDragCursor();
    }

    createChatInterface() {
      const widgetStyles = this.getStyles();
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const backgroundStyle = this.getBackgroundStyle({
        fallbackColor: "#667eea",
        includeGradient: false,
      });

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
      const chatHeaderStyles = this.getStyles();
      const chatHeaderBackgroundColor = chatHeaderStyles.chat_header_background_color || "rgba(0, 0, 0, 0.8)";
      const chatHeaderTextColor = chatHeaderStyles.chat_header_text_color || "white";
      const headerBackgroundStyle = resolveLayerBackground(
        this.host,
        chatHeaderBackgroundColor,
        chatHeaderStyles.chat_header_background_image
      );
      
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
                cursor: grab;
            `;
      
      // Get the last agent who responded
      const lastAgent = this.getLastAgent();
      const agentName = lastAgent ? lastAgent.name : (this.widget && this.widget.agent_name_placeholder ? this.widget.agent_name_placeholder : "Support Agent");
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
      const chatBodyStyles = this.getStyles();
      const chatBodyBackgroundColor = chatBodyStyles.chat_body_background_color || "transparent";
      const bodyBackgroundStyle = resolveLayerBackground(
        this.host,
        chatBodyBackgroundColor,
        chatBodyStyles.chat_body_background_image
      );
      
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
      const chatFooterStyles = this.getStyles();
      const chatFooterBackgroundColor = chatFooterStyles.chat_footer_background_color || "rgba(0, 0, 0, 0.8)";
      const footerBackgroundStyle = resolveLayerBackground(
        this.host,
        chatFooterBackgroundColor,
        chatFooterStyles.chat_footer_background_image
      );
      
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
      this.messageInput.style.resize = "none"; // Disable manual resize

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

      this.widgetContainer.appendChild(this.chatInterfaceContainer);

      // Trigger entrance animation
      setTimeout(() => {

        this.chatInterfaceContainer.style.opacity = "1";
        this.chatInterfaceContainer.style.transform = "scale(1) translateY(0)";

      }, 50);
    }

    loadMessages() {
      if (!this.currentChatId) {
        console.error("No chat ID available");
        return;
      }

      fetch(`${this.host}/api/chat/messages/${this.currentChatId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {

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
                flex-direction: column;
                align-items: ${isUser ? "flex-end" : "flex-start"};
                margin-bottom: 8px;
            `;

      // Get widget styles for chat bubble customization
      const widgetStyles = this.getStyles();
      const agentBubbleBackgroundColor = widgetStyles.widget_agent_buble_background_color || "rgba(255, 255, 255, 0.2)";
      const agentBubbleColor = widgetStyles.widget_agent_buble_color || "white";
      const userBubbleBackgroundColor = widgetStyles.widget_user_buble_background_color || "rgba(255, 255, 255, 0.95)";
      const userBubbleColor = widgetStyles.widget_user_buble_color || "#333";
      const chatBodyTextColor = widgetStyles.chat_body_text_color || "rgba(255, 255, 255, 0.7)";
      const widgetBorderRadius = widgetStyles.widget_border_radius || 12;

      const messageBubble = document.createElement("div");
      messageBubble.style.cssText = `
                max-width: 80%;
                padding: 10px 12px;
                border-radius: ${widgetBorderRadius}px;
                font-size: 14px;
                line-height: 1.4;
                word-wrap: break-word;
                background: ${isUser ? userBubbleBackgroundColor : agentBubbleBackgroundColor};
                color: ${isUser ? userBubbleColor : agentBubbleColor};
                backdrop-filter: blur(10px);
            `;

      messageBubble.textContent = message.message;
      messageDiv.appendChild(messageBubble);

      // Add timestamp below the message bubble
      if (message.created_at) {
        const timestamp = document.createElement("div");
        const messageDate = new Date(message.created_at);
        const timeString = messageDate.toLocaleTimeString([], { 
          hour: '2-digit', 
          minute: '2-digit',
          hour12: false 
        });
        
        timestamp.textContent = timeString;
        timestamp.style.cssText = `
                    font-size: 11px;
                    color: ${chatBodyTextColor};
                    margin-top: 2px;
                    opacity: 0.8;
                    font-weight: 400;
                `;
        
        messageDiv.appendChild(timestamp);
      }

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
          this.muteButton.style.display = "flex"; // Show mute button again
        }
      }

      // Restore buttons to appropriate state
      if (this.hoverButton) {
        if (this.isExpanded) {
          // Use minimize icon when expanded
          this.hoverButton.innerHTML = this.icons.minimize;
          // Keep buttons visible when expanded
          this.hoverButton.style.opacity = "1";
          this.hoverButton.style.pointerEvents = "auto";
          if (this.muteButton) {
            this.muteButton.style.opacity = "1";
            this.muteButton.style.pointerEvents = "auto";
          }
        } else {
          // Use expand icon when collapsed
          this.hoverButton.innerHTML = this.icons.expand;
          // Hide buttons when collapsed
          this.hoverButton.style.opacity = "0";
          this.hoverButton.style.pointerEvents = "none";
          if (this.muteButton) {
            this.muteButton.style.opacity = "0";
            this.muteButton.style.pointerEvents = "none";
          }
        }
      }

      // Remove chat form if it exists
      if (this.chatFormContainer) {
        this.chatFormContainer.remove();
        this.chatFormContainer = null;
      }

      this.isChatFormVisible = false;
      this.updateDragCursor();
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

      //this.widgetContainer.appendChild(this.chatButton);
    }

    toggleChat() {
      // Toggle chat functionality

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

    // Close widget completely
    closeWidget() {
      this.isWidgetClosed = true;
      
      // Collapse widget if expanded and mute video before closing
      if (this.isExpanded) {
        this.collapseVideo();
      }
      
      // Ensure video is muted when closed
      if (this.videoElement) {
        this.videoElement.muted = true;
        this.videoElement.pause();
        this.isMuted = true;
      }
      if (this.muteButton) {
        this.muteButton.innerHTML = this.icons.mute;
      }
      
      // Hide the main widget container
      if (this.widgetContainer) {
        this.widgetContainer.style.display = "none";
      }

      // Create and show reopen button
      this.createReopenButton();
    }

    // Create start button to reopen widget
    createReopenButton() {
      if (this.reopenButton) {
        this.reopenButton.style.display = "flex";
        this.bindReopenDragListeners();
        this.updateDragCursor();
        return;
      }

      // Get widget styles
      const widgetStyles = this.getStyles();
      const widgetBorderRadius = widgetStyles.widget_border_radius ?? 10;
      const widgetTextColor = widgetStyles.widget_text_color || "#000000";
      
      // Get background style
      let backgroundStyle;
      if (widgetStyles.widget_background_type === "gradient" && 
          widgetStyles.widget_background_color1 && 
          widgetStyles.widget_background_color2) {
        backgroundStyle = `linear-gradient(135deg, ${widgetStyles.widget_background_color1} 0%, ${widgetStyles.widget_background_color2} 100%)`;
      } else {
        const bgColor = widgetStyles.widget_background_color1 || "#ffffff";
        backgroundStyle = bgColor;
      }

      this.reopenButton = document.createElement("div");
      this.reopenButton.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: ${widgetBorderRadius}px;
                background: ${backgroundStyle};
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
                color: ${widgetTextColor};
                font-size: 16px;
                font-weight: bold;
                border: 1px solid rgba(255, 255, 255, 0.2);
            `;

      // Get button text from widget data
      const buttonText = this.widget && this.widget.button_text 
        ? this.widget.button_text 
        : "💬 Start Chat";

      this.reopenButton.innerHTML = `
                <div style="font-weight: bold; font-size: 16px;">
                    ${buttonText}
                </div>
            `;

      // Add hover effect
      this.reopenButton.addEventListener("mouseenter", () => {
        this.reopenButton.style.transform = "translateY(-2px)";
        this.reopenButton.style.boxShadow = "0 6px 20px rgba(0, 0, 0, 0.25)";
      });

      this.reopenButton.addEventListener("mouseleave", () => {
        this.reopenButton.style.transform = "translateY(0)";
        this.reopenButton.style.boxShadow = "0 4px 12px rgba(0, 0, 0, 0.15)";
      });

      // Add click handler to reopen widget
      this.reopenButton.addEventListener("click", () => {
        if (this.consumeDragClick()) {
          return;
        }

        this.reopenWidget();
      });

      document.body.appendChild(this.reopenButton);
      this.bindReopenDragListeners();
      this.updateDragCursor();
    }

    // Reopen widget
    reopenWidget() {
      this.isWidgetClosed = false;
      
      // Show the main widget container
      if (this.widgetContainer) {
        this.widgetContainer.style.display = "block";
        
        // Expand widget to maximized state
        if (!this.isExpanded) {
          this.expandVideo();
        }
        
        // Restart video from beginning and autoplay when reopened
        if (this.videoElement) {
          this.videoElement.currentTime = 0;
          this.videoElement.muted = false;
          this.videoElement.play().catch(error => {

            // If autoplay fails, keep it muted and try again
            this.videoElement.muted = true;
            this.videoElement.play();
          });
          this.isMuted = false;
        }
        if (this.muteButton) {
          this.muteButton.innerHTML = this.icons.unmute;
        }
      }

      // Hide reopen button
      if (this.reopenButton) {
        this.reopenButton.style.display = "none";
      }
    }

    // Cleanup method
    destroy() {
      this.unbindDragListeners();

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

      // Remove reopen button from DOM
      if (this.reopenButton) {
        this.reopenButton.remove();
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
