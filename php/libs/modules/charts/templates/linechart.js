(function($) {
	$(document).ready(function() {
		var id = '#' + {{id}};
        $(id).highcharts({
            chart: {
                type: 'spline',
                marginRight: 130,
                marginBottom: 40,
                zoomType: 'x',
            },
            title: {
                text: {{chart_title}},
                x: -20 //center
            },
            subtitle: {
                text: {{chart_subtitle}},
                x: -20
            },
            xAxis: {
                type: 'datetime',
                maxZoom: 1 * 2 * 3600000, // fourteen days
                title: {
                    text: {{x_title}}
                }
            },
            yAxis: {
                title: {
                    text: {{y_title}}
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: ''
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: 0,
                y: 100,
                borderWidth: 0,
                enabled: false
            },
            series: {{series}}
        });

	});
})(jQuery); 