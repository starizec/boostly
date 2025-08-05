<template>
    <div class="chat-container">
        <div class="chat-header">
            <h3 class="text-lg font-semibold">Chat with {{ friend?.name || 'Support' }}</h3>
        </div>
        
        <div class="chat-messages">
            <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                No messages yet. Start the conversation!
            </div>
            <div
                v-else
                v-for="message in messages"
                :key="message.id"
                class="message"
                :class="message.type === 'user' ? 'message-user' : 'message-friend'"
            >
                <div class="message-content">
                    {{ message.message }}
                </div>
            </div>
        </div>
        
        <div class="chat-input">
            <input
                type="text"
                v-model="newMessage"
                @keyup.enter="sendMessage"
                placeholder="Type a message..."
                class="w-full px-3 py-2 border rounded-lg"
                :disabled="sending"
            />
            <button
                @click="sendMessage"
                class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg w-full"
                :disabled="sending || !newMessage.trim()"
            >
                <span v-if="sending">Sending...</span>
                <span v-else>Send Message</span>
            </button>
        </div>
        
        <div v-if="error" class="mt-2 p-2 bg-red-100 text-red-700 rounded text-sm">
            {{ error }}
        </div>
    </div>
</template>
 
<script setup>
import axios from "axios";
import { onMounted, ref } from "vue";

const props = defineProps({
    friend: {
        type: Object,
        required: true,
    },
    currentUser: {
        type: Object,
        required: true,
    },
});

const messages = ref([]);
const newMessage = ref("");
const sending = ref(false);
const error = ref("");

const sendMessage = async () => {
    if (newMessage.value.trim() === "" || sending.value) return;
    
    try {
        sending.value = true;
        error.value = "";
        
        // Add message to local state immediately for better UX
        const userMessage = {
            id: Date.now(),
            message: newMessage.value,
            type: 'user',
            timestamp: new Date()
        };
        messages.value.push(userMessage);
        
        // Send to backend
        const response = await axios.post('/api/chat/message', {
            message: newMessage.value,
            user_id: props.currentUser.id,
            friend_id: props.friend.id
        });
        
        if (response.data.success) {
            // Add response message if any
            if (response.data.reply) {
                messages.value.push({
                    id: Date.now() + 1,
                    message: response.data.reply,
                    type: 'friend',
                    timestamp: new Date()
                });
            }
        }
        
        newMessage.value = "";
    } catch (err) {
        console.error('Failed to send message:', err);
        error.value = 'Failed to send message. Please try again.';
    } finally {
        sending.value = false;
    }
};

onMounted(() => {
    console.log('ChatComponent mounted with:', {
        friend: props.friend,
        currentUser: props.currentUser
    });
});
</script>

<style scoped>
.chat-container {
    max-width: 600px;
    margin: 0 auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.chat-header {
    background: #f3f4f6;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.chat-messages {
    height: 400px;
    overflow-y: auto;
    padding: 1rem;
    background: white;
}

.message {
    margin-bottom: 1rem;
    display: flex;
}

.message-user {
    justify-content: flex-end;
}

.message-friend {
    justify-content: flex-start;
}

.message-content {
    max-width: 70%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    word-wrap: break-word;
}

.message-user .message-content {
    background: #3b82f6;
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message-friend .message-content {
    background: #f3f4f6;
    color: #374151;
    border-bottom-left-radius: 0.25rem;
}

.chat-input {
    padding: 1rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}
</style>