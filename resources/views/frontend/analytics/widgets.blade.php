@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Analitika: Widgeti</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <div class="input-group flatpickr w-200px me-2 mb-2 mb-md-0" id="dashboardDate">
                <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle><i
                        data-lucide="calendar" class="text-primary"></i></span>
                <input type="text" class="form-control bg-transparent border-primary" placeholder="Select date"
                    data-input>
            </div>
            <button type="button" class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend" data-lucide="printer"></i>
                Print
            </button>
            <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend" data-lucide="download-cloud"></i>
                Download Report
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-xl-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h6 class="card-title mb-0">Widgeti</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pt-0">#</th>
                                    <th class="pt-0">Naziv</th>
                                    @isset($eventTypes)
                                        @foreach ($eventTypes as $type)
                                            <th class="pt-0 text-capitalize">{{ str_replace('_', ' ', $type) }} Count</th>
                                        @endforeach
                                    @endisset
                                    <th class="pt-0">Status</th>
                                    <th class="pt-0">Korisnik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($widgets as $widget)
                                    <tr data-href="{{ route('analytics.widget', ['widgetId' => $widget->id]) }}">
                                        <td>{{ $widget->id }}</td>
                                        <td>{{ $widget->name }}</td>
                                        @isset($eventTypes)
                                            @php $counts = $analyticsCounts[$widget->id] ?? []; @endphp
                                            @foreach ($eventTypes as $type)
                                                @php $value = $counts[$type] ?? 0; @endphp
                                                <td>
                                                    <span class="badge bg-primary">{{ $value }}</span>
                                                </td>
                                            @endforeach
                                        @endisset
                                        <td><span
                                                class="badge bg-success">{{ $widget->active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td>{{ $widget->user->name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
            const rows = document.querySelectorAll('.table tbody tr[data-href]');
            if (!rows || !rows.length) return;
            rows.forEach(function(row) {
                const url = row.getAttribute('data-href');
                if (!url) return;
                row.style.cursor = 'pointer';
                row.addEventListener('click', function(e) {
                    const interactive = e.target.closest('a, button, input, textarea, select, label');
                    if (interactive) return;
                    window.location.href = url;
                });
            });
        })();
    </script>
    <script>
        (function() {
            const el = document.querySelector('#pojavljivanjaChart');
            if (!el || typeof ApexCharts === 'undefined') return;

            const categories = @json($chartCategories ?? []);
            const series = @json($chartSeriesLoaded ?? []);

            const colors = (window && window.config && window.config.colors) ?
                window.config.colors :
                {
                    primary: '#6571ff',
                    secondary: '#7987a1',
                    warning: '#fbbc06',
                    danger: '#ff3366',
                    gridBorder: 'rgba(77, 138, 240, .15)'
                };

            const options = {
                chart: {
                    type: "line",
                    height: 60,
                    sparkline: {
                        enabled: !0
                    }
                },
                series: (Array.isArray(series) && series.length) ? series : [],
                xaxis: {
                    type: 'datetime',
                    categories: (Array.isArray(categories) && categories.length) ? categories : [],
                },
                yaxis: {
                    min: 0,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                stroke: {
                    width: 2,
                    curve: "smooth"
                },
                markers: {
                    size: 0
                },
                colors: [colors.primary],
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        })();
    </script>
    <script>
        (function() {
            const el = document.querySelector('#otvaranjaChart');
            if (!el || typeof ApexCharts === 'undefined') return;

            const categories = @json($chartCategories ?? []);
            const series = @json($chartSeriesOpened ?? []);

            const colors = (window && window.config && window.config.colors) ?
                window.config.colors :
                {
                    primary: '#6571ff',
                    secondary: '#7987a1',
                    warning: '#fbbc06',
                    danger: '#ff3366',
                    gridBorder: 'rgba(77, 138, 240, .15)'
                };

            const options = {
                chart: {
                    type: "line",
                    height: 60,
                    sparkline: {
                        enabled: !0
                    }
                },
                series: (Array.isArray(series) && series.length) ? series : [],
                xaxis: {
                    type: 'datetime',
                    categories: (Array.isArray(categories) && categories.length) ? categories : [],
                },
                yaxis: {
                    min: 0,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                stroke: {
                    width: 2,
                    curve: "smooth"
                },
                markers: {
                    size: 0
                },
                colors: [colors.primary],
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        })();
    </script>
    <script>
        (function() {
            const el = document.querySelector('#klikoviChart');
            if (!el || typeof ApexCharts === 'undefined') return;

            const categories = @json($chartCategories ?? []);
            const series = @json($chartSeriesActionClicked ?? []);

            const colors = (window && window.config && window.config.colors) ?
                window.config.colors :
                {
                    primary: '#6571ff',
                    secondary: '#7987a1',
                    warning: '#fbbc06',
                    danger: '#ff3366',
                    gridBorder: 'rgba(77, 138, 240, .15)'
                };

            const options = {
                chart: {
                    type: "line",
                    height: 60,
                    sparkline: {
                        enabled: !0
                    }
                },
                series: (Array.isArray(series) && series.length) ? series : [],
                xaxis: {
                    type: 'datetime',
                    categories: (Array.isArray(categories) && categories.length) ? categories : [],
                },
                yaxis: {
                    min: 0,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                stroke: {
                    width: 2,
                    curve: "smooth"
                },
                markers: {
                    size: 0
                },
                colors: [colors.primary],
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        })();
    </script>
@endpush

@push('custom-scripts')
  @vite(['resources/js/pages/dashboard.js'])
@endpush