@extends('layouts.app')

{{-- Customize layout sections --}}

@section('subtitle', __('adminlte::adminlte.welcome'))
@section('content_header_title', __('adminlte::adminlte.home'))
@section('content_header_subtitle', __('adminlte::adminlte.welcome'))

{{-- Content body: main page content --}}

@section('content_body')
<div class="dashboard-hero shadow-sm mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="dashboard-title mb-1">MoM Dashboard</h2>
            <p class="dashboard-description mb-0">
                Current progress in <b>Week {{ \Carbon\Carbon::now()->weekOfYear }}</b> of {{ \Carbon\Carbon::now()->year }}
            </p>
        </div>
        <div class="dashboard-hero-icon d-none d-md-block">
            <i class="far fa-calendar-check"></i>
        </div>
    </div>
</div>

<livewire:dashboard.filter/>

<div id="container-3">
    <div class="row">
        <div class="col-lg-6 d-flex">
            <livewire:dashboard.status/>
        </div>
        <div class="col-lg-6 d-flex">
            <livewire:dashboard.user-completed/>
        </div>

        <div class="col-12">
            <livewire:dashboard.timeline/>
        </div>
    </div>
</div>
@stop

{{-- Push extra CSS --}}

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <style>
        :root {
            --dash-chart-text: #343a40;
            --dash-chart-muted: #6c757d;
        }
        body.dark-mode {
            --dash-chart-text: #e9ecef;
            --dash-chart-muted: #adb5bd;
        }

        /* Hero header */
        .dashboard-hero {
            border-radius: .5rem;
            padding: 1.25rem 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, #8a6d4b 0%, #5c452c 100%);
        }
        .dashboard-hero .dashboard-title {
            font-weight: 600;
            letter-spacing: .02em;
        }
        .dashboard-hero .dashboard-description {
            opacity: .85;
        }
        .dashboard-hero-icon {
            font-size: 2.5rem;
            opacity: .5;
        }
        .dark-mode .dashboard-hero {
            background: linear-gradient(135deg, #343a40 0%, #23262d 100%);
            border: 1px solid #454d55;
        }

        /* Filter card */
        .dashboard-filter-card {
            border: 0;
            border-radius: .5rem;
        }
        .dashboard-filter-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--dash-chart-muted);
            margin-bottom: .25rem;
        }

        /* Chart cards */
        #container-3 .card,
        .dashboard-filter-card {
            overflow: hidden;
        }
        #container-3 .card {
            border: 0;
            border-radius: .5rem;
            width: 100%;
        }
        #container-3 .col-lg-6 > div {
            width: 100%;
        }
        .highcharts-figure {
            margin: 0;
        }

        /* Loading overlay shown while a chart component is refreshing */
        .chart-card {
            position: relative;
        }
        .chart-loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 10;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.7);
            color: #495057;
        }
        .dark-mode .chart-loading-overlay {
            background-color: rgba(33, 37, 41, 0.7);
            color: #e9ecef;
        }

        /* Active filters banner */
        .dashboard-filter-banner {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .35rem;
            border-radius: .5rem;
            padding: .5rem 1rem;
            background-color: rgba(246, 235, 219, 0.85);
            border-left: 4px solid #8a6d4b;
            color: #495057;
        }
        .dashboard-filter-banner .banner-lead {
            margin-right: .25rem;
        }
        .dashboard-filter-banner .filter-badge {
            background-color: #8a6d4b;
            color: #fff;
            font-weight: 500;
            padding: .35em .8em;
        }
        .dashboard-filter-banner .banner-loading {
            margin-left: auto;
            font-size: .85rem;
            color: #6c757d;
        }
        .dark-mode .dashboard-filter-banner {
            background-color: #2b3035;
            border-left-color: #7cb5ec;
            color: #ced4da;
        }
        .dark-mode .dashboard-filter-banner .filter-badge {
            background-color: #495057;
            color: #e9ecef;
        }
        .dark-mode .dashboard-filter-banner .banner-loading {
            color: #adb5bd;
        }

        /* Select2: size to match form-control-sm and adapt to theme */
        .dashboard-filter-card .select2-container--default .select2-selection--single {
            height: calc(1.8125rem + 2px);
            font-size: .875rem;
            border: 1px solid #ced4da;
            border-radius: .2rem;
        }
        .dashboard-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.8125rem;
            padding-left: .5rem;
        }
        .dashboard-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 1.8125rem;
        }
        .dark-mode .select2-container--default .select2-selection--single {
            background-color: #343a40;
            border-color: #6c757d;
        }
        .dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e9ecef;
        }
        .dark-mode .select2-dropdown {
            background-color: #343a40;
            border-color: #6c757d;
        }
        .dark-mode .select2-container--default .select2-results__option {
            color: #e9ecef;
        }
        .dark-mode .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #2b3035;
            border-color: #6c757d;
            color: #e9ecef;
        }
        .dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4e79a7;
        }
        .dark-mode .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #495057;
        }
    </style>

    <style>
        /* Highcharts: adapt to light/dark theme */
        .highcharts-background {
            fill: transparent;
        }

        .dashboard-total-label text {
            fill: var(--dash-chart-text) !important;
        }

        .dark-mode .highcharts-title,
        .dark-mode .highcharts-subtitle,
        .dark-mode .highcharts-axis-title,
        .dark-mode .highcharts-axis-labels > text,
        .dark-mode .highcharts-legend-item > text,
        .dark-mode .highcharts-data-label text,
        .dark-mode .highcharts-stack-labels text,
        .dark-mode .highcharts-range-selector-group text,
        .dark-mode .highcharts-button text,
        .dark-mode .highcharts-breadcrumbs-group text {
            color: #e9ecef !important;
            fill: #e9ecef !important;
        }

        .dark-mode .highcharts-subtitle a {
            fill: #7cb5ec !important;
        }

        .dark-mode .highcharts-grid-line {
            stroke: rgba(255, 255, 255, 0.08);
        }
        .dark-mode .highcharts-axis-line,
        .dark-mode .highcharts-tick {
            stroke: rgba(255, 255, 255, 0.25);
        }

        .dark-mode .highcharts-data-label-connector {
            stroke: #6c757d;
        }

        .dark-mode .highcharts-legend-box {
            fill: #343a40;
            stroke: #495057;
        }

        .dark-mode .highcharts-tooltip-box {
            fill: #2b3035;
            stroke: #495057;
        }
        .dark-mode .highcharts-tooltip text,
        .dark-mode .highcharts-tooltip span {
            color: #e9ecef !important;
            fill: #e9ecef !important;
        }

        .dark-mode .highcharts-button > rect {
            fill: #3a4047 !important;
            stroke: #495057;
        }
        .dark-mode .highcharts-button-pressed > rect {
            fill: #495057 !important;
        }

        .dark-mode .highcharts-scrollbar-track {
            fill: #343a40;
            stroke: #495057;
        }
        .dark-mode .highcharts-scrollbar-thumb {
            fill: #6c757d;
            stroke: #6c757d;
        }
        .dark-mode .highcharts-scrollbar-button {
            fill: #3a4047;
            stroke: #495057;
        }
        .dark-mode .highcharts-scrollbar-arrow {
            fill: #e9ecef;
        }
        .dark-mode .highcharts-navigator-outline {
            stroke: #495057;
        }
        .dark-mode .highcharts-navigator-handle {
            fill: #6c757d;
            stroke: #adb5bd;
        }
        .dark-mode .highcharts-navigator-mask-inside {
            fill: rgba(124, 181, 236, 0.2);
        }

        .dark-mode input.highcharts-range-selector {
            color: #e9ecef;
            background-color: #343a40;
        }
    </style>
