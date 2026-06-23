@php
    $widget = $widget ?? null;
    $isEdit = $widget !== null;
    $formAction = $isEdit ? route('widgets.update', $widget) : route('widgets.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';

    $value = function (string $field, mixed $default = '') use ($widget, $isEdit) {
        return old($field, $isEdit ? ($widget->{$field} ?? $default) : $default);
    };

    $checked = function (string $field, bool $default = true) use ($widget, $isEdit) {
        return old($field, $isEdit ? (bool) ($widget->{$field} ?? $default) : $default);
    };

    $timeValue = function (string $field, string $default = '00:00') use ($widget, $isEdit) {
        if (old($field)) {
            return old($field);
        }
        if ($isEdit && $widget->{$field}) {
            return \Carbon\Carbon::parse($widget->{$field})->format('H:i');
        }
        return $default;
    };

    $existingUrls = $isEdit ? $widget->widgetUrls->pluck('url')->toArray() : [];
    $urlRows = old('urls', $existingUrls ?: ['']);
    $tabs = [
        'osnovno' => ['label' => 'Osnovno', 'icon' => 'settings'],
        'forma' => ['label' => 'Forma', 'icon' => 'file-text'],
        'tekstovi' => ['label' => 'Tekstovi', 'icon' => 'type'],
        'akcije' => ['label' => 'Akcije', 'icon' => 'mouse-pointer-2'],
        'mediji' => ['label' => 'Mediji', 'icon' => 'image'],
        'stilovi' => ['label' => 'Stilovi', 'icon' => 'palette'],
        'urlovi' => ['label' => 'URL-ovi', 'icon' => 'link'],
    ];
    $activeTab = old('active_tab', $activeTab ?? 'osnovno');
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <div>
        <h4 class="mb-1">{{ $isEdit ? 'Uredi widget' : 'Kreiraj widget' }}</h4>
        <p class="text-muted mb-0">Konfigurirajte postavke vašeg chat widgeta</p>
    </div>
    <a href="{{ route('widgets.index') }}" class="btn btn-outline-primary">Natrag na listu</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="active_tab" id="activeTabInput" value="{{ $activeTab }}">

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header pb-0 border-bottom-0">
                    <ul class="nav nav-tabs card-header-tabs" id="widgetTabs" role="tablist">
                        @foreach ($tabs as $key => $tab)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === $key ? 'active' : '' }}" id="tab-{{ $key }}"
                                    data-bs-toggle="tab" data-bs-target="#pane-{{ $key }}" type="button"
                                    role="tab" aria-controls="pane-{{ $key }}"
                                    aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                                    <i data-lucide="{{ $tab['icon'] }}" class="icon-sm me-1"></i>
                                    {{ $tab['label'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="widgetTabsContent">

                        {{-- Osnovno --}}
                        <div class="tab-pane fade {{ $activeTab === 'osnovno' ? 'show active' : '' }}" id="pane-osnovno"
                            role="tabpanel" aria-labelledby="tab-osnovno">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Naziv widgeta <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="tag"
                                                    class="icon-sm"></i></span>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ $value('name') }}"
                                                placeholder="npr. Glavni chat widget" required>
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="active" name="active"
                                                value="1" @checked($checked('active'))>
                                            <label class="form-check-label" for="active">Aktivan</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-3">Prikaži na</h6>
                            <div class="row mb-3">
                                @foreach (['monday' => 'Pon', 'tuesday' => 'Uto', 'wednesday' => 'Sri', 'thursday' => 'Čet', 'friday' => 'Pet', 'saturday' => 'Sub', 'sunday' => 'Ned'] as $day => $label)
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="show_{{ $day }}" name="show_{{ $day }}" value="1"
                                                @checked($checked('show_' . $day))>
                                            <label class="form-check-label"
                                                for="show_{{ $day }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="show_time_start" class="form-label">Početno vrijeme</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="clock"
                                                    class="icon-sm"></i></span>
                                            <input type="time" class="form-control" id="show_time_start"
                                                name="show_time_start" value="{{ $timeValue('show_time_start') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="show_time_end" class="form-label">Završno vrijeme</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="clock"
                                                    class="icon-sm"></i></span>
                                            <input type="time" class="form-control" id="show_time_end"
                                                name="show_time_end"
                                                value="{{ $timeValue('show_time_end', '23:59') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Forma --}}
                        <div class="tab-pane fade {{ $activeTab === 'forma' ? 'show active' : '' }}" id="pane-forma"
                            role="tabpanel" aria-labelledby="tab-forma">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="form_active" name="form_active"
                                    value="1" @checked($checked('form_active'))>
                                <label class="form-check-label" for="form_active">Offline forma aktivna</label>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="send_to_email" class="form-label">Pošalji na email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="mail"
                                                    class="icon-sm"></i></span>
                                            <input type="email"
                                                class="form-control @error('send_to_email') is-invalid @enderror"
                                                id="send_to_email" name="send_to_email"
                                                value="{{ $value('send_to_email') }}" placeholder="info@primjer.hr">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="offline_title" class="form-label">Naslov offline forme</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="heading"
                                                    class="icon-sm"></i></span>
                                            <input type="text" class="form-control" id="offline_title"
                                                name="offline_title" value="{{ $value('offline_title') }}"
                                                placeholder="Trenutno nismo dostupni">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="offline_message" class="form-label">Offline poruka</label>
                                <div class="input-group">
                                    <span class="input-group-text align-items-start pt-2"><i data-lucide="message-square"
                                            class="icon-sm"></i></span>
                                    <textarea class="form-control" id="offline_message" name="offline_message" rows="3"
                                        placeholder="Ostavite nam poruku i javit ćemo vam se.">{{ $value('offline_message') }}</textarea>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Polja forme</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="form_title" class="form-label">Naslov forme</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="heading-2"
                                                    class="icon-sm"></i></span>
                                            <input type="text" class="form-control" id="form_title" name="form_title"
                                                value="{{ $value('form_title') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="form_message" class="form-label">Poruka forme</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-lucide="align-left"
                                                    class="icon-sm"></i></span>
                                            <input type="text" class="form-control" id="form_message"
                                                name="form_message" value="{{ $value('form_message') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach (['name' => 'Ime', 'email' => 'Email', 'message' => 'Poruka'] as $field => $label)
                                <div class="row align-items-center mb-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="form_show_{{ $field }}" name="form_show_{{ $field }}"
                                                value="1" @checked($checked('form_show_' . $field))>
                                            <label class="form-check-label"
                                                for="form_show_{{ $field }}">Prikaži {{ strtolower($label) }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text"><i
                                                    data-lucide="{{ $field === 'email' ? 'at-sign' : ($field === 'name' ? 'user' : 'message-circle') }}"
                                                    class="icon-sm"></i></span>
                                            <input type="text" class="form-control"
                                                name="form_placeholder_{{ $field }}"
                                                value="{{ $value('form_placeholder_' . $field) }}"
                                                placeholder="Placeholder za {{ strtolower($label) }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tekstovi --}}
                        <div class="tab-pane fade {{ $activeTab === 'tekstovi' ? 'show active' : '' }}" id="pane-tekstovi"
                            role="tabpanel" aria-labelledby="tab-tekstovi">
                            @php
                                $textFields = [
                                    'button_text' => ['label' => 'Tekst gumba', 'icon' => 'square-mouse-pointer'],
                                    'start_button_text' => ['label' => 'Tekst početnog gumba', 'icon' => 'play'],
                                    'message_input_placeholder' => ['label' => 'Placeholder za poruku', 'icon' => 'message-square'],
                                    'back_button_text' => ['label' => 'Tekst gumba nazad', 'icon' => 'arrow-left'],
                                    'send_button_text' => ['label' => 'Tekst gumba poslati', 'icon' => 'send'],
                                    'agent_placeholder' => ['label' => 'Placeholder za agenta', 'icon' => 'bot'],
                                    'agent_name_placeholder' => ['label' => 'Placeholder za naziv agenta', 'icon' => 'user-round'],
                                ];
                            @endphp
                            <div class="row">
                                @foreach ($textFields as $field => $meta)
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="{{ $field }}" class="form-label">{{ $meta['label'] }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i data-lucide="{{ $meta['icon'] }}"
                                                        class="icon-sm"></i></span>
                                                <input type="text" class="form-control" id="{{ $field }}"
                                                    name="{{ $field }}" value="{{ $value($field) }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Akcije --}}
                        <div class="tab-pane fade {{ $activeTab === 'akcije' ? 'show active' : '' }}" id="pane-akcije"
                            role="tabpanel" aria-labelledby="tab-akcije">
                            <div class="mb-4">
                                <label for="action_id" class="form-label">Odaberi postojeću akciju</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i data-lucide="mouse-pointer-2"
                                            class="icon-sm"></i></span>
                                    <select class="form-select" id="action_id" name="action_id">
                                        <option value="">— Bez akcije —</option>
                                        @foreach ($actions as $action)
                                            <option value="{{ $action->id }}" @selected((string) $value('action_id') === (string) $action->id)>
                                                {{ $action->name }} ({{ $action->button_text }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-3">Ili kreiraj novu akciju</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_action_name" class="form-label">Naziv</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i data-lucide="tag"
                                                        class="icon-sm"></i></span>
                                                <input type="text" class="form-control" id="new_action_name"
                                                    name="new_action_name" value="{{ old('new_action_name') }}"
                                                    placeholder="Kontakt">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_action_url" class="form-label">URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i data-lucide="link"
                                                        class="icon-sm"></i></span>
                                                <input type="url" class="form-control" id="new_action_url"
                                                    name="new_action_url" value="{{ old('new_action_url') }}"
                                                    placeholder="https://primjer.hr/kontakt">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_action_button_text" class="form-label">Tekst gumba</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i data-lucide="square-mouse-pointer"
                                                        class="icon-sm"></i></span>
                                                <input type="text" class="form-control" id="new_action_button_text"
                                                    name="new_action_button_text"
                                                    value="{{ old('new_action_button_text') }}" placeholder="Kontaktirajte nas">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Mediji --}}
                        <div class="tab-pane fade {{ $activeTab === 'mediji' ? 'show active' : '' }}" id="pane-mediji"
                            role="tabpanel" aria-labelledby="tab-mediji">
                            <div class="mb-4">
                                <label for="media_id" class="form-label">Odaberi postojeći medij</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i data-lucide="video" class="icon-sm"></i></span>
                                    <select class="form-select" id="media_id" name="media_id">
                                        <option value="">— Bez medija —</option>
                                        @foreach ($mediaItems as $media)
                                            <option value="{{ $media->id }}" @selected((string) $value('media_id') === (string) $media->id)>
                                                {{ $media->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-3">Ili učitaj novi video</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="new_media_name" class="form-label">Naziv medija</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i data-lucide="tag"
                                                        class="icon-sm"></i></span>
                                                <input type="text" class="form-control" id="new_media_name"
                                                    name="new_media_name" value="{{ old('new_media_name') }}"
                                                    placeholder="Promotivni video">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="new_media_file" class="form-label">Video datoteka (MP4)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i data-lucide="upload"
                                                        class="icon-sm"></i></span>
                                                <input type="file" class="form-control" id="new_media_file"
                                                    name="new_media_file" accept="video/mp4">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Stilovi --}}
                        <div class="tab-pane fade {{ $activeTab === 'stilovi' ? 'show active' : '' }}" id="pane-stilovi"
                            role="tabpanel" aria-labelledby="tab-stilovi">
                            <div class="mb-4">
                                <label for="style_id" class="form-label">Odaberi postojeći stil</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i data-lucide="palette" class="icon-sm"></i></span>
                                    <select class="form-select" id="style_id" name="style_id">
                                        <option value="">— Bez stila —</option>
                                        @foreach ($styles as $style)
                                            <option value="{{ $style->id }}" @selected((string) $value('style_id') === (string) $style->id)>
                                                Stil #{{ $style->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="border rounded p-3 bg-light" id="newStyleSection">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="create_new_style"
                                        name="create_new_style" value="1"
                                        @checked(old('create_new_style', false))>
                                    <label class="form-check-label" for="create_new_style">Kreiraj novi stil</label>
                                </div>
                                <div id="newStyleFields" @if (!old('create_new_style')) style="display:none;" @endif>
                                    @include('frontend.widgets.partials.style-fields')
                                </div>
                            </div>
                        </div>

                        {{-- URL-ovi --}}
                        <div class="tab-pane fade {{ $activeTab === 'urlovi' ? 'show active' : '' }}" id="pane-urlovi"
                            role="tabpanel" aria-labelledby="tab-urlovi">
                            <p class="text-muted mb-3">Dodajte URL-ove stranica na kojima će se widget prikazivati.</p>
                            <div id="urlRepeater">
                                @foreach ($urlRows as $index => $url)
                                    <div class="input-group mb-2 url-row">
                                        <span class="input-group-text"><i data-lucide="globe"
                                                class="icon-sm"></i></span>
                                        <input type="url" class="form-control" name="urls[]"
                                            value="{{ $url }}" placeholder="https://primjer.hr">
                                        <button type="button" class="btn btn-outline-danger btn-remove-url"
                                            @if (count($urlRows) <= 1) disabled @endif>
                                            <i data-lucide="trash-2" class="icon-sm"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnAddUrl">
                                <i data-lucide="plus" class="icon-sm me-1"></i>
                                Dodaj URL
                            </button>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('widgets.index') }}" class="btn btn-light">Odustani</a>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="icon-sm me-1"></i>
                            {{ $isEdit ? 'Spremi promjene' : 'Kreiraj widget' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabInput = document.getElementById('activeTabInput');
            document.querySelectorAll('#widgetTabs button[data-bs-toggle="tab"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const id = event.target.getAttribute('data-bs-target').replace('#pane-', '');
                    tabInput.value = id;
                });
            });

            const repeater = document.getElementById('urlRepeater');
            const addBtn = document.getElementById('btnAddUrl');

            function updateRemoveButtons() {
                const rows = repeater.querySelectorAll('.url-row');
                rows.forEach(function(row) {
                    const btn = row.querySelector('.btn-remove-url');
                    btn.disabled = rows.length <= 1;
                });
            }

            addBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'input-group mb-2 url-row';
                row.innerHTML = `
                    <span class="input-group-text"><i data-lucide="globe" class="icon-sm"></i></span>
                    <input type="url" class="form-control" name="urls[]" placeholder="https://primjer.hr">
                    <button type="button" class="btn btn-outline-danger btn-remove-url">
                        <i data-lucide="trash-2" class="icon-sm"></i>
                    </button>
                `;
                repeater.appendChild(row);
                if (window.lucide) lucide.createIcons();
                updateRemoveButtons();
            });

            repeater.addEventListener('click', function(event) {
                const btn = event.target.closest('.btn-remove-url');
                if (!btn || btn.disabled) return;
                btn.closest('.url-row').remove();
                updateRemoveButtons();
            });

            const createStyleCheckbox = document.getElementById('create_new_style');
            const newStyleFields = document.getElementById('newStyleFields');
            const styleSelect = document.getElementById('style_id');

            function toggleNewStyleFields() {
                const enabled = createStyleCheckbox.checked;
                newStyleFields.style.display = enabled ? '' : 'none';
                styleSelect.disabled = enabled;
                if (enabled) {
                    styleSelect.value = '';
                }
            }

            createStyleCheckbox.addEventListener('change', toggleNewStyleFields);
            toggleNewStyleFields();

            if (window.lucide) lucide.createIcons();
        });
    </script>
@endpush
