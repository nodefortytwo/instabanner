<?php
class InstagramMedia extends MongoBase{
	protected $collection = 'instagramMedia';

	public function load_from_object($obj){
		$this->_id = $obj->id;
		$this['_id'] = $obj->id;
		unset($obj->id);
		foreach($obj as $key=>$val){
			$this[$key] = $val;
		}
		$this->save();
		$this->load_from_id();
	}
}

class InstagramMediaCollection extends Collection{
	protected $collection = 'instagramMedia', $class_name = 'InstagramMedia';


	function render_gallery($style = 'gallery', $args = array()){

		$html = '<div class="row-fluid">';
		$c = 0;
		$t = 0;
		$w = 2;
		$cols = 12/$w;
		$rows = 5;

		foreach($this as $media){
			$c++;
			$t++;
			//var_dump($media['images.low_resolution.url']);
			$html .= '<div class="span2"><img src="' . $media['images.low_resolution.url'] . '" class="img-polaroid" width="100%" style="margin-top:10px;"/></div>';
			//die();
			if($t == ($cols * $rows)){
				break;
			}
			if($c == $cols){
				$c = 0;
				$html .= '</div><div class="row-fluid">';
			}
		}

		$html .= '</div>';
		return $html;
	}

}