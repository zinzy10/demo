var chartInstances = {};
var goals;

$(document).ready(function() {
    $('#active').addClass('active');
    tableGoals();
    saleChart();
    remove();
    resize();
    // new TomSelect('#clients', {
    //     create: false
    // });
});

$(window).on('resize', function() {
    resize();
    adjustCharts();
});

function tableGoals() {
    
    goals = $('#tableClient').DataTable({
        ajax: '../src/controllers/sales.ajax.php?opc=8',
        pageLength: 20,
        info: false,
        lengthChange: false,
        pagingType: 'full_numbers',
        order: [[ 4, 'desc' ]],
        columnDefs: [
          { orderable: false, targets: [0] }
        ]
    });

    goals.on('draw', function() {
        //Funcion para limpiar y convertir valores a numero
        let parseNumber = val => {
            if (!val) return 0;
            let clean = val.replace(/[$,]/g, '');   //Elimina S y comas
            return parseFloat(clean) || 0;  //Convierte a numero
        };

        //Funcion para formatear como moneda
        let formatCurrency = val => Intl.NumberFormat('en-US', {
            style: 'currency', currency: 'USD'
        }).format(val);

        //Suman columnas con conversion segura
        let totals = [1, 2, 3, 4].map(index => {
            return goals
            .data()
            .toArray()
            .reduce((acc, row) => {
                let val = $('<div>').html(row[index]).text(); //Extrae texto plano
                return acc + parseNumber(val);
            }, 0);
        });

        //Insertar valores en los footers
        totals.forEach((total, i) => {
            goals.columns(i + 1).footer().to$().html(formatCurrency(total));
        });
    });
}

