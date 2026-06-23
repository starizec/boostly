<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWidgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'form_active' => ['sometimes', 'boolean'],
            'form_show_name' => ['sometimes', 'boolean'],
            'form_show_email' => ['sometimes', 'boolean'],
            'form_show_message' => ['sometimes', 'boolean'],
            'show_monday' => ['sometimes', 'boolean'],
            'show_tuesday' => ['sometimes', 'boolean'],
            'show_wednesday' => ['sometimes', 'boolean'],
            'show_thursday' => ['sometimes', 'boolean'],
            'show_friday' => ['sometimes', 'boolean'],
            'show_saturday' => ['sometimes', 'boolean'],
            'show_sunday' => ['sometimes', 'boolean'],
            'show_time_start' => ['required', 'date_format:H:i'],
            'show_time_end' => ['required', 'date_format:H:i'],
            'offline_message' => ['nullable', 'string'],
            'send_to_email' => ['nullable', 'email', 'max:255'],
            'offline_title' => ['nullable', 'string', 'max:255'],
            'form_title' => ['nullable', 'string', 'max:255'],
            'form_message' => ['nullable', 'string'],
            'form_placeholder_name' => ['nullable', 'string', 'max:255'],
            'form_placeholder_email' => ['nullable', 'string', 'max:255'],
            'form_placeholder_message' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'start_button_text' => ['nullable', 'string', 'max:255'],
            'message_input_placeholder' => ['nullable', 'string', 'max:255'],
            'back_button_text' => ['nullable', 'string', 'max:255'],
            'send_button_text' => ['nullable', 'string', 'max:255'],
            'agent_placeholder' => ['nullable', 'string', 'max:255'],
            'agent_name_placeholder' => ['nullable', 'string', 'max:255'],
            'action_id' => ['nullable', 'integer', 'exists:widget_actions,id'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'style_id' => ['nullable', 'integer', 'exists:widget_styles,id'],
            'create_new_style' => ['sometimes', 'boolean'],
            'new_style_start_button_border_radius' => ['nullable', 'integer', 'min:0'],
            'new_style_chat_button_border_radius' => ['nullable', 'integer', 'min:0'],
            'new_style_action_button_border_radius' => ['nullable', 'integer', 'min:0'],
            'new_style_widget_border_radius' => ['nullable', 'integer', 'min:0'],
            'new_style_widget_width' => ['nullable', 'integer', 'min:1'],
            'new_style_widget_height' => ['nullable', 'integer', 'min:1'],
            'new_style_widget_background_url' => ['nullable', 'url', 'max:255'],
            'new_style_start_button_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_start_button_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_start_button_hover_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_start_button_hover_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_chat_button_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_chat_button_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_chat_button_hover_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_chat_button_hover_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_action_button_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_action_button_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_action_button_hover_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_action_button_hover_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_widget_background_color_1' => ['nullable', 'string', 'max:20'],
            'new_style_widget_background_color_2' => ['nullable', 'string', 'max:20'],
            'new_style_widget_text_color' => ['nullable', 'string', 'max:20'],
            'new_style_widget_agent_buble_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_widget_agent_buble_color' => ['nullable', 'string', 'max:20'],
            'new_style_widget_user_buble_background_color' => ['nullable', 'string', 'max:20'],
            'new_style_widget_user_buble_color' => ['nullable', 'string', 'max:20'],
            'new_action_name' => ['nullable', 'string', 'max:255', 'required_with:new_action_url,new_action_button_text'],
            'new_action_url' => ['nullable', 'url', 'max:255', 'required_with:new_action_name'],
            'new_action_button_text' => ['nullable', 'string', 'max:255', 'required_with:new_action_name'],
            'new_media_name' => ['nullable', 'string', 'max:255', 'required_with:new_media_file'],
            'new_media_file' => ['nullable', 'file', 'mimes:mp4', 'max:102400'],
            'urls' => ['nullable', 'array'],
            'urls.*' => ['nullable', 'url', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $booleanFields = [
            'active',
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
            'create_new_style',
        ];

        $data = [];
        foreach ($booleanFields as $field) {
            $data[$field] = $this->boolean($field);
        }

        $this->merge($data);
    }
}
