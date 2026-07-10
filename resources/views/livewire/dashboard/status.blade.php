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
                <div id="container-1"></div>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            window.addEventListener('update-chart-1', event => {
                const data = event.detail.data;
                const total = event.detail.totalTopic;
                const drilldown = event.detail.drilldownData;

                Highcharts.chart('container-1', {
                    chart: {
                        type: 'pie',
                        custom: {},
                        events: {
                            render() {
                                const chart = this,
                                    series = chart.series[0];
                                let customLabel = chart.options.chart.custom.label;
        
                                if (!customLabel) {
                                    customLabel = chart.options.chart.custom.label =
                                        chart.renderer.label(
                                            'Total<br/>' +
                                            '<strong>'+total+'</strong>'
                                        )
                                            .css({
                                                textAnchor: 'middle'
                                            })
                                            .addClass('dashboard-total-label')
                                            .add();
                                }
        
                                const x = series.center[0] + chart.plotLeft,
                                    y = series.center[1] + chart.plotTop -
                                    (customLabel.attr('height') / 2);
        
                                customLabel.attr({
                                    x,
                                    y
                                });
                                // Set font size based on chart diameter
                                customLabel.css({
                                    fontSize: `${series.center[2] / 12}px`
                                });
                            }
                        }
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    title: {
                        text: 'Topics of Meeting Status'
                    },
                    subtitle: {
                        text: 'Source: <a href="{{route("mom.index")}}">MOM\'s</a>'
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.0f}%</b>'
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            borderRadius: 8,
                            dataLabels: [{
                                enabled: true,
                                distance: 20,
                                format: '{point.name}',
                                style: {
                                    color: '#212529',
                                    fontWeight: '600',
                                    textOutline: 'none'
                                }
                            }, {
                                enabled: true,
                                distance: -15,
                                format: '{point.percentage:.0f}%',
                                style: {
                                    fontSize: '0.9em'
                                }
                            }],
                            showInLegend: true
                        }
                    },
                    series: [{
                        name: 'Topics',
                        colorByPoint: true,
                        innerSize: '75%',
                        data: data
                    }],
                    drilldown: {
                        series: drilldown
                    }
                });

            });
        });
    </script>
</div>