function saleChart() {

    let fontSize = {x: 14, y: 12};

    if ($(window).width() < 486) {
        fontSize = {x: 8, y: 11};
    } else if ($(window).width() >= 768 && $(window).width() <= 970) {
        fontSize = {x: 9, y: 10};
    }

    $.getJSON('../src/controllers/sales.ajax.php?opc=1', (res) => {
        let chartsData = [
            {id: '#chart-sales'},
            {id: '#salesClients'}
        ];

        chartsData.forEach(({id}) => {
            let ctx = $(id)[0].getContext('2d');

            chartInstances[id] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                    datasets: [{
                        label: res.year,
                        tension: 0,
                        borderWidth: 0,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(255, 255, 255, .8)',
                        pointBorderColor: 'transparent',
                        borderColor: 'rgba(255, 255, 255, .8)',
                        borderWidth: 4,
                        backgroundColor: 'transparent',
                        fill: true,
                        data: res.totalYear,
                        maxBarThickness: 5
                    },
                    {
                        label:'',
                        tension: 0,
                        borderWidth: 0,
                        pointRadius: 5,
                        pointBackgroundColor: '#DF7401',
                        pointBorderColor: 'transparent',
                        borderColor: 'transparent',
                        borderWidth: 4,
                        backgroundColor: 'transparent',
                        fill: true,
                        data: [],
                        maxBarThickness: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels:{
                                color: 'white',
                            }
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
                                    size: fontSize.y,
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
                                    size: fontSize.x,
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
        });
    });
}

function chartMonth() {

    let years1 = $('#year1').val();
    let years2 = $('#year2').val();
    let isValid = true;

    if (!years1 && !years2) {
        $('#year1, #year2').addClass('is-invalid');
        isValid = false;
    } else {
        if (!years1) {
            $('#year1').addClass('is-invalid');
            isValid = false;
        }
        if (!years2) {
            $('#year2').addClass('is-invalid');
            isValid = false;
        }
    }

    if (!isValid) return;

    $.getJSON('../src/controllers/sales.ajax.php', {opc:2, years1:years1, years2:years2}, (data) => {
        let month = $('#chart-sales')[0].getContext('2d');
        let existingChart = Chart.getChart(month);

        existingChart.data.datasets[0].data = data.totales1;
        existingChart.data.datasets[0].label = years1;
        existingChart.data.datasets[1].data = data.totales2;
        existingChart.data.datasets[1].label = years2;
        existingChart.data.datasets[1].borderColor = '#DF7401';
        existingChart.update();
    });
}

function cleanSales() {

    $('#year1, #year2').val('0').removeClass('is-invalid');

    $.getJSON('../src/controllers/sales.ajax.php?opc=1', (res) => {
        let month = $('#chart-sales')[0].getContext('2d');
        let existingChart = Chart.getChart(month);

        existingChart.data.datasets[0].data = res.totalYear;
        existingChart.data.datasets[0].label = res.year;
        existingChart.data.datasets[1].data = [];
        existingChart.data.datasets[1].label = "";
        existingChart.data.datasets[1].borderColor = 'transparent';
        existingChart.update();
    });
}

function chartsClients() {

    let clients = $('#clients').val() || '';
    clients = clients ? encodeURIComponent(clients) : '';
    let years3 = $('#year3').val();
    let years4 = $('#year4').val();
    let isValid = true;

    if (!clients && !years3 && !years4) {
        $('#clients, #year3, #year4').addClass('is-invalid');
        isValid = false;
    } else {
        if (!clients) {
            $('#clients').addClass('is-invalid');
            isValid = false;
        }
        if (!years3) {
            $('#year3').addClass('is-invalid');
            isValid = false;
        }
        if (!years4) {
            $('#year4').addClass('is-invalid');
            isValid = false;
        }
    }

    if (!isValid) return;

    $.getJSON(`../src/controllers/sales.ajax.php?opc=3&clients=${clients}&years3=${years3}&years4=${years4}`, (data) => {

        let month = $('#salesClients')[0].getContext('2d');
        let existingChart = Chart.getChart(month);

        if (existingChart) {
            existingChart.data.datasets[0].data = data.totales1;
            existingChart.data.datasets[0].label = years3;
            existingChart.data.datasets[0].borderColor = 'rgba(255, 255, 255, .8)';
            existingChart.data.datasets[0].pointBackgroundColor = 'rgba(255, 255, 255, .8)';

            existingChart.data.datasets[1].data = data.totales2;
            existingChart.data.datasets[1].label = years4;
            existingChart.data.datasets[1].borderColor = '#DF7401';
            existingChart.data.datasets[1].pointBackgroundColor = '#DF7401';

            existingChart.update();
        }
    });
}

function cleanClients() {
    
    let option = $('#year3 option:first').prop('outerHTML');
    $('#year3, #year4').html(option).removeClass('is-invalid');
    $('#clients').val('0').removeClass('is-invalid');

    $.getJSON('../src/controllers/sales.ajax.php?opc=1', (res) => {
        let month = $('#salesClients')[0].getContext('2d');
        let existingChart = Chart.getChart(month);

        existingChart.data.datasets[0].data = res.totalYear;
        existingChart.data.datasets[0].label = res.year;
        existingChart.data.datasets[1].data = [];
        existingChart.data.datasets[1].label = "";
        existingChart.data.datasets[1].borderColor = 'transparent';
        existingChart.update();
    });
}

function clientList() {
  
    let Client = $('#clients').val();
    let option = $('#year3 option:first').prop('outerHTML');
    
    $.ajax({
        url: '../src/controllers/sales.ajax.php?opc=4',
        method: 'POST',
        data: {Client:Client},
        success: function(res) {
            $('#year3').html(option + res);
            $('#year4').html(option + res);
        },
        error: function(xhr, status, error) {
            console.log(xhr);
        }
    });
}

function remove() {

    $('#clients').on('change', function() {
        $(this).removeClass('is-invalid');
    });

    $('#year1').on('change', function() {
        $(this).removeClass('is-invalid');
    });

    $('#year2').on('change', function() {
        $(this).removeClass('is-invalid');
    });

    $('#year3').on('change', function() {
        $(this).removeClass('is-invalid');
    });

    $('#year4').on('change', function() {
        $(this).removeClass('is-invalid');
    });
}

function resize() {
    
    let smallScreen = $(window).width() < 500;

    $('#selects, #selects2').toggleClass('flex-column', smallScreen);
    $('#year2, #year3, #year4').toggleClass('ms-3', !smallScreen).toggleClass('mt-3', smallScreen);
    $('#buttons, #buttons2').toggleClass('mt-3 justify-content-center', smallScreen).toggleClass('ms-3', !smallScreen);
}

function adjustCharts() {

    let fontSize = {x: 14, y: 12};

    if ($(window).width() < 486) {
        fontSize = {x: 8, y: 11};
    } else if ($(window).width() >= 768 && $(window).width() <= 970) {
        fontSize = {x: 9, y: 10};
    }

    $.each(chartInstances, function(key, chartInstance) {
        let scales = chartInstance.config.options.scales;
        scales.x.ticks.font.size = fontSize.x;
        scales.y.ticks.font.size = fontSize.y;
        chartInstance.update();
    });
}