<?php
class Station extends MongoBase{
	protected $collection = 'station';


	function add_data_point($dp){
		$hours = explode(':', $dp[4]);
		$time = strtotime('today', $dp[5]);
		$time = mktime($hours[0], $hours[1], 0, date('n', $time), date('j', $time), date('Y', $time));
		
		$dp[6] = new MongoDate($time);

		$id = $this['_id'] . '_' .  $time;

		$data = new StationDataPoint($id);
		$data['station'] = $this['_id'];
		$data['avaliable'] = $dp[2];
		$data['empty'] = $dp[3];
		$data['time'] = $dp[6];
		$data['dow'] = date('N', $data['time']->sec);
		$data->save();
	}

	function get_datapoints(){
		$this->datapoints = new StationDataPointCollection(array('station' => $this['_id']));
		return $this->datapoints;
	}

	function avaliable_averages($int){

		foreach($this->datapoints as $dp){
			$t =  round_n($dp['time']->sec, $int*60);
			$grp = date('Gi', $t);
			if(!isset($avg[$grp])){
				$avg[$grp] = array(0, 0);
			}
			$avg[$grp][0] += $dp['avaliable'];
			$avg[$grp][1]++;
		}
		foreach($avg as $grp=>$vals){
			$avg[$grp] = round($vals[0] / $vals[1]);
		}
		ksort($avg);
		return $avg;
	}
}
class StationCollection extends Collection{
	protected $collection = 'station', $class_name = 'Station';
	protected $default_cols = array(
		'ID' => '_id',
		'Name' => 'name',
		'Total Bays' => 'total_spaces'
		);
}

class StationDataPoint extends MongoBase{
	protected $collection = 'stationDataPoints';

	public function load_postprocess(){
		if(!isset($this['dow'])){
			$this['dow'] = date('N', $this['time']->sec);
			$this->save();
		}
	}
}

class StationDataPointCollection extends Collection{
	protected $collection = 'stationDataPoints', $class_name = 'StationDataPoint';

}