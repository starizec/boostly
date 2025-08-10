<x-filament-panels::page wire:poll.10s="refreshChats">
    <div class="flex flex-row w-full" style="height: calc(100vh - 200px);">
        <!-- Left Sidebar - Chat List -->
        <div class="border-t border-b border-l border-gray-200 flex flex-col flex-shrink-0 min-h-0">
            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden" style="width: 300px">
                @if ($this->getFilteredChats()->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach ($this->getFilteredChats() as $chat)
                            <div wire:click="selectChat({{ $chat->id }})"
                                class="relative p-4 hover:bg-gray-100 cursor-pointer transition-all duration-200
                                    {{ $selectedChat && $selectedChat->id === $chat->id ? 'bg-white' : '' }}"
                                style="{{ $chat->unread_count > 0 && (!$selectedChat || $selectedChat->id !== $chat->id)
                                    ? 'background-color: #dcfce7 !important; border-right: 4px solid #22c55e !important;'
                                    : '' }}">
                                <!-- Normalni sadržaj chata -->
                                <div class="flex items-start space-x-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                @if ($selectedChat && $selectedChat->id === $chat->id)
                                                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                                @endif
                                                <p
                                                    class="text-sm font-medium {{ $selectedChat && $selectedChat->id === $chat->id ? 'text-blue-900' : 'text-gray-900' }} truncate">
                                                    {{ $chat->contact->name ?? 'Unknown Contact' }}
                                                </p>
                                            </div>
                                            <span class="text-xs text-gray-500">
                                                {{ $chat->last_message_at ? $chat->last_message_at->diffForHumans() : 'No messages' }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $chat->title ?: 'No title' }}
                                        </p>
                                        @if ($chat->latestMessage)
                                            <p class="text-sm text-gray-600 truncate mt-1">
                                                {{ Str::limit($chat->latestMessage->message, 50) }}
                                            </p>
                                        @endif
                                        <div class="flex items-center justify-between mt-1">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                {{ $chat->status?->name === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($chat->status?->name ?? 'Unknown') }}
                                            </span>
                                            @if ($chat->unread_count > 0)
                                                <span
                                                    class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                                                    {{ $chat->unread_count }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No chats found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new chat.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Middle Column -->
        <div class="border-t border-b border-gray-200 flex flex-col flex-shrink-0 min-h-0" style="width: 300px">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Chat Details</h3>
            </div>
            <div class="flex-1 overflow-y-auto overflow-x-hidden p-4">
                @if ($selectedChat)
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h4>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-900"><strong>Name:</strong> {{ $selectedChat->contact->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-600"><strong>Email:</strong> {{ $selectedChat->contact->email ?? 'Not provided' }}</p>
                                <p class="text-sm text-gray-600"><strong>Phone:</strong> {{ $selectedChat->contact->phone ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Chat Information</h4>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-900"><strong>Status:</strong> {{ ucfirst($selectedChat->status?->name ?? 'Unknown') }}</p>
                                <p class="text-sm text-gray-600"><strong>Created:</strong> {{ $selectedChat->created_at->format('M j, Y') }}</p>
                                <p class="text-sm text-gray-600"><strong>Last Message:</strong> {{ $selectedChat->last_message_at ? $selectedChat->last_message_at->format('M j, Y g:i A') : 'Never' }}</p>
                                <p class="text-sm text-gray-600"><strong>Unread:</strong> {{ $selectedChat->unread_count }} messages</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Change Status</h4>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <select wire:change="updateChatStatus($event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                    <option value="">Select Status</option>
                                    @foreach($this->getAvailableStatuses() as $id => $name)
                                        <option value="{{ $id }}" {{ $selectedChat->status_id == $id ? 'selected' : '' }}>
                                            {{ ucfirst($name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No chat selected</h3>
                            <p class="mt-1 text-sm text-gray-500">Select a chat to view details.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side - Chat Feed -->
        <div class="relative flex-1 flex flex-col bg-white min-w-0 h-full border border-gray-200 min-h-0">
            {{-- FULL OVERLAY dok traje selectChat --}}
            <div wire:loading wire:target="selectChat"
                class="absolute inset-0 z-20 bg-white/70 backdrop-blur-[1px] flex items-center justify-center">
                <div class="animate-spin rounded-full h-10 w-10 border-2 border-gray-300 border-t-transparent"></div>
            </div>

            @if ($selectedChat)
                <!-- Messages -->
                <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-4" id="messages-container">
                    @if ($selectedChat->messages->count() > 0)
                        @foreach ($selectedChat->messages as $message)
                            <div class="flex {{ $message->type === 'agent' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                                    style="{{ $message->type === 'agent'
                                        ? 'background-color: #6b7280 !important; color: white !important;'
                                        : 'background-color: #bfdbfe !important; color: #111827 !important;' }}">
                                    @if ($message->type === 'agent' && $message->agent)
                                        <p
                                            class="text-xs {{ $message->type === 'agent' ? 'text-gray-100' : 'text-gray-600' }} mb-1 font-medium">
                                            {{ $message->agent->name }}
                                        </p>
                                    @endif
                                    <p
                                        class="text-sm {{ $message->type === 'agent' ? 'text-white' : 'text-gray-900' }}">
                                        {{ $message->message }}
                                    </p>
                                    <p
                                        class="text-xs {{ $message->type === 'agent' ? 'text-gray-100' : 'text-gray-600' }} mt-1">
                                        {{ $message->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <div class="mx-auto h-12 w-12 text-gray-400">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No messages yet</h3>
                                <p class="mt-1 text-sm text-gray-500">Start the conversation by sending a message.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Message Input -->
                <div class="p-4 border-t border-gray-200">
                    <form wire:submit="sendMessage" class="flex space-x-2">
                        <div class="flex-1">
                            <x-filament::input.wrapper>
                                <textarea wire:model="message" placeholder="Type your message... (Press Enter to send)" rows="1"
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm resize-none"
                                    x-data="{}" x-on:keydown.enter.prevent="$wire.handleKeyPress('Enter')"></textarea>
                            </x-filament::input.wrapper>
                        </div>
                        <x-filament::button type="submit" :disabled="empty($message)" icon="heroicon-m-paper-airplane">
                            Send
                        </x-filament::button>
                    </form>
                </div>
            @else
                <!-- No Chat Selected -->
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No chat selected</h3>
                        <p class="mt-1 text-sm text-gray-500">Select a chat from the list to start messaging.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Auto-scroll na dno poruka kad se mijenja sadržaj
        document.addEventListener('livewire:updated', function() {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });

        // Auto-scroll pri inicijalnom učitavanju
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    </script>
</x-filament-panels::page>
