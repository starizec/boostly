@php
    $styleDefaults = [
        'start_button_border_radius' => 5,
        'start_button_background_color' => '#007bff',
        'start_button_text_color' => '#ffffff',
        'start_button_hover_background_color' => '#0056b3',
        'start_button_hover_text_color' => '#ffffff',
        'chat_button_border_radius' => 5,
        'chat_button_background_color' => '#28a745',
        'chat_button_text_color' => '#ffffff',
        'chat_button_hover_background_color' => '#1e7e34',
        'chat_button_hover_text_color' => '#ffffff',
        'action_button_border_radius' => 5,
        'action_button_background_color' => '#ffc107',
        'action_button_text_color' => '#212529',
        'action_button_hover_background_color' => '#e0a800',
        'action_button_hover_text_color' => '#212529',
        'widget_border_radius' => 10,
        'widget_background_color_1' => '#ffffff',
        'widget_background_color_2' => '#f8f9fa',
        'widget_background_url' => '',
        'widget_text_color' => '#212529',
        'widget_width' => '350',
        'widget_height' => '500',
        'widget_agent_buble_background_color' => '#e9ecef',
        'widget_agent_buble_color' => '#212529',
        'widget_user_buble_background_color' => '#007bff',
        'widget_user_buble_color' => '#ffffff',
    ];

    $styleValue = fn (string $field) => old('new_style_' . $field, $styleDefaults[$field] ?? '');

    $styleGroups = [
        'Početni gumb' => [
            ['field' => 'start_button_border_radius', 'label' => 'Border radius', 'type' => 'number', 'icon' => 'corner-down-right'],
            ['field' => 'start_button_background_color', 'label' => 'Boja pozadine', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'start_button_text_color', 'label' => 'Boja teksta', 'type' => 'color', 'icon' => 'type'],
            ['field' => 'start_button_hover_background_color', 'label' => 'Hover pozadina', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'start_button_hover_text_color', 'label' => 'Hover tekst', 'type' => 'color', 'icon' => 'type'],
        ],
        'Chat gumb' => [
            ['field' => 'chat_button_border_radius', 'label' => 'Border radius', 'type' => 'number', 'icon' => 'corner-down-right'],
            ['field' => 'chat_button_background_color', 'label' => 'Boja pozadine', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'chat_button_text_color', 'label' => 'Boja teksta', 'type' => 'color', 'icon' => 'type'],
            ['field' => 'chat_button_hover_background_color', 'label' => 'Hover pozadina', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'chat_button_hover_text_color', 'label' => 'Hover tekst', 'type' => 'color', 'icon' => 'type'],
        ],
        'Akcija gumb' => [
            ['field' => 'action_button_border_radius', 'label' => 'Border radius', 'type' => 'number', 'icon' => 'corner-down-right'],
            ['field' => 'action_button_background_color', 'label' => 'Boja pozadine', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'action_button_text_color', 'label' => 'Boja teksta', 'type' => 'color', 'icon' => 'type'],
            ['field' => 'action_button_hover_background_color', 'label' => 'Hover pozadina', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'action_button_hover_text_color', 'label' => 'Hover tekst', 'type' => 'color', 'icon' => 'type'],
        ],
        'Widget' => [
            ['field' => 'widget_border_radius', 'label' => 'Border radius', 'type' => 'number', 'icon' => 'corner-down-right'],
            ['field' => 'widget_background_color_1', 'label' => 'Boja pozadine 1', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'widget_background_color_2', 'label' => 'Boja pozadine 2', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'widget_background_url', 'label' => 'URL pozadine', 'type' => 'url', 'icon' => 'image'],
            ['field' => 'widget_text_color', 'label' => 'Boja teksta', 'type' => 'color', 'icon' => 'type'],
            ['field' => 'widget_width', 'label' => 'Širina (px)', 'type' => 'number', 'icon' => 'move-horizontal'],
            ['field' => 'widget_height', 'label' => 'Visina (px)', 'type' => 'number', 'icon' => 'move-vertical'],
        ],
        'Chat baloni' => [
            ['field' => 'widget_agent_buble_background_color', 'label' => 'Agent pozadina', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'widget_agent_buble_color', 'label' => 'Agent tekst', 'type' => 'color', 'icon' => 'type'],
            ['field' => 'widget_user_buble_background_color', 'label' => 'Korisnik pozadina', 'type' => 'color', 'icon' => 'palette'],
            ['field' => 'widget_user_buble_color', 'label' => 'Korisnik tekst', 'type' => 'color', 'icon' => 'type'],
        ],
    ];
@endphp

@foreach ($styleGroups as $groupTitle => $fields)
    <h6 class="mb-3 mt-2">{{ $groupTitle }}</h6>
    <div class="row">
        @foreach ($fields as $meta)
            <div class="col-md-6 col-lg-4">
                <div class="mb-3">
                    <label for="new_style_{{ $meta['field'] }}" class="form-label">{{ $meta['label'] }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i data-lucide="{{ $meta['icon'] }}" class="icon-sm"></i></span>
                        <input type="{{ $meta['type'] }}"
                            class="form-control @error('new_style_' . $meta['field']) is-invalid @enderror"
                            id="new_style_{{ $meta['field'] }}"
                            name="new_style_{{ $meta['field'] }}"
                            value="{{ $styleValue($meta['field']) }}"
                            @if ($meta['type'] === 'number') min="0" @endif>
                    </div>
                    @error('new_style_' . $meta['field'])
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endforeach
    </div>
@endforeach
