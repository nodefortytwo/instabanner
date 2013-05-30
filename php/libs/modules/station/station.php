<?php

function station_init(){
	require 'station.class.php';
}

function station_routes(){

	$routes = array();

	$routes['load/data'] = array('callback' => 'station_load_data');
	$routes['stations'] = array('callback' => 'station_list');
	$routes['station/view'] = array('callback' => 'station_view');
	$routes['stations/update'] = array('callback' => 'stations_update');
	$routes['stations/heatmap'] = array('callback' => 'stations_heatmap');
	return $routes;
}

function station_list(){
	$page = new Template();
	$stations = new StationCollection(array());
	$page->c($stations->render());

	return $page->render();
}

function station_load_data(){

	$res = mdb()->processedFiles->find();
	$pfiles = array();
	foreach($res as $f){
		$pfiles[] = $f['file'];
	}
	$base = 'http://cd.rickburgess.me/';

	$files = trim(file_get_contents($base . 'data.php'));
	$files = explode("\n", $files);
	foreach($files as $file){
		if(in_array($file, $pfiles)){
			continue;
		}

		$ts = str_replace('.json', '', $file);
		$ts = str_replace('public/', '', $ts);

		$json = file_get_contents($base . $file);
		$json = json_decode($json);
		if(!is_object($json)){
			continue;
		}
		foreach($json->aaData as $d){
			$station = new Station($d[0]);
			if(!$station->exists){
				$station['name'] = $d[1];
				$station['total_spaces'] = $d[2] + $d[3];
				$station->save();
			}

			$d[] = $ts;

			$station->add_data_point($d);
		}
		mdb()->processedFiles->insert(array('file' => $file));
	}

}


function station_view($id){
	$station = new Station($id);
	$page = new Template();

	$avgs = $station->avaliable_averages(30);
	$avgs = normalise($avgs);
	$page->c('<div class="chartcontainer">');
	foreach($avgs as $key=>$avg){
		$str = '<div class="bar" style="height:' . ($avg) . 'px; margin-top:'.(100-$avg).'px;">'  . $key . '</div>';
		$page->c($str);
	}
	$page->c('</div>');
	return $page->render();
}


function stations_heatmap($hour = 1200){
	$stations = new StationCollection(array(), 1);
	$hours = array($hour => array());

	foreach($stations as $station){
		$avg = $station->avaliable_averages(60);
		if(isset($avg[$hour])){
			$hours[$hour][] = array($station['_id'], $station['latitude'], $station['longitude'], $avg[$hour]);
		}
			
	}

	if(isset($hours[$hour])){

		foreach($hours[$hour] as $key=>$point){

			$latlng = 'new google.maps.LatLng('. $point[1] .', '.$point[2].')';
			$wobj = 'new google.maps.WeightedLocation(' . $latlng . ', ' . $point[3].')';
			$wobj = '{location:' . $latlng . ', weight:' . pow($point[3], 2) . '}';
			$hours[$hour][$key] = $wobj;
		}

		$vars = array('heatmap_data' => '['.implode(',',$hours[$hour]).']');
		$js = new Template(false);
		$js->load_template('templates/heatmap.js', 'station');
		$js->add_variable($vars);
		$js = $js->render();

		$page = new Template();
		$page->add_js('https://maps.googleapis.com/maps/api/js?key=AIzaSyDNi5HfYEItsxrSj1AXQHiQLpNUmsh46LU&sensor=false&libraries=visualization', 'station');
		$page->add_js($js, 'station', 'inline');
		$page->c('<div id="map_canvas" class="span12" style="height:700px;"></div>');
		return $page->render();

	}else{
		die('test');
	}

}

function stations_update(){
	$stations = new StationCollection(array());

	foreach($stations as $station){
		$url = 'http://api.bike-stats.co.uk/service/rest/bikestat/'.$station['_id'].'?format=json';	
		$data = json_decode(get_data($url));
		if(isset($data->dockStation)){
			$station['latitude'] = $data->dockStation->latitude;
			$station['longitude'] = $data->dockStation->longitude;
			$station->save();
		}
	}
}
