(function () {
    const scriptTag = document.currentScript;
    const host = `${window.location.protocol}//${window.location.host}`;
    let widget = null;

    document.addEventListener('DOMContentLoaded', () => {
        // First check if this domain is allowed
        fetch(`${host}/verify`)
            .then(response => response.json())
            .then(data => {
                if (data.allowed && data.visible) {
                    widget = data.widget;
                    widgetStyle = data.widgetStyle;
                    createWidget();
                }
            })
            .catch(error => console.error('Error checking domain:', error));
    });

    function createWidget() {
        const buttonContainer = document.createElement('div');

        buttonContainer.id = 'chat-widget-button';
        buttonContainer.style.position = 'fixed';
        buttonContainer.style.bottom = '10px';
        buttonContainer.style.right = '10px';
        buttonContainer.style.padding = '10px 20px';
        buttonContainer.style.borderRadius = (widgetStyle.start_button_border_radius || 10) + 'px';
        buttonContainer.style.overflow = 'hidden';
        buttonContainer.style.backgroundColor = widgetStyle.start_button_background_color;
        buttonContainer.style.color = widgetStyle.start_button_text_color;
        buttonContainer.style.cursor = 'pointer';

        buttonContainer.textContent = widget.button_text || 'Chat';
        buttonContainer.style.display = 'flex';
        buttonContainer.style.alignItems = 'center';
        buttonContainer.style.justifyContent = 'center';
        buttonContainer.style.fontFamily = 'Arial, sans-serif';
        buttonContainer.style.fontSize = '14px';
        
        buttonContainer.addEventListener('mouseover', function() {
            this.style.backgroundColor = widgetStyle.start_button_hover_background_color;
            this.style.color = widgetStyle.start_button_hover_text_color;
        });

        buttonContainer.addEventListener('mouseout', function() {
            this.style.backgroundColor = widgetStyle.start_button_background_color;
            this.style.color = widgetStyle.start_button_text_color; 
        });

        document.body.appendChild(buttonContainer);

        buttonContainer.addEventListener('click', function() {
            const widgetContainer = document.getElementById('chat-widget');
            
            if (widgetContainer) {
                // Return button to original state
                this.style.padding = '10px 20px';
                this.style.width = 'auto';
                this.style.height = 'auto';
                this.style.borderRadius = (widgetStyle.widget_border_radius || 10) + 'px';
                this.textContent = widget.start_button_text || 'Chat';
                this.style.fontSize = '14px';
                
                // Animate widget container before removal
                widgetContainer.style.transition = 'all 0.3s ease-in-out';
                widgetContainer.style.transform = 'translateY(20px)';
                widgetContainer.style.opacity = '0';
                
                // Remove after animation completes
                setTimeout(() => {
                    widgetContainer.remove();
                }, 300);
            } else {
                // Transform button to circle with close icon
                this.style.padding = '10px';
                this.style.width = '50px';
                this.style.height = '50px';
                this.style.borderRadius = '50%';
                this.textContent = '✕';
                this.style.fontSize = '20px';

                const widgetContainer = document.createElement('div');
                widgetContainer.id = 'chat-widget';
                widgetContainer.style.position = 'fixed';
                widgetContainer.style.bottom = '80px';
                widgetContainer.style.right = '10px';
                widgetContainer.style.width = '300px';
                widgetContainer.style.height = '533px';
                widgetContainer.style.borderRadius = '10px';
                widgetContainer.style.overflow = 'hidden';
                widgetContainer.style.backgroundColor = '#fff';
                
                // Add initial state for animation
                widgetContainer.style.opacity = '0';
                widgetContainer.style.transform = 'translateY(20px)';
                widgetContainer.style.transition = 'all 0.3s ease-in-out';

                document.body.appendChild(widgetContainer);

                // Trigger animation after a brief delay
                setTimeout(() => {
                    widgetContainer.style.opacity = '1';
                    widgetContainer.style.transform = 'translateY(0)';
                }, 10);

                const iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.border = 'none';
                iframe.src = `${host}/chat-widget`;
                widgetContainer.appendChild(iframe);
            }
        });
    }
})();
