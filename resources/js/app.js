import './bootstrap';
import { createApp, h } from 'vue';
import ChatComponent from './components/ChatComponent.vue';

// Create Vue app with a render function
const app = createApp({
    render() {
        return h('div', [
            h(ChatComponent, {
                widget: this.widget,
                'widget-action': this.widgetAction,
                'widget-style': this.widgetStyle,
                media: this.media
            })
        ]);
    },
    data() {
        return {
            widget: null,
            widgetAction: null,
            widgetStyle: null,
            media: null
        }
    },
    mounted() {
        const widgetData = document.querySelector('[data-widget]')?.dataset.widget;
        const widgetActionData = document.querySelector('[data-widget-action]')?.dataset.widgetAction;
        const widgetStyleData = document.querySelector('[data-widget-style]')?.dataset.widgetStyle;
        const mediaData = document.querySelector('[data-media]')?.dataset.media;

        if (widgetData) {
            try {
                this.widget = JSON.parse(widgetData);
            } catch (e) {
                console.error('Failed to parse widget data:', e);
            }
        } else {
            console.warn('No widget data found in DOM');
        }
        
        if (widgetActionData) {
            try {
                this.widgetAction = JSON.parse(widgetActionData);
            } catch (e) {
                console.error('Failed to parse widgetAction data:', e);
            }
        } else {
            console.warn('No widgetAction data found in DOM');
        }
        
        if (widgetStyleData) {
            try {
                this.widgetStyle = JSON.parse(widgetStyleData);
            } catch (e) {
                console.error('Failed to parse widgetStyle data:', e);
            }
        } else {
            console.warn('No widgetStyle data found in DOM');
        }
        
        if (mediaData) {
            try {
                this.media = JSON.parse(mediaData);
            } catch (e) {
                console.error('Failed to parse media data:', e);
            }
        } else {
            console.warn('No media data found in DOM');
        }
    }
});


// Mount the app
const mountElement = document.getElementById('app');

if (mountElement) {
    app.mount('#app');
} else {
    console.error('Mount element #app not found');
}
