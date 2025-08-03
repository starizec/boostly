<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'form_active',
        'form_show_name',
        'form_show_email', 
        'form_show_message',
        'show_monday',
        'show_tuesday',
        'show_wednesday',
        'show_thursday',
        'show_friday',
        'show_saturday',
        'show_sunday',
        'show_time_start',
        'show_time_end',
        'offline_message',
        'send_to_email',
        'action_id',
        'media_id',
        'user_id',
        'media_id',
        'button_text',
        'start_button_text',
        'style_id'
    ];

    protected $casts = [
        'form_active' => 'boolean',
        'form_show_name' => 'boolean',
        'form_show_email' => 'boolean',
        'form_show_message' => 'boolean',
        'show_monday' => 'boolean',
        'show_tuesday' => 'boolean',
        'show_wednesday' => 'boolean',
        'show_thursday' => 'boolean',
        'show_friday' => 'boolean',
        'show_saturday' => 'boolean',
        'show_sunday' => 'boolean',
        'show_time_start' => 'datetime',
        'show_time_end' => 'datetime',
        'button_text' => 'string',
        'start_button_text' => 'string',
        'style_id' => 'integer'
    ];

    public function widgetAction()
    {
        return $this->belongsTo(WidgetAction::class, 'action_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(WidgetStyle::class);
    }
} 