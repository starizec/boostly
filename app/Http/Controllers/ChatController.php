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
use App\Models\Status;
use App\Models\ConversionUrl;
use App\Services\AnalyticsService;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class ChatController extends Controller
{
    public $isWidgetVisible = false;
    public $domain;
    public $path;
    public $clientDomain;
    public $clientUrl;
    public function __construct(
        private AnalyticsService $analyticsService
    ) {
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
            $widget = Widget::with(['style', 'media'])->find($widgetUrl->widget_id);
            $widgetAction = WidgetAction::find($widget->action_id);
            $widgetStyle = $widget->style;
            $media = $widget->media;

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
        Log::info('Request received: ' . json_encode($request->all()));
        $this->clientDomain = $request->input('client_domain');
        $this->clientUrl = $request->input('client_url');
        
        if (!$this->checkDomain()) {
            Log::info('Domain not found: ' . $this->clientDomain);
            return response()->json([
                'error' => 'Domain not authorized',
                'domain' => $this->clientDomain,
                'message' => 'Domain not found in authorized domains list'
            ], 403);
        }
        Log::info('Request: ' . json_encode($request->all()));
        // Check if client_url matches any conversion URL and track conversion
        $this->checkAndTrackConversion($request);

        if ($request->input('bc_id') && $request->input('bw_id')) {
            $chat = Chat::with('messages')->with('messages.agent')->find($request->input('bc_id'));
            $widget = Widget::with('widgetAction')->with('style')->with('media')->find($request->input('bw_id'));

            if ($chat) {
                return response()->json(['allowed' => true, 'visible' => true, 'widget' => $widget, 'chat' => $chat]);
            }
        }

        $widgetUrl = WidgetUrl::where('url', $this->clientUrl)
            ->first();

        if ($widgetUrl) {
            $widget = Widget::with('widgetAction')->with('style')->with('media')->find($widgetUrl->widget_id);

            if ($this->isWidgetVisibleToday($widget) && $this->isWidgetVisibleNow($widget)) {
                return response()->json(['allowed' => true, 'visible' => true, 'widget' => $widget, 'chat' => null]);
            } else {
                return response()->json(['allowed' => true, 'visible' => false, 'widget' => false, 'chat' => null]);
            }
        }

        Log::info('No widget found for this domain: ' . $this->clientUrl);
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
            'name' => 'string|min:2',
            'email' => 'required|email',
            'message' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            // Extract name from email if no name is provided
            $name = $request->name;
            if (empty($name) && !empty($request->email)) {
                $name = explode('@', $request->email)[0];
            }
            
            // Create contact
            $contact = Contact::create([
                'name' => $name,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
            ]);

            // Get the widget to find the company
            $widget = Widget::with('user')->find($request->bw_id);
            if (!$widget) {
                return response()->json([
                    'success' => false,
                    'message' => 'Widget not found'
                ], 404);
            }
            
            $companyId = $widget->user->company_id;
            
            // Get default status for the specific company
            $defaultStatus = Status::where('default', true)
                ->where('company_id', $companyId)
                ->first();

            // If no default status found, get the first available status for the company
            if (!$defaultStatus) {
                $defaultStatus = Status::where('company_id', $companyId)->first();
            }

            // If still no status found, return error
            if (!$defaultStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'No status configuration found for this company'
                ], 500);
            }

            // Create new chat
            $chat = Chat::create([
                'contact_id' => $contact->id,
                'widget_id' => $request->bw_id,
                'status_id' => $defaultStatus->id,
                'last_message_at' => now(),
                'started_url' => $request->client_url,
            ]);

            // Create first message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'message' => $request->message,
                'type' => 'user',
                'is_read' => false,
            ]);

            // Broadcast the message event (with error handling)
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Exception $broadcastError) {
                // Log the broadcast error but don't fail the chat creation
                Log::warning('Failed to broadcast message event: ' . $broadcastError->getMessage());
            }

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

            // Broadcast the message event (with error handling)
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Exception $broadcastError) {
                // Log the broadcast error but don't fail the message creation
                Log::warning('Failed to broadcast message event: ' . $broadcastError->getMessage());
            }

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
                ->with(['agent' => function($query) {
                    $query->select('id', 'name', 'email');
                }])
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

    public function sendAdminMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $message = ChatMessage::create([
                'chat_id' => $request->chat_id,
                'message' => $request->message,
                'type' => 'agent', // Admin/agent message
                'is_read' => false,
            ]);

            // Update chat last message time
            Chat::where('id', $request->chat_id)->update([
                'last_message_at' => now()
            ]);

            // Broadcast the message event (with error handling)
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Exception $broadcastError) {
                // Log the broadcast error but don't fail the message creation
                Log::warning('Failed to broadcast message event: ' . $broadcastError->getMessage());
            }

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

    public function showChat()
    {
        $widget = Widget::with(['style', 'media'])->find(1);
        $widgetAction = null;
        $widgetStyle = null;
        $media = null;

        if ($widget) {
            $widgetAction = WidgetAction::find($widget->action_id);
            $widgetStyle = $widget->style;
            $media = $widget->media;
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

    /**
     * Check if client URL matches any conversion URL and track conversion
     */
    private function checkAndTrackConversion(Request $request): void
    {
        Log::info('Checking and tracking conversion for URL: ' . $this->clientUrl);
        Log::info('Checking and tracking conversion for URL: ' . $request);
        try {
            // Check if the client URL begins with any conversion URL (take the latest match)
            $conversionUrl = ConversionUrl::orderBy('created_at', 'desc')
                ->get()
                ->first(function ($url) {
                    return str_starts_with($this->clientUrl, $url->url);
                });
            Log::info('Conversion URL: ' . $conversionUrl);
            if ($conversionUrl) {
                // Get widget ID from request
                $widgetId = $request->input('bw_id');
                
                if ($widgetId) {
                    // Track conversion event
                    $this->analyticsService->track(
                        widgetId: (int) $widgetId,
                        event: 'conversion',
                        url: $this->clientUrl,
                        data: [
                            'conversion_url_id' => $conversionUrl->id,
                            'conversion_url' => $conversionUrl->url,
                            'client_domain' => $this->clientDomain,
                            'has_existing_chat' => $request->input('bc_id') ? true : false,
                            'timestamp' => now()->toISOString(),
                        ]
                    );
                    
                    Log::info('Conversion tracked for URL: ' . $this->clientUrl . ' on widget: ' . $widgetId);
                } else {
                    Log::warning('Conversion URL matched but no widget ID provided: ' . $this->clientUrl);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error tracking conversion: ' . $e->getMessage(), [
                'client_url' => $this->clientUrl,
                'client_domain' => $this->clientDomain,
                'widget_id' => $request->input('bw_id'),
            ]);
        }
    }
}
