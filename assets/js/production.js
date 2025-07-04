var chartInstances = {};
var TableProd;

$(document).ready(function() { 
    $('#active3').addClass('active');
    chartsProd();
    tableProd();
});

$(window).on('resize', adjustCharts);

function chartsProd() {

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

    $.getJSON('../src/controllers/dash-prod.ajax.php?opc=1', (res) => {
        let chartsData = [
            {id: '#printing', labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], data: res.totalPrint},
            {id: '#treatment', labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], data: res.totalTreat},
            {id: '#label', labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], data: res.totalLabel}
        ];

        chartsData.forEach(({ id, labels, data }) => {
            let ctx = $(id)[0].getContext('2d');

            chartInstances[id] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total Amount $',
                        tension: 0,
                        borderWidth: 4,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(255, 255, 255, .8)',
                        pointBorderColor: 'transparent',
                        borderColor: 'rgba(255, 255, 255, .8)',
                        backgroundColor: 'transparent',
                        fill: true,
                        data,
                        maxBarThickness: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    interaction: { intersect: false, mode: 'index' },
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
                                font: { size: sizeY, weight: 300, family: 'Helvetica', style: 'normal', lineHeight: 2 }
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
                                font: { size: sizeX, weight: 300, family: 'Helvetica', style: 'normal', lineHeight: 2 }
                            }
                        }
                    }
                }
            });
            $('.loader').removeClass('loader').addClass('dark-mode');
        });
    });
}

function tableProd() {

    TableProd = $('#production').DataTable({
        ajax: '../src/controllers/dash-prod.ajax.php?opc=2',
        info: false,
        lengthChange: false,
        ordering: false,
        paging: false,
        searching: false,
        columnDefs: [
            {className: "dt-center", targets: [1, 2, 3, 4]}
        ]
    });
}

function update() {

    $.getJSON('../src/controllers/dash-prod.ajax.php?opc=1', (res) => {
        let charts = [
            {id: '#printing', data: res.totalPrint},
            {id: '#treatment', data: res.totalTreat},
            {id: '#label', data: res.totalLabel}
        ];

        charts.forEach(chart => {
            let ctx = $(chart.id)[0].getContext('2d');
            let existingChart = Chart.getChart(ctx);

            if (existingChart) {
                existingChart.data.datasets[0].data = chart.data;
                existingChart.update();
            }
        });
    });

    TableProd.ajax.reload();
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