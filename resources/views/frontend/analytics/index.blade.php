@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Pojavljivanja</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    @php
                                        $loadedStats = $eventStats['loaded'] ?? ['all_time' => 0, 'mom_change' => null];
                                        $loadedMom = $loadedStats['mom_change'];
                                        $loadedUp = is_null($loadedMom) ? true : ($loadedMom >= 0);
                                        $loadedPercent = is_null($loadedMom) ? 0 : $loadedMom;
                                    @endphp
                                    <h3 class="mb-2">{{ number_format($loadedStats['all_time'] ?? 0) }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="{{ $loadedUp ? 'text-success' : 'text-danger' }}">
                                            <span>{{ $loadedUp ? '+' : '' }}{{ number_format($loadedPercent, 1) }}%</span>
                                            <i data-lucide="{{ $loadedUp ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-12 col-xl-7">
                                    <div id="pojavljivanjaChart" class="mt-md-3 mt-xl-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Otvaranja</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    @php
                                        $openedStats = $eventStats['opened'] ?? ['all_time' => 0, 'mom_change' => null];
                                        $openedMom = $openedStats['mom_change'];
                                        $openedUp = is_null($openedMom) ? true : ($openedMom >= 0);
                                        $openedPercent = is_null($openedMom) ? 0 : $openedMom;
                                    @endphp
                                    <h3 class="mb-2">{{ number_format($openedStats['all_time'] ?? 0) }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="{{ $openedUp ? 'text-success' : 'text-danger' }}">
                                            <span>{{ $openedUp ? '+' : '' }}{{ number_format($openedPercent, 1) }}%</span>
                                            <i data-lucide="{{ $openedUp ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-12 col-xl-7">
                                    <div id="otvaranjaChart" class="mt-md-3 mt-xl-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Klikovi</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    @php
                                        $clickedStats = $eventStats['action_clicked'] ?? ['all_time' => 0, 'mom_change' => null];
                                        $clickedMom = $clickedStats['mom_change'];
                                        $clickedUp = is_null($clickedMom) ? true : ($clickedMom >= 0);
                                        $clickedPercent = is_null($clickedMom) ? 0 : $clickedMom;
                                    @endphp
                                    <h3 class="mb-2">{{ number_format($clickedStats['all_time'] ?? 0) }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="{{ $clickedUp ? 'text-success' : 'text-danger' }}">
                                            <span>{{ $clickedUp ? '+' : '' }}{{ number_format($clickedPercent, 1) }}%</span>
                                            <i data-lucide="{{ $clickedUp ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-12 col-xl-7">
                                    <div id="klikoviChart" class="mt-md-3 mt-xl-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- row -->

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
                                    <tr>
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
                                                class="badge bg-danger">{{ $widget->active ? 'Active' : 'Inactive' }}</span>
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
            const el = document.querySelector('#pojavljivanjaChart');
            if (!el || typeof ApexCharts === 'undefined') return;

            const categories = @json($chartCategories ?? []);
            const series = @json($chartSeriesLoaded ?? []);

            const colors = (window && window.config && window.config.colors)
                ? window.config.colors
                : { primary: '#6571ff', secondary: '#7987a1', warning: '#fbbc06', danger: '#ff3366', gridBorder: 'rgba(77, 138, 240, .15)' };

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

            const colors = (window && window.config && window.config.colors)
                ? window.config.colors
                : { primary: '#6571ff', secondary: '#7987a1', warning: '#fbbc06', danger: '#ff3366', gridBorder: 'rgba(77, 138, 240, .15)' };

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

            const colors = (window && window.config && window.config.colors)
                ? window.config.colors
                : { primary: '#6571ff', secondary: '#7987a1', warning: '#fbbc06', danger: '#ff3366', gridBorder: 'rgba(77, 138, 240, .15)' };

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
