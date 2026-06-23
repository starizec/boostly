<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWidgetRequest;
use App\Models\Media;
use App\Models\Widget;
use App\Models\WidgetAction;
use App\Models\WidgetStyle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WidgetController extends Controller
{
    public function index(): View
    {
        $widgets = Widget::query()
            ->where('user_id', Auth::id())
            ->with(['widgetAction', 'media', 'style'])
            ->orderByDesc('id')
            ->get();

        return view('frontend.widgets.index', compact('widgets'));
    }

    public function create(): View
    {
        return view('frontend.widgets.create', $this->formData());
    }

    public function store(StoreWidgetRequest $request): RedirectResponse
    {
        $data = $this->resolveRelations($request);
        $data['user_id'] = Auth::id();

        $widget = Widget::create($data);
        $this->syncUrls($widget, $request->input('urls', []));

        return redirect()
            ->route('widgets.index')
            ->with('success', 'Widget je uspješno kreiran.');
    }

    public function edit(Widget $widget): View
    {
        $this->authorizeWidget($widget);

        return view('frontend.widgets.edit', array_merge(
            $this->formData(),
            ['widget' => $widget->load('widgetUrls')]
        ));
    }

    public function update(StoreWidgetRequest $request, Widget $widget): RedirectResponse
    {
        $this->authorizeWidget($widget);

        $data = $this->resolveRelations($request);
        $widget->update($data);
        $this->syncUrls($widget, $request->input('urls', []));

        return redirect()
            ->route('widgets.index')
            ->with('success', 'Widget je uspješno ažuriran.');
    }

    public function destroy(Widget $widget): RedirectResponse
    {
        $this->authorizeWidget($widget);
        $widget->delete();

        return redirect()
            ->route('widgets.index')
            ->with('success', 'Widget je uspješno obrisan.');
    }

    private function formData(): array
    {
        $userId = Auth::id();

        return [
            'actions' => WidgetAction::query()->where('user_id', $userId)->orderBy('name')->get(),
            'mediaItems' => Media::query()->where('user_id', $userId)->orderBy('name')->get(),
            'styles' => WidgetStyle::query()->orderByDesc('id')->get(),
            'activeTab' => request('tab', 'osnovno'),
        ];
    }

    private function resolveRelations(StoreWidgetRequest $request): array
    {
        $data = $request->safe()->except([
            'new_action_name',
            'new_action_url',
            'new_action_button_text',
            'new_media_name',
            'new_media_file',
            'create_new_style',
            'urls',
            ...array_map(fn (string $field) => 'new_style_' . $field, array_keys($this->styleDefaults())),
        ]);

        if ($request->boolean('create_new_style')) {
            $data['style_id'] = $this->createStyle($request);
        }

        if ($request->filled('new_action_name')) {
            $action = WidgetAction::create([
                'user_id' => Auth::id(),
                'name' => $request->input('new_action_name'),
                'url' => $request->input('new_action_url'),
                'button_text' => $request->input('new_action_button_text'),
            ]);
            $data['action_id'] = $action->id;
        }

        if ($request->hasFile('new_media_file')) {
            $file = $request->file('new_media_file');
            $path = $file->store('videos', 'public');

            $media = Media::create([
                'user_id' => Auth::id(),
                'name' => $request->input('new_media_name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'url' => $path,
                'mime_type' => 'video/mp4',
            ]);
            $data['media_id'] = $media->id;
        }

        return $data;
    }

    private function syncUrls(Widget $widget, array $urls): void
    {
        $widget->widgetUrls()->delete();

        foreach (array_filter($urls) as $url) {
            $widget->widgetUrls()->create(['url' => $url]);
        }
    }

    private function authorizeWidget(Widget $widget): void
    {
        abort_unless($widget->user_id === Auth::id(), 403);
    }

    private function createStyle(StoreWidgetRequest $request): int
    {
        $data = [];

        foreach (array_keys($this->styleDefaults()) as $field) {
            $value = $request->input('new_style_' . $field);
            $data[$field] = filled($value) ? $value : $this->styleDefaults()[$field];
        }

        foreach (['widget_width', 'widget_height'] as $dimension) {
            if (is_numeric($data[$dimension])) {
                $data[$dimension] = $data[$dimension] . 'px';
            }
        }

        if (blank($data['widget_background_url'])) {
            $data['widget_background_url'] = null;
        }

        return WidgetStyle::create($data)->id;
    }

    private function styleDefaults(): array
    {
        return [
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
            'widget_background_url' => null,
            'widget_text_color' => '#212529',
            'widget_width' => '350px',
            'widget_height' => '500px',
            'widget_agent_buble_background_color' => '#e9ecef',
            'widget_agent_buble_color' => '#212529',
            'widget_user_buble_background_color' => '#007bff',
            'widget_user_buble_color' => '#ffffff',
        ];
    }
}
