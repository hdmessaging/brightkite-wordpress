<?php
/*
 * Plugin Name: Brightkite Place Widhet
 * Version: 1.0
 * Plugin URI:
 * Description: Wordpress widget to display Brightkite activity at and around a place.
 * Author: Jordan Harband <jordan@brightkite.com>
 * Author URI: http://twitter.com/ljharb
 ** This plugin was started with the example at http://jessealtman.com/2009/06/08/tutorial-wordpress-28-widget-api/
 */
require_once('brightkite_functions.php');
class BrightkitePlaceWidget extends WP_Widget {
	/**
	* Declares the BrightkitePlaceWidget class.
	*
	*/
	function BrightkitePlaceWidget() {
		$widget_ops = array(
			'classname' => 'widget_brightkite_place',
			'description' => __( "Wordpress widget to display Brightkite activity at and around a place.")
		);
		$control_ops = array('width' => 400, 'height' => 500);
		$this->WP_Widget('brightkite_place', __('Brightkite Place Widget'), $widget_ops, $control_ops);
	}

	/**
	* Displays the widget on the blog
	*
	*/
	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$place_url = empty($instance['place_url']) ? '' : $instance['place_url'];
		$place_id = BK_extract_place_id($place_url);
		$display_settings = explode(',', $instance['display_settings']);
		$filter_settings = explode(',', $instance['filter_settings']);
		$count = (int)$instance['count'];

