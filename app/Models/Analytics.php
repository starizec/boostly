<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analytics extends Model
{
    protected $fillable = [
        'widget_id',
        'event',
        'url',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the widget that owns the analytics event.
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Event types constants
     */
    public const EVENT_LOADED = 'loaded';
    public const EVENT_OPENED = 'opened';
    public const EVENT_ACTION_CLICKED = 'action_clicked';
    public const EVENT_CHAT_CLICKED = 'chat_clicked';
    public const EVENT_CHAT_STARTED = 'chat_started';
    public const EVENT_CONVERSION = 'conversion';

    /**
     * Get all available event types
     */
    public static function getEventTypes(): array
    {
        return [
            self::EVENT_LOADED,
            self::EVENT_OPENED,
            self::EVENT_ACTION_CLICKED,
            self::EVENT_CHAT_CLICKED,
            self::EVENT_CHAT_STARTED,
            self::EVENT_CONVERSION,
        ];
    }
}
