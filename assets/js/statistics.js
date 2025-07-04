var chartInstances = {};
var tableRecive;

$(document).ready(function () {
    $('#active3').addClass('active');
    charts();
    table();
});

$(window).on('resize', adjustCharts);

function charts() {

    let screenSizes = [
        { max: 460, y: 10, x: 9 },
        { max: 768, y: 11, x: 12 },
        { max: 880, y: 10, x: 9 },
        { max: 1200, y: 11, x: 12 },
        { max: 1400, y: 10, x: 11 },
        { max: 1655, y: 10, x: 9 },
        { max: Infinity, y: 12, x: 13 }
    ];

    let { x: sizeX, y: sizeY } = screenSizes.find(s => $(window).width() < s.max);

    $.getJSON('../src/controllers/dashboard.ajax.php?opc=1', (data) => {
        let qrt = $('#chart-line3')[0].getContext('2d');
        new Chart(qrt, {
            type: 'line',
            data: {
                labels: ["1QTR","2QTR","3QTR","4QTR"],
                datasets: [{
                    label: 'Total Amount $',
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(255, 255, 255, .8)',
                    pointBorderColor: 'transparent',
                    borderColor: 'rgba(255, 255, 255, .8)',
                    borderColor: 'rgba(255, 255, 255, .8)',
                    borderWidth: 4,
                    backgroundColor: 'transparent',
                    fill: true,
                    data: data.totalesQtr,
                    maxBarThickness: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                    display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            font: {
                                size: 12,
                                weight: 300,
                                family: 'Helvetica',
                                style: 'normal',
                                lineHeight: 2,
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            font: {
                                size: 13,
                                weight: 300,
                                family: 'Helvetica',
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        let mth = $('#chart-line1')[0].getContext('2d');
        chartInstances.mth = new Chart(mth, {
            type: 'line',
            data: {
                labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                datasets: [{
                    label: 'Total Amount $',
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(255, 255, 255, .8)',
          			pointBorderColor: 'transparent',
          			borderColor: 'rgba(255, 255, 255, .8)',
          			borderWidth: 4,
          			backgroundColor: 'transparent',
          			fill: true,
                    data: data.totalesMes,
                    maxBarThickness: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
          	            beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            font: {
                                size: sizeY,
                                weight: 300,
                                family: 'helvetica',
                                style: 'normal',
                                lineHeight: 2,
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            font: {
                                size: sizeX,
                                weight: 300,
                                family: 'Helvetica',
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });
  
        let sem = $('#chart-line2')[0].getContext('2d');
        new Chart(sem, {
            type: 'line',
            data: {
                labels: data.semana,
                datasets: [{
                    label: 'Total Amount $',
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(255, 255, 255, .8)',
                    pointBorderColor: 'transparent',
                    borderColor: 'rgba(255, 255, 255, .8)',
                    borderColor: 'rgba(255, 255, 255, .8)',
                    borderWidth: 4,
                    backgroundColor: 'transparent',
                    fill: true,
                    data: data.totalesWeek,
                    maxBarThickness: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            font: {
                                size: 12,
                                weight: 300,
                                family: 'Helvetica',
                                style: 'normal',
                                lineHeight: 2,
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            font: {
                                size: 13,
                                weight: 300,
                                family: 'Helvetica',
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });
        // $('.loader').fadeOut(250, function() {
            $('.loader').removeClass('loader').addClass('dark-mode');
        // });
    });

}

function table() {
    
    tableRecive = $('#received').DataTable({
        ajax: '../src/controllers/dashboard.ajax.php?opc=2',
        info: false,
        lengthChange: false,
        ordering: false,
        paging: false,
        searching: false,
        columnDefs: [
            {className: "dt-center", targets: [1, 2, 3, 4, 5]}
          ]
    });
}

function update() {

    $.getJSON('../src/controllers/dashboard.ajax.php?opc=1', (res) => {
        $('#tamount').html(res.tamount);
        $('#orders').html(res.orders);
        $('#blanks').html(res.blanks);
        $('#hits').html(res.hits);
        let charts = [
            {id: '#chart-line1', data: res.totalesMes},
            {id: '#chart-line2', labels: res.semana, data: res.totalesWeek},
            {id: '#chart-line3', data: res.totalesQtr}
        ];

        charts.forEach(chart => {
            let ctx = $(chart.id)[0].getContext('2d');
            let existingChart = Chart.getChart(ctx);

            if (existingChart) {
                if (chart.labels) existingChart.data.labels = chart.labels;
                existingChart.data.datasets[0].data = chart.data;
                existingChart.update();
            }
        });
    });

    tableRecive.ajax.reload();
}

function adjustCharts() {

    let sizes = [
        { max: 460, x: 9, y: 10 },
        { max: 768, x: 12, y: 11 },
        { max: 880, x: 9, y: 10 },
        { max: 1200, x: 12, y: 11 },
        { max: 1400, x: 11, y: 10 },
        { max: 1655, x: 9, y: 10 },
        { max: Infinity, x: 13, y: 12 }
    ];

    let width = $(window).width();
    let size = sizes.find(s => width < s.max);

    $.each(chartInstances, (_, chartInstance) => {
        let scales = chartInstance.config.options.scales;
        scales.x.ticks.font.size = size.x;
        scales.y.ticks.font.size = size.y;
        chartInstance.update();
    });
}