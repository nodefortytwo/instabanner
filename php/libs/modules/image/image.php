<?php
function image_init(){
	require 'image.custom.class.php';
}
function image_routes(){
	$routes= array();

	$routes['image/create'] = array('callback' => 'image_create');
	$routes['image/view'] = array('callback' => 'image_view');
	$routes['image/custom/layouts'] = array('callback' => 'image_custom_layouts');
	$routes['image/custom/create'] = array('callback' => 'image_custom_layout');
	$routes['image/custom/save'] = array('callback' => 'image_custom_save');
	$routes['image/get_types'] = array('callback' => 'get_image_types');
	

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
						'width' => 2100,
						'height' => 1200,
						'target_size' => 150,
						'cols' => 8);
	if($id && isset($images[$id])){
		return $images[$id];
	}
	return $images;
}

function get_image_types($id = null){
	return json_encode(image_types($id));
}

function image_url($id){
	return config('UPLOAD_PATH') . '/' . $id . '.png';
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
	$path =image_url($iid);
	if(!image_exists($iid)){
		message('That image no longer exists, sorry :(');
		redirect('/user');
	}

	$vars = array(
		'image_path' => '/'.$path
		);

	$page = new Template();
	$content = new Template(false);
	$content->load_template('templates/image.view.html', 'image');
	$content->add_variable($vars);
	$page->c($content->render());

	return $page->render();
}

function image_create($id = null){
	if(!$id){
		$type = get('type');
		$order = get('order');
		if(empty($type)){
			message('missing info');
			redirect('/user');
		}
		$type = image_types($type);
		
		$page = new Template();
		$page->add_js('js/select_layout.js', 'image');
		$page->c('<h1>Choose your layout</h1>');
		$page->c('<p>Click one of the layouts below to create your banner</p>');
		$search = array('h'=>(string)$type['height'], 'w'=>(string)$type['width']);
		$images = new ImageCustomCollection($search);
		$page->c($images->render('gallery'));
		return $page->render();
	}

	$order = 'random';
	$page = new Template();
	$image = new ImageCustom($id);

	//$page->add_js('js/image_create.js', 'image');
	//$page->load_template('templates/image_create.html', 'image');

	$user = current_user();
	if($order == 'random'){
		$images = $user->media_random;
	}else{
		$images = $user->media;
	}

	$page->c($image->render('image_tag', array('source' => $images)));
	return $page->render();

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


function homepage_image(){

	$id = var_get('homepage_image', null);
	if($id && image_exists($id)){
		return $id;
	}

	$image = array(
			'width' => 1600,
			'height' => 320,
			'cols' => 10
		);

	$images = new InstagramMediaCollection(array());
	$images->random = true;
	$id = $images->render('image', array('type' => $image));
	var_set('homepage_image', $id);
	return $id;
}

function image_custom_layouts(){
	$search = array('author' => current_user()->_id);
	$images = new ImageCustomCollection($search);
	$page = new Template();
	$page->c($images->render('gallery'));
	return $page->render();
}

function image_custom_layout(){
	$options = array();
	foreach(image_types() as $key=>$image){
		$options[$key] = $image['title'];
	}
	$def = image_types();
	//shuffle($def);
	$def = array_shift($def);

	$options['custom'] = 'Custom';
	$form = new Form(array('class' => 'form-inline span9'));
	$form->e(array(
		'type' => 'select',
		'id' => 'type',
		'options' => $options,
		'selected' => $def['id'],
		'label' => 'Starting Image Type',
		'class' => 'span4'
		));
	$form->e(array(
		'type' => 'text',
		'id' => 'width',
		'options' => array('rand' => 'Random', 'latest' => 'Latest'),
		'label' => 'Width',
		'option_label_class' => 'inline',
		'class' => 'span4',
		'default' => $def['width']
		));
	$form->e(array(
		'type' => 'text',
		'id' => 'height',
		'options' => array('rand' => 'Random', 'latest' => 'Latest'),
		'label' => 'Height',
		'option_label_class' => 'inline',
		'class' => 'span4',
		'default' => $def['height']
		));
	$vars = array();
	$vars['form'] = $form->render();

	$page = new Template();
	$page->add_js('js/jquery-ui-1.10.3.custom.min.js', 'image');
	$page->add_js('js/custom_layout.js', 'image');
	$page->add_css('css/jquery-ui-1.10.3.custom.min.css', 'image');
	$page->add_css('css/custom_grid.css', 'image');
	$content = new Template(false);
	$content->load_template('templates/custom_layout.html', 'image');
	$content->add_variable($vars);
	$page->c($content->render());

	return $page->render();
}

function image_custom_save(){
	if(!empty($_POST['_id'])){
		$image = new ImageCustom($_POST['_id']);
	}else{
		$image = new ImageCustom();
	}
	$image['author'] = current_user()->_id;

	foreach($_POST as $field => $value){
		if(!empty($value)){
			$image[$field] = $value;
		}
	}
	$image->save();
	return json_encode(array('_id' => (string) $image->_id));
}

