<?php

function image_routes(){
	$routes= array();

	$routes['image/create'] = array('callback' => 'image_create');
	$routes['image/view'] = array('callback' => 'image_view');

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
						'width' => 2120,
						'height' => 1192,
						'target_size' => 150,
						'cols' => 8);
	if($id && isset($images[$id])){
		return $images[$id];
	}
	return $images;
}

function image_url($id){
	return config('UPLOAD_PATH') . '/' . $iid . '.jpg';
}

function image_exists($id){
	return file_exists(image_url($id));
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

function image_view($iid){
	$path = '/' . config('UPLOAD_PATH') . '/' . $iid . '.jpg';

	$vars = array(
		'image_path' => $path
		);

	$page = new Template();
	$content = new Template(false);
	$content->load_template('templates/image.view.html', 'image');
	$content->add_variable($vars);
	$page->c($content->render());

	return $page->render();
}

function image_create(){
	
	$type = get('type');
	$order = get('order');
	if(empty($type) || empty($order)){
		message('missing info');
		redirect('/user');
	}

	$type = image_types($type);

	$user = current_user();
	if($order == 'rand'){
		$images = $user->media_random;
	}else{
		$images = $user->media;
	}

	$args = array(
			'type' => $type
		);

	$id = $images->render('image', $args);

	$user['generated_images.[]'] = $id;
	$user->save();

	redirect('/image/view/~/' . $id);
}

