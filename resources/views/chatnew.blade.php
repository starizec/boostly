@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div id="app"></div>
                    <div data-widget='@json($widget)' data-widget-action='@json($widgetAction)'
                        data-widget-style='@json($widgetStyle)' data-media='@json($media)'
                        style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
