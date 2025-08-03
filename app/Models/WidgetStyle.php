<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetStyle extends Model
{
    protected $fillable = [
        // Start Button Styles
        'start_button_border_radius',
        'start_button_background_color',
        'start_button_text_color',
        'start_button_hover_background_color',
        'start_button_hover_text_color',

        // Chat Button Styles
        'chat_button_border_radius',
        'chat_button_background_color',
        'chat_button_text_color',
        'chat_button_hover_background_color',
        'chat_button_hover_text_color',

        // Action Button Styles
        'action_button_border_radius',
        'action_button_background_color',
        'action_button_text_color',
        'action_button_hover_background_color',
        'action_button_hover_text_color',

        // Widget Container Styles
        'widget_border_radius',
        'widget_background_color_1',
        'widget_background_color_2',
        'widget_background_url',
        'widget_text_color',
        'widget_width',
        'widget_height',

        // Chat Bubble Styles
        'widget_agent_buble_background_color',
        'widget_agent_buble_color',
        'widget_user_buble_background_color',
        'widget_user_buble_color',
    ];

    /**
     * Get the widget that owns the style.
     */
    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
