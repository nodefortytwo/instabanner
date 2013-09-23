<?php
class InstagramUser extends MongoBase{
	protected $collection = 'instagramUser';

	public function __construct($rec = null){
		parent::__construct($rec);

		$this['last_pull'] = isset($this['last_pull']) ? $this['last_pull'] : new MongoDate(0);
	}

	public function update_from_session(){
		foreach(session()->instagram->user as $key=>$value){
			$this[$key] = $value;
		}
		$this->save();
	}

	public function pull_media(){
		if(time() - $this['last_pull']->sec > $this->pull_freq){
			$media = new InstagramUserMediaPull($this['_id']);	
			$this['last_pull'] = new MongoDate();
			$this->save();		
		}
	}

	//__get function
	public function get_media(){
		$search = array('user.id' => $this['_id']);
		$this->media = new InstagramMediaCollection($search);
		return $this->media;
	}

	public function media_search($search, $limit, $sort = null){
		$search = array('user.id' => $this['_id']);
		$this->media = new InstagramMediaCollection($search, $limit);
		return $this->media;
	}

	public function get_media_random(){

		$m = $this->media;
		$m->random = true;
		return $m;
	}

	public function get_pull_freq(){
		return 3600 * 24;
	}
}

class InstagramUserMediaPull{
	private $mpr = 200;//media per request
	private $remaining = 5000, $data = array();

	function __construct($id){
		$this->url = 'https://api.instagram.com/v1/users/' . $id . '/media/recent?count='.$this->mpr.'&access_token=' . session()->instagram->access_token;
		$this->run();
	}

	function run(){
		
		list($headers, $data) = (get_data($this->url, null, true));
		$data = json_decode($data);
		$this->remaining = $headers['X-Ratelimit-Remaining'];
		$this->data = array_merge($this->data, $data->data);

		if(isset($data->pagination->next_url) && $this->remaining > 0){
			$this->url = $data->pagination->next_url;
			$this->run();
		}

		//looping has completed, process;
		$this->process();
	}

	function process(){
		foreach($this->data as $media){
			if(!isset($media->id)){
				continue;
			}
			$media = new InstagramMedia($media);
		}

	}

}