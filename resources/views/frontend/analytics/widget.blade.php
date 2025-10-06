@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

    <div class="row">
        <div class="col-12 d-flex align-items-center mb-3">
            <a href="{{ route('analytics.widgets') }}" class="btn btn-outline-secondary">&larr; Nazad</a>
            <div class="ms-3" style="min-width:260px;">
                <select id="widgetSelect" class="form-select">
                    @foreach(($widgets ?? collect()) as $w)
                        <option value="{{ $w->id }}" @selected(isset($widget) && $widget && $w->id === $widget->id)>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <div class="input-group flatpickr me-2 mb-2 mb-md-0" id="fromDate" style="width:140px;">
                <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle><i
                        data-lucide="calendar" class="text-primary"></i></span>
                <input type="text" class="form-control bg-transparent border-primary" placeholder="From date" data-input>
            </div>
            <div class="input-group flatpickr me-2 mb-2 mb-md-0" id="untilDate" style="width:140px;">
                <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle><i
                        data-lucide="calendar" class="text-primary"></i></span>
                <input type="text" class="form-control bg-transparent border-primary" placeholder="Until date"
                    data-input>
            </div>
            <button type="button" class="btn btn-outline-primary me-2 mb-2 mb-md-0" id="btnLastMonth" aria-label="Prošli mjesec">Prošli mjesec</button>
            <button type="button" class="btn btn-outline-primary me-2 mb-2 mb-md-0" id="btnThisMonth">Ovaj mjesec</button>

            @isset($allEventTypes)
                <div class="me-2 mb-2 mb-md-0 d-flex align-items-center flex-wrap" id="eventTypeInline">
                    @foreach ($allEventTypes as $type)
                        @php $id = 'evt_' . $type; @endphp
                        <div class="form-check form-check-inline me-2 mb-2">
                            <input class="form-check-input" type="checkbox" id="{{ $id }}" name="events[]"
                                value="{{ $type }}" @checked(isset($eventTypes) && in_array($type, $eventTypes, true))>
                            <label class="form-check-label text-capitalize"
                                for="{{ $id }}">{{ str_replace('_', ' ', $type) }}</label>
                        </div>
                    @endforeach
                </div>
            @endisset

            <button type="button" class="btn btn-primary me-2 mb-2 mb-md-0" id="btnApplyRange">Primijeni</button>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7 mb-3 mb-md-0">
                            @isset($eventTypes)
                                @php
                                    $palette = [
                                        'bg-primary',
                                        'bg-success',
                                        'bg-warning',
                                        'bg-danger',
                                        'bg-info',
                                        'bg-secondary',
                                    ];
                                    $i = 0;
                                @endphp
                                @foreach ($eventTypes as $etype)
                                    @php
                                        $cls = $palette[$i % count($palette)];
                                        $i++;
                                    @endphp
                                    <span
                                        class="badge {{ $cls }} me-2 text-capitalize">{{ str_replace('_', ' ', $etype) }}:
                                        {{ number_format($eventTotals[$etype] ?? 0) }}</span>
                                @endforeach
                            @endisset
                        </div>
                    </div>
                    <div id="dataChart"></div>
                </div>
            </div>
        </div>
    </div> <!-- row -->
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('build/plugins/apexcharts/apexcharts.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        (function() {
            // Navigate when selecting another widget, preserve existing query params
            const sel = document.getElementById('widgetSelect');
            if (sel) {
                sel.addEventListener('change', function() {
                    const params = new URLSearchParams(window.location.search);
                    const id = this.value;
                    const base = `{{ url('/analytics/widget') }}` + '/' + encodeURIComponent(id);
                    const qs = params.toString();
                    window.location.href = qs ? `${base}?${qs}` : base;
                });
            }
            if (typeof flatpickr === 'undefined') return;

            const fromEl = document.querySelector('#fromDate');
            const untilEl = document.querySelector('#untilDate');
            const btnThisMonth = document.querySelector('#btnThisMonth');
            const btnLastMonth = document.querySelector('#btnLastMonth');
            const btnApplyRange = document.querySelector('#btnApplyRange');
            if (!fromEl || !untilEl) return;

            const fromPicker = flatpickr('#fromDate', {
                wrap: true,
                dateFormat: 'd.m.Y',
                defaultDate: @json(isset($fromDate) ? \Carbon\Carbon::parse($fromDate)->format('d.m.Y') : null),
                onChange: function(selectedDates) {
                    if (selectedDates && selectedDates[0] && untilPicker) {
                        untilPicker.set('minDate', selectedDates[0]);
                    }
                },
            });

            const untilPicker = flatpickr('#untilDate', {
                wrap: true,
                dateFormat: 'd.m.Y',
                defaultDate: @json(isset($untilDate) ? \Carbon\Carbon::parse($untilDate)->format('d.m.Y') : null),
                onChange: function(selectedDates) {
                    if (selectedDates && selectedDates[0] && fromPicker) {
                        fromPicker.set('maxDate', selectedDates[0]);
                    }
                },
            });

            function setRange(start, end) {
                if (fromPicker) fromPicker.setDate(start, true);
                if (untilPicker) untilPicker.setDate(end, true);
                if (fromPicker && untilPicker) {
                    untilPicker.set('minDate', start);
                    fromPicker.set('maxDate', end);
                }
            }

            function startOfMonth(date) {
                return new Date(date.getFullYear(), date.getMonth(), 1);
            }

            function endOfMonth(date) {
                return new Date(date.getFullYear(), date.getMonth() + 1, 0);
            }

            (function presetInitial() {
                const hasBackendDefaults = !!(@json(isset($fromDate))) && !!(@json(isset($untilDate)));
                if (!hasBackendDefaults) {
                    const now = new Date();
                    setRange(startOfMonth(now), endOfMonth(now));
                }
            })();

            if (btnThisMonth) {
                btnThisMonth.addEventListener('click', function() {
                    const now = new Date();
                    setRange(startOfMonth(now), endOfMonth(now));
                });
            }

            if (btnLastMonth) {
                btnLastMonth.addEventListener('click', function() {
                    const now = new Date();
                    const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    setRange(startOfMonth(lastMonth), endOfMonth(lastMonth));
                });
            }

            if (btnApplyRange) {
                btnApplyRange.addEventListener('click', function() {
                    const fromVal = fromEl.querySelector('input.form-control').value;
                    const untilVal = untilEl.querySelector('input.form-control').value;
                    const params = new URLSearchParams(window.location.search);
                    if (fromVal) params.set('from', fromVal);
                    if (untilVal) params.set('until', untilVal);
                    const checked = Array.from(document.querySelectorAll(
                        '#eventTypeInline input[type="checkbox"]:checked')).map(cb => cb.value);
                    params.delete('events[]');
                    checked.forEach(v => params.append('events[]', v));
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                });
            }
        })();
    </script>
    <script>
        (function() {
            const el = document.querySelector('#dataChart');
            if (!el || typeof ApexCharts === 'undefined') return;

            const categories = @json($revenueCategories ?? []);
            const series = @json($revenueSeries ?? []);

            const colors = (window && window.config && window.config.colors) ?
                window.config.colors : {
                    primary: '#6571ff',
                    success: '#00c853',
                    gridBorder: 'rgba(77, 138, 240, .15)'
                };

            const options = {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: Array.isArray(series) ? series : [],
                xaxis: {
                    type: 'datetime',
                    categories: categories,
                    labels: {
                        rotate: -45
                    }
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: colors.gridBorder
                }
            };
            const chart = new ApexCharts(el, options);
            chart.render();
        })();
    </script>
@endpush
