<?php
class ImageCustom extends MongoBase{
	protected $collection = "imageCustom", $obj_id = true;

	function get_source_image(){
		if(!isset($this->source)){
			return null;
		}
		$this->source->next();
		return $this->source->current();
	}

	function render_image($args = array()){

		$this->source = $args['source'];
		$width = $this['w'];
		$height = $this['h'];

		$im = imagecreatetruecolor($this['w'], $this['h']);
		foreach($this['layout'] as $key=>$image){

			

			$tl = explode('-', $image[0]);
			$tl[0] = $tl[0] * $this['gs'];
			$tl[1] = $tl[1] * $this['gs'];
			$image['size'] = sqrt(count($image)) * $this['gs'];
			$source = $this->get_source_image()->to_gd($image['size']);

			imagecopyresampled($im, $source, $tl[1], $tl[0], 0, 0, $image['size'], $image['size'], imagesx($source), imagesy($source));

		}
		ob_start();
		imagepng($im);
		$imagevariable = ob_get_contents();
		imagedestroy($im);
		ob_end_clean();

		$file = new File();
		$file->create($imagevariable, null, 'png');
		return $file->get_path();
	}

	function render_image_tag($args = array()){
		$path = $this->render('image', $args);
		return '<img src="' . $path . '" class="img-polaroid" data-id="'.$this->_id.'"/>';
	}

	function render_thumbnail_image($args = array()){
		$width = $this['w'];
		$height = $this['h'];

		$im = imagecreatetruecolor($this['w'], $this['h']);
		foreach($this['layout'] as $key=>$image){
			$tl = explode('-', $image[0]);
			$tl[0] = $tl[0] * $this['gs'];
			$tl[1] = $tl[1] * $this['gs'];
			$image['size'] = sqrt(count($image)) * $this['gs'];
			$image['tl'] = $tl;
			$rnd = rand(0,255);
			$col = imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
			imagefilledrectangle ($im,  $tl[1]+1, $tl[0]+1 , $tl[1]+$image['size']-2 , $tl[0]+$image['size']-2 , $col);
		}

		if(isset($args['width'])){
			$width = $args['width'];
			$height = $this['h'] * ($args['width'] / $this['w']);
			$thumb = imagecreatetruecolor($width, $height);
			imagecopyresized($thumb, $im, 0, 0, 0, 0, $width, $height, $this['w'], $this['h']);
			$im = $thumb;
		}	

		$path = config('UPLOAD_PATH') . '/' . $this->_id . '_' . $width . '_' . $height . '.png';

		//header('Content-Type: image/png');
		imagepng($im, $path, 0);
		imagedestroy($im);
		return $path;
	}

	function render_thumbnail_image_tag($args = array()){
		$path = $this->render('thumbnail_image', $args);
		return '<img src="/' . $path . '" class="img-polaroid" data-id="'.$this->_id.'"/>';
	}

}

class ImageCustomCollection extends Collection{
	protected $collection = "imageCustom", $class_name = 'ImageCustom';

	function render_gallery($args = array()){
	
		$html = '<div class="row-fluid gallery">';
		$c = 0;
		$t = 0;
		$w = 2;
		$cols = 12/$w;
		$rows = 5;

		foreach($this as $image){
			$c++;
			$t++;
			//var_dump($media['images.low_resolution.url']);
			$html .= '<div class="span2">';
			$html .= $image->render('thumbnail_image_tag', array('width' => 400));
			//$html .= '<br/>' . $image['w'] . 'x' . $image['h'];
			$html .='</div>';
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