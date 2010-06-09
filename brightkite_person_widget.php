<?php
/*
 * Plugin Name: Brightkite Person Widget
 * Version: 1.0
 * Plugin URI:
 * Description: Wordpress widget to display Brightkite activity for a person.
 * Author: Jordan Harband <jordan@brightkite.com>
 * Author URI: http://twitter.com/ljharb
 ** This plugin was started with the example at http://jessealtman.com/2009/06/08/tutorial-wordpress-28-widget-api/
 */
require_once('brightkite_functions.php');
class BrightkitePersonWidget extends WP_Widget {
	/**
	* Declares the BrightkitePersonWidget class.
	*
	*/
	function BrightkitePersonWidget() {
		$widget_ops = array(
			'classname' => 'widget_brightkite_person',
			'description' => __( "Wordpress widget to display Brightkite activity for a person.")
		);
		$control_ops = array('width' => 400, 'height' => 500);
		$this->WP_Widget('brightkite_person', __('Brightkite Person Widget'), $widget_ops, $control_ops);
	}

	/**
	* Displays the widget on the blog
	*
	*/
	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$login = empty($instance['login']) ? '' : $instance['login'];
		$display_settings = explode(',', $instance['display_settings']);
		$filter_settings = explode(',', $instance['filter_settings']);
		$count = (int)$instance['count'];

		if (strlen($login) > 0) {

			$user_url = BK_HOST.'/people/'.$login.'.json?dataset=profile';
			$stream_url = BK_HOST.'/objects.json?person_id='.$login.'&filter='.implode('%2C', $filter_settings).'&limit='.(int)$count;

			$json = wp_remote_fopen($user_url);
			$data = BK_json_decode($json);

			if (is_array($data) && array_key_exists('login', $data)) {
				/* Before the widget */
				echo $before_widget;

				/* the title */
				if ($title) { echo $before_title . $title . $after_title; }

				/* Make the widget */
	?>
				<div class="brightkite-person-data" id="<?php echo $this->get_field_id('brightkite-person') ?>">
					<div>
						<a href="<?php echo BK_HOST ?>/people/<?php echo $login ?>/">
							<span><?php echo $login . (in_array('name', $display_settings) ? ' ('.BK_emoji_decode($data['fullname']).')' : '')?></span>
	<?php	if (in_array('avatar', $display_settings)) {	?>
							<img src="<?php echo $data['small_avatar_url'] ?>" />
	<?php	}	?>
						</a>
					</div>
	<?php	if (in_array('desc', $display_settings)) {	?>
					<div><?php echo $data['description'] ?></div>
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
		if (array_key_exists('photo', $obj)) {
	?>
						<div class="clear"></div>
	<?php
		}
		if (array_key_exists('place', $obj) && array_key_exists('name', $obj['place'])) {
			if (array_key_exists('created_at_as_words', $obj)) {
				$ago = ' <a rel="external" href="'.BK_HOST.'/objects/'.$obj['id'].'" class="brightkite-time-link">'.$obj['created_at_as_words'].' ago</a>';
			} else { $ago = ''; }
			$place = '<a href="'.BK_create_place_url($obj['place']['id']).'">@ '.$obj['place']['name'].'</a>';
			if ($obj['object_type'] === 'checkin') { $place = 'checked in '.$place; }
	?>
						<div class="brightkite-location"><?php echo $place ?><?php echo $ago ?></div>
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

	/* BK username
		** Brightkite usernames must be between 3-15 characters,
		** and only letters, numbers, or underscores.
		** Only change the saved login if it's valid or empty.
		*/
		$login_new = $new_instance['login'];
		$len = strlen($login_new);
		$loginValid = $len >= 3 && $len <= 15 && preg_match('/([^A-Za-z0-9_])/', $login_new) === 0;
		if ($login_new === '' || $loginValid) { $instance['login'] = $login_new; }
		unset($login_new, $len, $loginValid);

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
			'login'=> 'Username', 'name'=> 'Full Name',
			'avatar'=> 'Avatar', 'desc'=> 'Description',
		);
		$filters = array(
			'checkins'=> 'Checkins',
			'notes'=> 'Notes',
			'photos'=> 'Photos'
		);
		$defaults = array(
			'title'=>'Brightkite User',
			'login'=> '',
			'display_settings'=> implode(',', array_keys($displays)),
			'filter_settings'=> implode(',', array_keys($filters)),
			'count'=> 5
		);
		$instance = wp_parse_args( (array)$instance, $defaults);

		$title = htmlspecialchars($instance['title']);

		$login = $instance['login'];
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
			<label for="<?php echo $this->get_field_id('login') ?>_person">
				<?php echo __('Brightkite Username') ?>:
				<input type="text" id="<?php echo $this->get_field_id('login') ?>" name="<?php echo $this->get_field_name('login') ?>" value="<?php echo $login ?>"<?php echo $input ?> />
			</label>
		</p>
		<h4 class="brightkite_person_displays">
			<?php echo __('Show these things about the user') ?>:
		</h4>
		<ul class="brightkite_person_displays">
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

		<h4 class="brightkite_person_displays">
			<?php echo __('Show these types of posts:') ?>
		</h4>
		<ul class="brightkite_person_displays">
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

	function enqueue_bk_person_scripts() {
		
	}
	function enqueue_bk_person_styles() {
		if (!is_admin()) {
			wp_enqueue_style('bk_emoji', WP_PLUGIN_URL.'/brightkite/emoji/emoji.css');
			wp_enqueue_style('bk_css', WP_PLUGIN_URL.'/brightkite/brightkite.css');
		} else {
			wp_enqueue_style('bk_admin_css', WP_PLUGIN_URL.'/brightkite/brightkite_admin.css');
		}
	}
	/**
	* Register Brighkite Person widget.
	*
	* Calls 'widgets_init' action after the Brighkite Person widget has been registered.
	*/
	function BrightkitePersonInit() {
		register_widget('BrightkitePersonWidget');
	}
	add_action('widgets_init', 'BrightkitePersonInit');
	add_action('wp_print_scripts', 'enqueue_bk_person_scripts');
	add_action('wp_print_styles', 'enqueue_bk_person_styles');
