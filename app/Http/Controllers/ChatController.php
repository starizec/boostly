<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\WidgetUrl;
use App\Models\Widget;
use App\Models\Media;
use App\Models\WidgetAction;
use App\Models\WidgetStyle;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Contact;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public $isWidgetVisible = false;
    public $domain;
    public $path;
    public $clientDomain;

    public function __construct()
    {
        $referer = request()->headers->get('referer');

        $protocol = parse_url($referer, PHP_URL_SCHEME);
        $host = parse_url($referer, PHP_URL_HOST);
        $this->path = parse_url($referer, PHP_URL_PATH);

        $this->domain = $protocol . '://' . $host;
    }

    public function index()
    {
        if (!$this->checkDomain()) {
            return response()->json(['error' => 'Domain not found' . ' ' . parse_url($this->domain, PHP_URL_HOST)], 404);
        }

        $widgetUrl = WidgetUrl::where('url', $this->domain)
            ->first();

        if (!$widgetUrl) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        if ($widgetUrl) {
            $widget = Widget::find($widgetUrl->widget_id);
            $widgetAction = WidgetAction::find($widget->action_id);
            $widgetStyle = WidgetStyle::where('widget_id', $widget->id)->first();
            $media = Media::find($widget->media_id);

            $this->isWidgetVisible = $this->isWidgetVisibleToday($widget) && $this->isWidgetVisibleNow($widget);

            if ($this->isWidgetVisible) {
                return view('chat', [
                    'widget' => $widget,
                    'widgetAction' => $widgetAction,
                    'media' => $media,
                    'widgetStyle' => $widgetStyle
                ]);
            }
        }
    }

    public function checkDomain()
    {
        $domainExists = Domain::where('url', $this->domain)
            ->exists();

        return $domainExists;
    }

    public function verifyDomain(Request $request)
    {

        $this->clientDomain = $request->input('client_domain');
        
        if (!$this->checkDomain()) {
            return response()->json([
                'error' => 'Domain not authorized',
                'domain' => $this->clientDomain,
                'message' => 'Domain not found in authorized domains list'
            ], 403);
        }

        if ($request->input('bc_id') && $request->input('bw_id')) {
            $chat = Chat::with('messages')->find($request->input('bc_id'));
            $widget = Widget::with('widgetAction')->with('style')->with('media')->find($request->input('bw_id'));

            if ($chat) {
                return response()->json(['allowed' => true, 'visible' => true, 'widget' => $widget, 'chat' => $chat]);
            }
        }

        $widgetUrl = WidgetUrl::where('url', $this->clientDomain)
            ->first();

        if ($widgetUrl) {
            $widget = Widget::find($widgetUrl->widget_id)->with('widgetAction')->with('style')->with('media')->first();


            if ($this->isWidgetVisibleToday($widget) && $this->isWidgetVisibleNow($widget)) {
                return response()->json(['allowed' => true, 'visible' => true, 'widget' => $widget, 'chat' => null]);
            } else {
                return response()->json(['allowed' => true, 'visible' => false, 'widget' => false, 'chat' => null]);
            }
        }

        return response()->json(['allowed' => false, 'visible' => false, 'message' => 'No widget found for this domain']);
    }

    private function isWidgetVisibleToday($settings)
    {
        $currentDay = strtolower(date('l'));

        $dayMap = [
            'monday' => 'show_monday',
            'tuesday' => 'show_tuesday',
            'wednesday' => 'show_wednesday',
            'thursday' => 'show_thursday',
            'friday' => 'show_friday',
            'saturday' => 'show_saturday',
            'sunday' => 'show_sunday'
        ];

        return $settings->{$dayMap[$currentDay]} ?? false;
    }

    private function isWidgetVisibleNow($settings)
    {
        $currentTime = now();
        $startTime = \Carbon\Carbon::parse($settings->show_time_start);
        $endTime = \Carbon\Carbon::parse($settings->show_time_end);

        return $currentTime->between($startTime, $endTime);
    }

    // API Methods for Chat Widget
    public function startChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'message' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Create contact
            $contact = Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? null,
            ]);

            // Create new chat
            $chat = Chat::create([
                'contact_id' => $contact->id,
                'status' => 'active',
                'last_message_at' => now(),
                'title' => 'Chat with ' . $request->name,
            ]);

            // Create first message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'message' => $request->message,
                'type' => 'user',
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'chat_id' => $chat->id,
                'message' => $message,
                'contact' => $contact,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string|min:1',
            'type' => 'in:user,agent',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $message = ChatMessage::create([
                'chat_id' => $request->chat_id,
                'message' => $request->message,
                'type' => $request->type ?? 'user',
                'is_read' => false,
            ]);

            // Update chat last message time
            Chat::where('id', $request->chat_id)->update([
                'last_message_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMessages($chatId)
    {
        try {
            $messages = ChatMessage::where('chat_id', $chatId)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'messages' => $messages,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getChatStatus($chatId)
    {
        try {
            $chat = Chat::with('contact')->find($chatId);

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            $unreadCount = ChatMessage::where('chat_id', $chatId)
                ->where('type', 'agent')
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'chat' => $chat,
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get chat status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showChat()
    {
        $widget = Widget::find(1);
        $widgetAction = null;
        $widgetStyle = null;
        $media = null;

        if ($widget) {
            $widgetAction = WidgetAction::find($widget->action_id);
            $widgetStyle = WidgetStyle::where('widget_id', $widget->id)->first();
            $media = Media::find($widget->media_id);
        }

        // Create default objects if data doesn't exist
        if (!$widget) {
            $widget = new Widget();
        }
        if (!$widgetAction) {
            $widgetAction = new WidgetAction();
        }
        if (!$widgetStyle) {
            $widgetStyle = new WidgetStyle();
        }
        if (!$media) {
            $media = new Media();
        }

        return view('chatnew')
            ->with('widget', $widget)
            ->with('widgetAction', $widgetAction)
            ->with('widgetStyle', $widgetStyle)
            ->with('media', $media);
    }
}
