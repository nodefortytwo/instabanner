<?php
function instagram_init(){
	require 'instagram.user.class.php';
	require 'instagram.media.class.php';
}

function instagram_routes(){
	$routes = array();
	$routes['instagram/connect'] = array('callback' => 'instagram_connect');
	$routes['instagram/callback'] = array('callback' => 'instagram_callback');
	$routes['instagram/pull/media'] = array('callback' => 'instagram_pull_media');
	$routes['user'] = array('callback' => 'instagram_user');
	return $routes;
}

function instagram_connect_button(){
	return l('Connect with Instagram', '/instagram/connect/', 'btn btn-primary btn-large');
}

function instagram_connect(){
	$url = 'http://' .  config('HOST') . '/instagram/callback/';
	redirect('https://api.instagram.com/oauth/authorize/?client_id=' .  config('INSTAGRAM_ID') . '&redirect_uri=' . $url . '&response_type=code', 301, false);
}

function instagram_callback(){
	if(!get('code')){
		redirect('/instagram/connect/');
	}

	$postfields = array(
		'client_id' => config('INSTAGRAM_ID'),
		'client_secret' => config('INSTAGRAM_SECRET'),
		'grant_type' => 'authorization_code', 
		'redirect_uri' =>  'http://' .  config('HOST') . '/instagram/callback/',
		'code' => get('code')
	);
	
	$url = 'https://api.instagram.com/oauth/access_token';

	$data = json_decode(get_data($url, $postfields));
	if(is_object($data) && isset($data->access_token)){
		session(1)->instagram = $data;
		redirect('/user');
	}else{
		message('Sorry there was an error connecting to Instagram, Please try again');
		redirect('/');
	}
}

function current_user(){
	if(!is_null(session()->instagram)){
		$user = new InstagramUser(session()->instagram->user->id);
		if(!$user->exists){
			$user->update_from_session();	
		}
		return $user;
	}else{
		return false;
	}
}

function instagram_user(){
	$user = current_user();
	if(!is_object($user)){
		redirect('/');
	}
	$history = '';
	$content = new Template(false);
	$content->load_template('templates/user.html', 'instagram');
	$vars = array(
			'username' => $user['username'],
			'media_count' => $user->media->cnt,
			'last_pull' => template_date($user['last_pull']),
			'pull_media' => l('Load Images', '/instagram/pull/media', 'btn'),
			'image_create_form' => image_create_form(),
			'history' => $history
		);

	if ($user->media->cnt < 64){
		$vars['image_create_form'] = '<p>Before you can create an instabanner, you need to '.l('load your Instagram images', '/instagram/pull/media').' into instabanner</p>';
	}

	$content->add_variable($vars);
	$page = new Template();

	$page->c($content->render());
	$page->c($user->media_random->render('gallery'));

	return $page->render();
}


function instagram_pull_media(){
	$user = current_user();
	$user->pull_media();
	redirect('/user');
}