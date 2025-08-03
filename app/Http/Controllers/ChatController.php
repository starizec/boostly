<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\WidgetUrl;
use App\Models\Widget;
use App\Models\Media;
use App\Models\WidgetAction;
use App\Models\WidgetStyle;
class ChatController extends Controller
{
    public $isWidgetVisible = false;
    public $domain;
    public $path;

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

        $widgetUrl = WidgetUrl::where('url', $this->path)
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

        if (!$domainExists) {
            return response()->json([
                'allowed' => false,
                'error' => 'Domain not authorized',
                'status' => 403
            ], 403);
        }
        return $domainExists;
    }

    public function verifyDomain()
    {
        if (!$this->checkDomain()) {
            return response()->json(['error' => 'Domain not found' . ' ' . parse_url($this->domain, PHP_URL_HOST)], 404);
        }

        $widgetUrl = WidgetUrl::where('url', $this->path)
            ->first();

        if ($widgetUrl) {
            $widget = Widget::find($widgetUrl->widget_id);
            $widgetStyle = WidgetStyle::where('widget_id', $widget->id)->first();

            if ($this->isWidgetVisibleToday($widget) && $this->isWidgetVisibleNow($widget)) {
                return response()->json(['allowed' => true, 'visible' => true, 'widget' => $widget, 'widgetStyle' => $widgetStyle]);
            } else {
                return response()->json(['allowed' => true, 'visible' => false, 'widget' => false]);
            }
        }

        return response()->json(['allowed' => false, 'visible' => false]);
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

    public function widgetVisible($referer)
    {
        return $this->isWidgetVisible;
    }
}
