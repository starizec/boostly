<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Polling;
use App\Models\Domain;

class ChatComponent extends Component
{
    #[Rule('required|min:1')]
    public $message = '';

    #[Rule('required|min:2')]
    public $name = '';

    #[Rule('required|email')]
    public $email = '';

    public $chat_id;
    public $isOpen = false;
    public $showInitialForm = true;
    public $showChat = false;
    public $widget;
    public $widgetAction;
    public $media;
    public $widgetStyle;

    public function mount($widget, $widgetAction, $media, $widgetStyle)
    {
        $this->widget = $widget;
        $this->widgetAction = $widgetAction;
        $this->media = $media;
        $this->widgetStyle = $widgetStyle;
        
        $this->chat_id = session('chat_id');
        $this->showInitialForm = !$this->chat_id;
        $this->showChat = $this->chat_id;
        $this->isOpen = $this->chat_id;
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function startChat()
    {
        $this->validate([
            'name' => 'min:2',
            'email' => 'email',
            'message' => 'required|min:1'
        ]);

        //Create contact
        $contact = Contact::create([
            'name' => $this->name,
            'email' => $this->email
        ]);

        // Create new chat
        $chat = Chat::create([
            'contact_id' => $contact->id,
            'status' => 'active',
            'last_message_at' => now()
        ]);

        $this->chat_id = $chat->id;
        session(['chat_id' => $this->chat_id]);

        // Create first message
        ChatMessage::create([
            'chat_id' => $this->chat_id,
            'message' => $this->message,
            'type' => 'user',
            'is_read' => false,
        ]);

        $this->isOpen = true;
        $this->showInitialForm = false;
        $this->showChat = true;
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required|min:1'
        ]);

        ChatMessage::create([
            'chat_id' => $this->chat_id,
            'message' => $this->message,
            'type' => 'user',
            'is_read' => false,
        ]);

        Chat::where('id', $this->chat_id)->update([
            'last_message_at' => now()
        ]);

        $this->message = '';
        $this->dispatch('messageStored');
    }

    #[On('messageStored')]
    public function render()
    {
        $messages = $this->chat_id ? ChatMessage::query()
            ->where('chat_id', $this->chat_id)
            ->latest()
            ->take(100)
            ->get()
            ->reverse() : collect();

        $unreadCount = ChatMessage::where('chat_id', $this->chat_id)
            ->where('type', 'agent')
            ->where('is_read', false)
            ->count();

        return view('livewire.chat-component', [
            'messages' => $messages,
            'unreadCount' => $unreadCount
        ]);
    }

    public function getMessagesProperty()
    {
        if (!$this->chat_id) {
            return collect();
        }

        $messages = ChatMessage::where('chat_id', $this->chat_id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Scroll to bottom after messages update
        $this->dispatch('scrollToBottom');

        return $messages;
    }
}
