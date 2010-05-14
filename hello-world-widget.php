<?php
/*
 * Plugin Name: Hello World Example
 * Version: 1.0
 * Plugin URI: http://jessealtman.com/2009/06/08/tutorial-wordpress-28-widget-api/
 * Description: Hello World example widget using the the WordPress 2.8 widget API. This is meant strictly as a means of showing the new API using the <a href="http://jessealtman.com/2009/06/08/tutorial-wordpress-28-widget-api/">tutorial</a>.
 * Author: Jesse Altman
 * Author URI: http://jessealtman.com/
 */
class HelloWorldWidget extends WP_Widget
{
	/**
	* Declares the HelloWorldWidget class.
	*
	*/
	function HelloWorldWidget(){
		$widget_ops = array('classname' => 'widget_hello_world', 'description' => __( "Example widget demoing WordPress 2.8 widget API") );
		$control_ops = array('width' => 300, 'height' => 300);
		$this->WP_Widget('helloworld', __('Hello World Example'), $widget_ops, $control_ops);
	}
	
	/**
	* Displays the Widget
	*
	*/
	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$lineOne = empty($instance['lineOne']) ? 'Hello' : $instance['lineOne'];
		$lineTwo = empty($instance['lineTwo']) ? 'World' : $instance['lineTwo'];
		
		# Before the widget
		echo $before_widget;
		
		# The title
		if ( $title )
			echo $before_title . $title . $after_title;
		
		# Make the Hello World Example widget
		echo '<div style="text-align:center;padding:10px;">' . $lineOne . '<br />' . $lineTwo . "</div>";
		
		# After the widget
		echo $after_widget;
	}
	
	/**
	* Saves the widgets settings.
	*
	*/
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['lineOne'] = strip_tags(stripslashes($new_instance['lineOne']));
		$instance['lineTwo'] = strip_tags(stripslashes($new_instance['lineTwo']));
		
		return $instance;
	}
	
	/**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>'', 'lineOne'=>'Hello', 'lineTwo'=>'World') );
		
		$title = htmlspecialchars($instance['title']);
		$lineOne = htmlspecialchars($instance['lineOne']);
		$lineTwo = htmlspecialchars($instance['lineTwo']);
		
		# Output the options
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
		# Text line 1
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('lineOne') . '">' . __('Line 1 text:') . ' <input style="width: 200px;" id="' . $this->get_field_id('lineOne') . '" name="' . $this->get_field_name('lineOne') . '" type="text" value="' . $lineOne . '" /></label></p>';
		# Text line 2
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('lineTwo') . '">' . __('Line 2 text:') . ' <input style="width: 200px;" id="' . $this->get_field_id('lineTwo') . '" name="' . $this->get_field_name('lineTwo') . '" type="text" value="' . $lineTwo . '" /></label></p>';
	}

}// END class
	
	/**
	* Register Hello World widget.
	*
	* Calls 'widgets_init' action after the Hello World widget has been registered.
	*/
	function HelloWorldInit() {
	register_widget('HelloWorldWidget');
	}	
	add_action('widgets_init', 'HelloWorldInit');
?>