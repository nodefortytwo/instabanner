(function($) {
	$(document).ready(function() {
		var heatmapData = {{heatmap_data}}
		var centre = new google.maps.LatLng(51.507222, -0.1275);
		map = new google.maps.Map(document.getElementById('map_canvas'), {
			center : centre,
			zoom : 13,
			mapTypeId : google.maps.MapTypeId.SATELLITE
		});

		var options = []

		var heatmap = new google.maps.visualization.HeatmapLayer({
			data : heatmapData
		});
		heatmap.setMap(map);
	});
})(jQuery); 