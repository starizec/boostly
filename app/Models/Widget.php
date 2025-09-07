<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'button_text',
        'start_button_text',
        'style_id',
        'active',
        'form_title',
        'form_message',
        'offline_title',
        'form_placeholder_name',
        'form_placeholder_email',
        'form_placeholder_message',
        'message_input_placeholder',
        'back_button_text',
        'send_button_text',
        'agent_placeholder',
        'agent_name_placeholder'
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
        'style_id' => 'integer',
        'active' => 'boolean',
        'form_title' => 'string',
        'form_message' => 'string',
        'offline_title' => 'string',
        'form_placeholder_name' => 'string',
        'form_placeholder_email' => 'string',
        'form_placeholder_message' => 'string',
        'message_input_placeholder' => 'string',
        'back_button_text' => 'string',
        'send_button_text' => 'string',
        'agent_placeholder' => 'string',
        'agent_name_placeholder' => 'string'
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

    public function widgetUrls(): HasMany
    {
        return $this->hasMany(WidgetUrl::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(Analytics::class);
    }
} 