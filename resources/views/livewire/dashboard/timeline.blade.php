<div>
    <div class="card shadow-sm chart-card">
        <div wire:loading.flex class="chart-loading-overlay">
            <div class="text-center">
                <i class="fas fa-circle-notch fa-spin fa-2x"></i>
                <div class="small mt-2">Updating chart...</div>
            </div>
        </div>
        <div class="card-body p-0">
            <figure class="highcharts-figure" wire:ignore>
                <div id="container-timeline"></div>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            window.addEventListener('update-chart-3', event => {
                const data = event.detail.data;
                const drilldownData = event.detail.drilldownData; // Get drilldown data

                Highcharts.ganttChart('container-timeline', {
                    chart: {
                        height: 600
                    },

                    title: {
                        text: 'MoM Timeline'
                    },

                    yAxis: {
                        uniqueNames: true,
                        scrollbar: {
                            enabled: true
                        },
                        max: 10,
                        labels: {
                            style: {
                                lineHeight: '14px'
                            }
                        }
                    },

                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            var point = this.point;
                            // Check if it's a drilldown point (i.e., from drilldown series)
                            if (point.series.options.id && point.series.options.id.startsWith('mom-')) {
                                return '<b>Topic:</b> ' + point.name + '<br/>' +
                                       '<b>Next Step:</b> ' + (point.options.next_step || '') + '<br/>' +
                                       '<b>Status:</b> ' + (point.options.status || '') + '<br/>' +
                                       '<b>Start:</b> ' + Highcharts.dateFormat('%Y-%m-%d', point.start) + '<br/>' +
                                       '<b>End:</b> ' + Highcharts.dateFormat('%Y-%m-%d', point.end);
                            } else {
                                // Original timeline point tooltip
                                return '<b>MoM Number:</b> ' + point.name + '<br/>' +
                                       '<b>Title:</b> ' + (point.options.title || '') + '<br/>' +
                                       '<b>Status:</b> ' + (point.options.status || '') + '<br/>' +
                                       '<b>Start:</b> ' + Highcharts.dateFormat('%Y-%m-%d', point.start) + '<br/>' +
                                       '<b>End:</b> ' + Highcharts.dateFormat('%Y-%m-%d', point.end);
                            }
                        }
                    },

                    plotOptions: {
                        gantt: {
                            completed: {
                                color: '#59a14f'
                            },
                            color: '#4e79a7'
                        },
                        // Enable drilldown for series
                        series: {
                            cursor: 'pointer', // Show pointer cursor on hover
                            point: {
                                events: {
                                    click: function () {
                                        // Only drill down if the point has a drilldown ID
                                        if (this.options.drilldown) {
                                            this.series.chart.drilldown(this.options.drilldown);
                                        }
                                    }
                                }
                            }
                        }
                    },

                    navigator: {
                        enabled: true,
                        liveRedraw: true,
                        series: {
                            type: 'gantt',
                            pointPlacement: 0.5,
                            pointPadding: 0.25,
                            accessibility: {
                                enabled: false
                            }
                        },
                        yAxis: {
                            min: 0,
                            max: 3,
                            reversed: true,
                            categories: []
                        }
                    },

                    scrollbar: {
                        enabled: true
                    },

                    rangeSelector: {
                        enabled: true,
                        selected: 0
                    },

                    accessibility: {
                        point: {
                            descriptionFormat: '{yCategory}. ' +
                                '{#if completed}Task {(multiply completed.amount 100):.1f}% ' +
                                'completed. {/if}' +
                                'Start {x:%Y-%m-%d}, end {x2:%Y-%m-%d}.'
                        },
                        series: {
                            descriptionFormat: '{name}'
                        }
                    },

                    lang: {
                        accessibility: {
                            axis: {
                                xAxisDescriptionPlural: 'The chart has a two-part X axis ' +
                                    'showing time in both week numbers and days.',
                                yAxisDescriptionPlural: 'The chart has one Y axis showing ' +
                                    'task categories.'
                            }
                        },
                        drillUpText: '‹ Back to MoM Timeline' // Text for the drill-up button
                    },

                    series: [{
                        name: 'MoM Timeline',
                        data: data
                    }],

                    // Define the drilldown series
                    drilldown: {
                        series: drilldownData, // This is where the drilldown data is fed
                        activeAxisLabelStyle: {
                            textDecoration: 'none', // Remove underline from drilled-down axis labels
                            fontStyle: 'italic'
                        },
                        activeDataLabelStyle: {
                            textDecoration: 'none', // Remove underline from drilled-down data labels
                            fontStyle: 'italic'
                        }
                    }
                });
            });
        });
    </script>
</div>