@endpush

{{-- Push extra scripts --}}

@push('js')
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
    <script src="{{ asset('vendor/highcharts/modules/gantt.js') }}"></script>
    <script src="{{ asset('vendor/highcharts/modules/series-label.js') }}"></script>
    <script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
    <script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>
    <script>
        Highcharts.setOptions({
            chart: {
                backgroundColor: 'transparent',
                style: {
                    fontSize: '12px',
                    fontFamily: 'Arial, sans-serif',
                    lineHeight: '14px'
                }
            },
            title: {
                style: {
                    fontSize: '16px',
                    lineHeight: '14px',
                    fontWeight: '600',
                    color: '#212529'
                }
            },
            subtitle: {
                style: {
                    color: '#495057'
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px',
                    lineHeight: '12px',
                    color: '#212529'
                }
            },
            legend: {
                itemStyle: {
                    fontSize: '12px',
                    lineHeight: '12px',
                    color: '#212529'
                }
            },
            xAxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        lineHeight: '12px',
                        color: '#343a40'
                    }
                },
                title: {
                    style: {
                        color: '#343a40'
                    }
                }
            },
            yAxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        lineHeight: '12px',
                        color: '#343a40'
                    }
                },
                title: {
                    style: {
                        color: '#343a40'
                    }
                }
            }
        });
    </script>
@endpush
