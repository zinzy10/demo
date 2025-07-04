var chartInstances = {};

$(document).ready(function() {
	$('#active2').addClass('active');
	charts();
});

$(window).on('resize', adjustCharts);

function printChart() {

	let factory = $('#factory').val();
	let url = `../src/controllers/production.ajax.php?opc=1&factory=${factory}`;

	$.getJSON(url, (res) => {
		let ctx = $('#chartPrints')[0].getContext('2d');
		let existingChart = Chart.getChart(ctx);

		if (existingChart) {
			existingChart.data.datasets[0].data = res.print1;
          	existingChart.data.datasets[0].label = res.lastYear;
          	existingChart.data.datasets[1].data = res.print2;
          	existingChart.data.datasets[1].label = res.currentYear;
          	existingChart.update();
		}
	});
}

function labelChart() {

	let factory2 = $('#factory2').val();
	let opc = ['MP1', 'MP8', 'MP7', 'ALL'].includes(factory2) ? 2 : 0 ;
	let url = `../src/controllers/production.ajax.php?opc=${opc}&factory2=${factory2}`;

	// if (opc !== 3) {
	// 	url += `&factory2=${factory2}`;
	// }

	$.getJSON(url, (res) => {
		let ctx = $('#chartLabel')[0].getContext('2d');
		let existingChart = Chart.getChart(ctx);

		if (existingChart) {
			existingChart.data.datasets[0].data = res.label1;
          	existingChart.data.datasets[0].label = res.lastYear;
          	existingChart.data.datasets[1].data = res.label2;
          	existingChart.data.datasets[1].label = res.currentYear;
          	existingChart.update();
		}
	});
}

function charts() {
	
	let width = $(window).width();

	let sizes = [
    	{ max: 482,  x: 8,  y: 11 },
    	{ max: 768,  x: 13, y: 11 },
    	{ max: 873,  x: 8,  y: 11 },
    	{ max: 992,  x: 11, y: 11 },
    	{ max: 1400, x: 13, y: 11 },
	];

	let { x: sizeX, y: sizeY } = sizes.find(s => width < s.max) || { x: 15, y: 13 };

	$.getJSON('../src/controllers/production.ajax.php?opc=3', (res) => {
		let chartsData = [
			{id: '#chartBlanks', data1: res.blank1, data2: res.blank2},
			{id: '#chartPrints', data1: res.print1, data2: res.print2},
			{id: '#chartTreatment', data1: res.treat1, data2: res.treat2},
			{id: '#chartEmb', data1: res.embro1, data2: res.embro2},
			{id: '#chartLabel', data1: res.label1, data2: res.label2},
			{id: '#chartHits', data1: res.hits1, data2: res.hits2}
		];

		chartsData.forEach(({id, data1, data2}) => {
			let ctx = $(id)[0].getContext('2d');

			chartInstances[id] = new Chart(ctx, {
				type: 'bar',
				data: {
			  		labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
			  		datasets: [{
						label: res.lastYear,
						borderColor: '#DF7401',
						borderWidth: 1,
						backgroundColor: '#DF7401',
						data: data1
			  		},
			  		{
						label: res.currentYear,
						borderColor: '#DF7401',
						borderWidth: 1,
						backgroundColor: '#DF7401',
						data: data2
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
					  				size: sizeY,
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
			$('.loader').removeClass('loader').addClass('dark-mode');
		});
	});
}

function adjustCharts() {
	
    let sizes = [
        { width: 482, x: 8, y: 11 },
        { width: 768, x: 13, y: 11 },
        { width: 873, x: 8, y: 11 },
        { width: 992, x: 11, y: 11 },
        { width: 1400, x: 13, y: 11 },
        { width: Infinity, x: 15, y: 13 }
    ];

	let width = $(window).width();
    let { x, y } = sizes.find(size => width < size.width);

    $.each(chartInstances, (_, chartInstance) => {
        let scales = chartInstance.config.options.scales;
        scales.x.ticks.font.size = x;
        scales.y.ticks.font.size = y;
        chartInstance.update();
    });
}
