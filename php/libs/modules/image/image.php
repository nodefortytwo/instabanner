<?php

function image_routes(){
	$routes= array();

	$routes['image/create'] = array('callback' => 'image_create');

	return $routes;
}

function image_types($id = null){
	$images = array();
	$images['twitter_profile'] = array(
						'id' => 'tpb',
						'title' => 'Twitter profile',
						'width' => 1252,
						'height' => 626,
						'target_size' => 150,
						'cols' => 7);

	$images['twitter_backround'] = array(
						'id' => 'tbi',
						'title' => 'Twitter background',
						'width' => 2048,
						'height' => 2048,
						'target_size' => 306,
						'cols' => 8);

	$images['facebook_cover'] = array(
						'id' => 'fcp',
						'title' => 'Facebook cover photo',
						'width' => 850,
						'height' => 350,
						'target_size' => 150,
						'cols' => 4);

	$images['iphone4s'] = array(
						'id' => 'iphone4s',
						'title' => 'iPhone 4 background',
						'width' => 640,
						'height' => 960,
						'target_size' => 306,
						'cols' => 4);
	$images['iphone5'] = array(
						'id' => 'iphone5',
						'title' => 'iPhone 5 background',
						'width' => 640,
						'height' => 1136,
						'target_size' => 306,
						'cols' => 4);
	$images['google'] = array(
						'id' => 'google',
						'title' => 'Google cover photo',
						'width' => 890,
						'height' => 180,
						'target_size' => 150,
						'cols' => 4);
	if($id && isset($images[$id])){
		return $images[$id];
	}
	return $images;
}


function image_create_form(){

	$form = new Form(array(
			'action' => get_url('/image/create'),
			'method' => 'POST'
		));

	$options = array();
	foreach(image_types() as $key=>$image){
		$options[$key] = $image['title'];
	}


	$form->e(array(
		'type' => 'select',
		'id' => 'type',
		'options' => $options,
		'label' => 'Image Type'
		));
	$form->e(array(
		'type' => 'radio',
		'id' => 'order',
		'options' => array('rand' => 'Random', 'latest' => 'Latest'),
		'label' => 'Image Order',
		'option_label_class' => 'inline'
		));
	$form->e(array(
		'type' => 'submit',
		'text' => 'Create Image',
		'class' => 'pull-right'
		));

	return '<div class="row-fluid"><div class="span12 well">'.$form->render() . '</div></div>';
}

function image_create(){
	
	$type = get('type');
	$order = get('order');
	if(empty($type) || empty($order)){
		message('missing info');
		redirect('/user');
	}

	$type = image_types($type);

	//instagram images are square (shock horror!) so we need to find the common factors
	//between the dest image height and width so we don't cur off images, this won't always be 
	//possible. when that happens we choose a decent size and cut off images at the bottom

	$target_size = $type['target_size'];

	$factors = common_factors($type['width'], $type['height']);

	if(count($factors) > 1){
		//we have some common factors (other than 1 of course)
		//find the nearest one to our target size
		$dif = 1000000;//stupid i know
		$match_id = 0;
		foreach($factors as $key => $f){
			if($f < 100){
				continue;
			}
			$d = abs($f - $target_size);
			if($d < $dif){
				$match_id = $key;
				$dif = $d;
			}
		}
		$size = ceil($factors[$match_id] / 2);
	}

	if(!isset($size)){
		//we couldn't find a factor, so lets width match
		$size = ceil($type['width'] / round($type['width'] / $target_size));
		//we don't want fractions becuase, you know, pixels, so ceil to avoid a black line;
	}


	$cols = $type['width'] / $size;
	$rows = $type['height'] / $size;
	$images_required = $cols * $rows;
	

	//get the images we need
	$user = current_user();
	if($order == 'rand'){
		$images = $user->media_random;
		$images->limit = $images_required;
	}else{
		$images = $user->media_search(array(), $images_required);
	}
	//if we don't have enough images to complete the image give up.
	if($images->cnt < $images_required){
		message('This image type requires ' . $images_required . ' images to complete, get snapping!');
		redirect('/user');
	}
	//yay we have enough images! Load them into memory
	$imgs = array();
	foreach($images as $image){
		$imgs[] = array($image['_id'], $image['images.low_resolution.url']);
	}
	unset($images);//destroy the collection

	//loop through every col/row
	$map = array();
	$iid = 0;
	$im = imagecreatetruecolor($type['width'], $type['height']);
	for ($r = 0; $r < $rows; $r++) {
		for ($c = 0; $c < $cols; $c++) {
			$img = $imgs[$iid];
			$iid++;
			$cur_img = imagecreatefromjpeg($img[1]);
			$new_size = $size;
			$x = $new_size * $c;
			$y = $new_size * $r;
			$map[] = array($x, $y, $x + $new_size, $y + $new_size, $image);
			imagecopyresized($im, $cur_img, $x, $y, 0, 0, $new_size, $new_size, 306, 306);
		}
	}
	
	header('Content-type: image/jpeg');
	imagejpeg($im);
	die();

	var_dump($images->cnt);

	var_dump($cols, $rows, $images_required);
}

