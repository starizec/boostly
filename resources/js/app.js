import './bootstrap';
import { createApp, h } from 'vue';
import ChatComponent from './components/ChatComponent.vue';

console.log('App.js loaded');
console.log('ChatComponent:', ChatComponent);

// Create Vue app with a render function
const app = createApp({
    render() {
        return h('div', [
            h(ChatComponent, {
                friend: this.friend,
                'current-user': this.currentUser
            })
        ]);
    },
    data() {
        return {
            friend: null,
            currentUser: null
        }
    },
    mounted() {
        // Get data from the page
        const friendData = document.querySelector('[data-friend]')?.dataset.friend;
        const userData = document.querySelector('[data-user]')?.dataset.user;
        
        if (friendData) {
            this.friend = JSON.parse(friendData);
        }
        if (userData) {
            this.currentUser = JSON.parse(userData);
        }
        
        console.log('Vue app mounted with data:', { friend: this.friend, currentUser: this.currentUser });
    }
});

console.log('Component registered');

// Mount the app
const mountElement = document.getElementById('app');
console.log('Mount element:', mountElement);

if (mountElement) {
    app.mount('#app');
    console.log('App mounted successfully');
} else {
    console.error('Mount element #app not found');
}
