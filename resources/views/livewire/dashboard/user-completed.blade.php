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
                <div id="container-2"></div>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            window.addEventListener('update-chart-2', event => {
                const data = event.detail.data;

                Highcharts.chart('container-2', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: 'Tasks Assigned to People by Status'
                    },
                    xAxis: {
                        categories: data.categories,
                        title: {
                            text: null
                        },
                        gridLineWidth: 1,
                        lineWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Number of Tasks'
                        },
                        stackLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                color: ( // theme
                                    Highcharts.defaultOptions.title.style &&
                                    Highcharts.defaultOptions.title.style.color
                                ) || 'gray'
                            }
                        }
                    },
                    legend: {
                        align: 'right',
                        x: -5,
                        verticalAlign: 'top',
                        y: 10,
                        floating: true,
                        borderColor: '#CCC',
                        borderWidth: 1,
                        shadow: false
                    },
                    tooltip: {
                        headerFormat: '<b>{point.x}</b><br/>',
                        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                        }, 
                    },
                    series: data.series.map(s => {
                        if (s.name.toLowerCase() === 'completed') {
                            s.color = '#59a14f';
                        } else {
                            s.color = '#4e79a7';
                        }
                        return s;
                    })
                }); 
            });
        });
    </script>
</div>