		if (strlen($place_url) > 0 && isset($place_id) && strlen($place_id) > 0) {

			$place_data_url = BK_HOST.'/places/'.$place_id.'.json';
			$stream_url = BK_HOST.'/objects.json?place_id='.$place_id.'&filter='.implode('%2C', $filter_settings).'&limit='.(int)$count;

			$json = wp_remote_fopen($place_data_url);
			$data = BK_json_decode($json);

			if (is_array($data) && array_key_exists('id', $data)) {
				/* Before the widget */
				echo $before_widget;

				/* the title */
				if ($title) { echo $before_title . $title . $after_title; }

				/* Make the widget */
	?>
				<div class="brightkite-place-data" id="<?php echo $this->get_field_id('brightkite-place') ?>">
					<div>
						<a href="<?php echo BK_HOST ?>/places/<?php echo $place_id ?>/">
	<?php	if (in_array('name', $display_settings) && array_key_exists('name', $data)) {	?>
							<span class="brightkite-place-name"><?php echo BK_emoji_decode($data['name']) ?></span>
	<?php	}	?>
	<?php	if (in_array('address', $display_settings) && in_array('display_location', $data)) {	?>
							<span class="brightkite-place-displaylocation"><?php echo $data['display_location'] ?></span>
	<?php	}
			if (in_array('crumbs', $display_settings) && array_key_exists('place_hierarchy', $data)) {	?>
							<span class="brightkite-place-hierarchy"><?php echo BK_construct_hierarchy($data['place_hierarchy']) ?></span>
	<?php	}	?>
						</a>
					</div>
	<?php	if (in_array('desc', $display_settings)) {	?>
					<div class="brightkite-place-description"><?php echo $data['description'] ?></div>
	<?php	}
			flush();
			$json = wp_remote_fopen($stream_url);
			$objects = BK_json_decode($json);
	?>
					<ul class="brightkite-stream">
	<?php
			for ($i = 0; $i < count($objects); ++$i) {
				$obj = $objects[$i];
	?>
					<li>
	<?php
		if (array_key_exists('creator', $obj)) {
	?>
			<div class="brightkite-post-creator">
				<a href="<?php echo BK_HOST ?>/people/<?php echo $obj['creator']['login'] ?>/">
					<span><?php echo $obj['creator']['login'] . '<br />('.BK_emoji_decode($obj['creator']['fullname']).')' ?></span>
					<img src="<?php echo $obj['creator']['small_avatar_url'] ?>" />
				</a>
				<div class="clear"></div>
			</div>
	<?php
		}
	?>
	<?php
		if (array_key_exists('photo', $obj)) {
	?>
						<div class="brightkite-object-image">
							<a rel="external" href="<?php echo BK_HOST ?>/photos/<?php echo $obj['id'] ?>">
								<img src="<?php echo preg_replace('/(\.(png|jpg))/', '-small.$2', $obj['photo']) ?>" />
							</a>
						</div>
	<?php
		}
		if (array_key_exists('body', $obj)) {
	?>
						<p><?php echo BK_linkify($obj['body']) ?></p>
	<?php
		}
		if (array_key_exists('photo', $obj) || array_key_exists('body', $obj)) {
	?>
						<div class="clear"></div>
	<?php
		}
		if (array_key_exists('place', $obj) && array_key_exists('name', $obj['place'])) {
			if (array_key_exists('created_at_as_words', $obj)) {
				$ago = ' <a rel="external" href="'.BK_create_object_url($obj['id']).'" class="brightkite-time-link">';
				if ($obj['object_type'] === 'checkin') { $ago .= 'checked in '; }
				$ago .= $obj['created_at_as_words'].' ago</a>';
			} else { $ago = ''; }
	?>
						<div class="brightkite-location"><?php echo $ago ?></div>
	<?php
		}
	?>
					</li>
	<?php
			}
	?>
					</ul>
				</div>
	<?php
				/* After the widget */
				echo $after_widget;
			}
		}
	}

	/**
	* Saves the widget's settings.
	*
	*/
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));

	/* BK place URL
		** Brightkite place urls should be in the expected format,
		** and most importantly, should contain the place ID.
		** Only change the saved place URL if it's valid or empty.
		*/
		$url_new = $new_instance['place_url'];
		$place_id = BK_extract_place_id($url_new);
		$hasPlaceID = isset($place_id) && strlen($place_id) > 0;
		if ($url_new === '' || $hasPlaceID) { $instance['place_url'] = $url_new === '' ? '' : BK_create_place_url($place_id); }
		unset($url_new, $place_id, $hasPlaceID);

	/* display settings
		** $new_instance['display'] is an array auto-parsed by PHP from the 'display[]' form elements.
		** this code uniquifies, sorts, and lowercases it before saving it.
		*/
		$display = array_unique($new_instance['display']);
		sort($display);
		$display = array_flip(array_change_key_case(array_flip($display), CASE_LOWER));
		$instance['display_settings'] = implode(',', $display);
		unset($display);

	/* filter settings
		** $new_instance['filter'] is an array auto-parsed by PHP from the 'filter[]' form elements.
		** this code uniquifies, sorts, and lowercases it before saving it.
		*/
		$filter = array_unique($new_instance['filter']);
		sort($filter);
		$filter = array_flip(array_change_key_case(array_flip($filter), CASE_LOWER));
		$instance['filter_settings'] = implode(',', $filter);
		unset($filter);

	/* count
		** how many items to show.
		** can be [0..20] - invalid inputs do not change anything.
		*/
		$count = $new_instance['count'];
		$count_i = (int)$count;
		if ($count == /* this must be an == and not ===. sorry. */ $count_i && $count_i >= 0 && $count_i <= 20) { $instance['count'] = $count_i; }
		unset($count, $count_i);

		return $instance;
	}

	/**
	* Creates the edit form for the widget (on the admin page)
	*
	*/
	function form($instance) {
		// Defaults
		$displays = array(
			'name'=> 'Place Name', 'address' => 'Address/Location',
			'crumbs'=> 'Location Hierarchy'/* , 'map'=> 'Google Map' */
		);
		$filters = array(
			'checkins'=> 'Checkins',
			'notes'=> 'Notes',
			'photos'=> 'Photos'
		);
		$defaults = array(
			'title'=>'Brightkite Place',
			'place_url'=> '',
			'display_settings'=> implode(',', array_keys($displays)),
			'filter_settings'=> implode(',', array_keys($filters)),
			'count'=> 5
		);
		$instance = wp_parse_args( (array)$instance, $defaults);

		$title = htmlspecialchars($instance['title']);

		$place_url = $instance['place_url'];
		$display_settings = explode(',', $instance['display_settings']);
		$filter_settings = explode(',', $instance['filter_settings']);
		$count = (int)$instance['count'];

		$input = ' style="width: 200px;"';
		# Output the options
?>
		<p style="text-align: right;">
			<label for="<?php echo $this->get_field_name('title') ?>">
				<?php echo __('Widget Title') ?>:
				<input<?php echo $input ?> id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" type="text" value="<?php echo $title ?>" />
			</label>
		</p>
		<p style="text-align: right;">
			<label for="<?php echo $this->get_field_id('place_url') ?>_place">
				<?php echo __('Brightkite Place Page URL') ?>:
				<input type="text" id="<?php echo $this->get_field_id('place_url') ?>" name="<?php echo $this->get_field_name('place_url') ?>" value="<?php echo $place_url ?>"<?php echo $input ?> />
			</label>
		</p>
		<h4 class="brightkite_place_displays">
			<?php echo __('Show these things about the place') ?>:
		</h4>
		<ul class="brightkite_place_displays">
<?php
		foreach ($displays as $id=> $label) {
			$selectThis = in_array($id, $display_settings);
?>
			<li>
				<label for="<?php echo $this->get_field_id('display_'.$id) ?>">
					<input type="checkbox" id="<?php echo $this->get_field_id('display_'.$id) ?>" name="<?php echo $this->get_field_name('display') ?>[]" value="<?php echo $id ?>" <?php if ($selectThis) { ?>checked="checked" <?php } ?>/>
					<?php echo __($label) ?>
				</label>
			</li>
<?php
		}
?>
		</ul>

		<h4 class="brightkite_place_displays">
			<?php echo __('Show these types of posts:') ?>
		</h4>
		<ul class="brightkite_place_displays">
<?php
		foreach ($filters as $id => $label) {
			$selectThis = in_array($id, $filter_settings);
?>
			<li>
				<label>
					<input type="checkbox" id="<?php echo $this->get_field_id('filter_'.$id) ?>" name="<?php echo $this->get_field_name('filter') ?>[]" value="<?php echo $id ?>" <?php if ($selectThis) { ?>checked="checked" <?php } ?>/>
					<?php echo __($label) ?>
				</label>
			</li>
<?php
		}
?>
		</ul>

		<p style="text-align: right;">
			<label for="<?php echo $this->get_field_id('count') ?>">
				<?php echo __('Show this many posts:') ?>
				<input type="text" id="<?php echo $this->get_field_id('count') ?>" name="<?php echo $this->get_field_name('count') ?>" value="<?php echo $count ?>" maxwidth="3" style="text-align: right;" />
			</label>
			<br />
			<span>(Any number from 0 to 20)</span>
		</p>
<?php

	}

} // END class

	function enqueue_bk_place_scripts() {
		
	}
	function enqueue_bk_place_styles() {
		if (!is_admin()) {
			wp_enqueue_style('bk_emoji', WP_PLUGIN_URL.'/brightkite/emoji/emoji.css');
			wp_enqueue_style('bk_css', WP_PLUGIN_URL.'/brightkite/brightkite.css');
		} else {
			wp_enqueue_style('bk_admin_css', WP_PLUGIN_URL.'/brightkite/brightkite_admin.css');
		}
	}
	/**
	* Register Brighkite Place widget.
	*
	* Calls 'widgets_init' action after the Brighkite Place widget has been registered.
	*/
	function BrightkitePlaceInit() {
		register_widget('BrightkitePlaceWidget');
	}
	add_action('widgets_init', 'BrightkitePlaceInit');
	add_action('wp_print_scripts', 'enqueue_bk_place_scripts');
	add_action('wp_print_styles', 'enqueue_bk_place_styles');
