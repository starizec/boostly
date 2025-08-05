<template>
  <div class="chat-component">
    <div v-if="media && media.url" class="video-background-container" :class="{ 'expanded': isExpanded }">
      <button 
        class="expand-button"
        @click="toggleExpand"
        title="Expand video"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
        </svg>
      </button>
      <video 
        ref="videoRef"
        class="video-background"
        muted
        autoplay
        loop
        playsinline
        @loadedmetadata="onVideoLoad"
      >
        <source :src="`/storage/${media.url}`" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      
      <!-- Action buttons overlay - only visible when expanded -->
      <div v-if="isExpanded" class="action-buttons-overlay">
        <div class="action-buttons-container">
          <!-- Widget Action Button -->
          <button 
            v-if="widgetAction && widgetAction.url" 
            class="action-button widget-action-button"
            @click="handleWidgetAction"
          >
            {{ widgetAction.button_text || 'Action' }}
          </button>
          
          <!-- Start Chat Button -->
          <button 
            class="action-button start-chat-button"
            @click="startChat"
          >
            Start Chat
          </button>
        </div>
      </div>
    </div>
    <div v-else class="no-video-placeholder">
      No video available
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    widget: {
        type: Object,
        required: false,
        default: () => ({})
    },
    widgetAction: {
        type: Object,
        required: false,
        default: () => ({})
    },
    widgetStyle: {
        type: Object,
        required: false,
        default: () => ({})
    },
    media: {
        type: Object,
        required: false,
        default: () => ({})
    },
});

const videoRef = ref(null);
const isExpanded = ref(false);

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value;
};

const handleWidgetAction = () => {
    if (props.widgetAction && props.widgetAction.url) {
        window.open(props.widgetAction.url, '_blank');
    }
};

const startChat = () => {
    // Handle start chat functionality
    console.log('Starting chat...');
    // Add your chat initialization logic here
};

const onVideoLoad = () => {
    if (videoRef.value) {
        const video = videoRef.value;
        const aspectRatio = video.videoHeight / video.videoWidth;
        const container = video.parentElement;
        
        // Store aspect ratio for later use
        container.dataset.aspectRatio = aspectRatio;
        
        // Set initial size (150px width)
        updateContainerSize(container, aspectRatio, 150);
    }
};

const updateContainerSize = (container, aspectRatio, width) => {
    container.style.width = `${width}px`;
    container.style.height = `${width * aspectRatio}px`;
};

onMounted(() => {
    if (videoRef.value && videoRef.value.readyState >= 2) {
        onVideoLoad();
    }
});

// Watch for expand state changes
watch(isExpanded, (newValue) => {
    if (videoRef.value) {
        const container = videoRef.value.parentElement;
        const aspectRatio = parseFloat(container.dataset.aspectRatio) || 1;
        const width = newValue ? 300 : 150;
        updateContainerSize(container, aspectRatio, width);
    }
});
</script>

<style scoped>
.chat-component {
  /* Add your styles here */
}

.video-background-container {
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.expand-button {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.6);
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  padding: 4px;
  z-index: 10;
  transition: all 0.2s ease;
  opacity: 0;
}

.video-background-container:hover .expand-button {
  opacity: 1;
}

.expand-button:hover {
  background: rgba(0, 0, 0, 0.8);
  transform: scale(1.1);
}

.expand-button:active {
  transform: scale(0.95);
}

.video-background {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.no-video-placeholder {
  width: 150px;
  height: 150px;
  background-color: #f3f4f6;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  font-size: 14px;
}

.action-buttons-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
  padding: 20px 16px 16px;
  z-index: 5;
}

.action-buttons-container {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.action-button {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.widget-action-button {
  background-color: #3b82f6;
  color: white;
}

.widget-action-button:hover {
  background-color: #2563eb;
  transform: translateY(-1px);
}

.start-chat-button {
  background-color: #10b981;
  color: white;
}

.start-chat-button:hover {
  background-color: #059669;
  transform: translateY(-1px);
}

.action-button:active {
  transform: translateY(0);
}
</style>
