<?php 
	/*
	Plugin Name: Brightkite
	Plugin URI: http://brightkite.com
	Description: Display your latest Brightkite checkins
	Author: Michael Lavrisha
	Version: 0.1
	Author URI: http://www.brightkite.com/people/vrish88
	*/

function brightkite_get_checkins($numCheckins = 10) {
	$feedURL = 'http://brightkite.com/people/'.get_option('bk-username').'/objects.xml?filters=checkins';
  $numCheckins = get_option('bk-numCheckins');
  if(empty($numCheckins)) {
    $numCheckins = 10;
  }
	$feedObject = simplexml_load_file($feedURL . '&limit=' . $numCheckins);
	$objects = $feedObject->objects;
	//array_unique($items);
	echo '<ul>';
	$count = 0;
//	var_dump($items);
	foreach ($feedObject->checkin as $item) {
		if ($item->place->name != '') {
			echo '<li><a href="http://brightkite.com/objects/'. $item->id .'">' . $item->place->name . '</a></li>';
		$count++;
		if ($count == $numCheckins) {break;}
		}
	}
	echo '</ul>';
}

function widget_brightkite($args) {
  extract($args);
  echo $before_widget.$before_title.'brightkite'.$after_title;
  brightkite_get_checkins();
  echo $after_widget;
}

function brightkite_init() {
  register_sidebar_widget(__('Brightkite'), 'widget_brightkite');
}

function brightkite_admin() {  
	include('brightkite-admin.php');  
}

function brightkite_admin_actions() {
	add_options_page("Brightkite", "Brightkite", 1, "Brightkite", "brightkite_admin");
	
}

add_action('admin_menu', 'brightkite_admin_actions');
add_action('plugins_loaded', 'brightkite_init');

?>
