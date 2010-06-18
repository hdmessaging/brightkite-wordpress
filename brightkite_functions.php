<?php
/* PHP functions for Brightkite Wordpress plugins */

require_once('emoji/emoji.php');

	/* constants from http://striderweb.com/nerdaphernalia/2008/09/hit-a-moving-target-in-your-wordpress-plugin/ */
	if (!defined('WP_CONTENT_URL')) { define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content' ); }
	if (!defined('WP_CONTENT_DIR')) { define('WP_CONTENT_DIR', ABSPATH . 'wp-content'); }
	if (!defined('WP_PLUGIN_URL')) { define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins'); }
	if (!defined('WP_PLUGIN_DIR')) { define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins'); }
	if (!defined('BK_HOST')) { define('BK_HOST', 'http://brightkite.com'); }

	function BK_json_decode($json) {
		if (!function_exists('json_decode')) {
			require_once('SERVICES_JSON.php');
			$jsonO = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$data = $jsonO->decode($json);
			unset($jsonO);
		} else {
			$data = json_decode($json, true);
		}
		return $data;
	}

	function BK_emoji_decode($text) {
		return emoji_unified_to_html(emoji_softbank_to_unified($text));
	}

	function BK_linkify(&$text) {
		$reg_link = '/\b((?:(?:https?):\/\/|www\.)[-A-Z0-9+&@#\/%=~_|$?!:;,.]*[A-Z0-9+&@#\/%=~_|$])/i';
		$words = explode(' ', $text);
		foreach ($words as &$word) {
			$matches = array();
			if (preg_match_all($reg_link, $word, $matches) > 0) {
				$word = preg_replace_callback($reg_link,
					create_function('$matches', 'return \'<a href="\'.BK_ensureHTTP($matches[0]).\'" rel="external">\'.BK_ellipsize($matches[0]).\'</a>\';'),
					$word);
			} else if (preg_match_all('/#[A-Za-z0-9_@-]+/', $word, $matches) > 0) {
				$word = preg_replace('/#([A-Za-z0-9_-]+)/', '<a rel="external" href="'.BK_HOST.'/objects/search?q=$1">#$1</a>', $word);
			} else if (preg_match_all('/@[A-Za-z0-9_-]+/', $word, $matches) > 0) {
				$word = preg_replace('/@([A-Za-z0-9_-]+)/', '<a rel="external" href="'.BK_HOST.'/people/$1">@$1</a>', $word);
			}
		}
		return implode(' ', $words);
	}

	function BK_ensureHTTP($url) {
		if (preg_match('/^(https?:\/\/)/', $string) < 0) { $url = 'http://'.$url; }
		return $url;
	}

	function BK_ellipsize($string) {
		if (preg_match('/^(https?:\/\/)/', $string) > 0) { $string = preg_replace('/^(https?:\/\/)/', '', $string); }
		return strlen($string) > 20 ? substr($string, 0, 8).'&hellip;'.substr($string, -8) : $string;
	}

	function BK_extract_place_id(&$url) {
		$matches = array();
		preg_match('/^https?:\/\/([^\.\/]+\.)?brightkite.com\/places\/([^\/\?\#]+)\/?/', $url, $matches);
		$id = is_array($matches) && count($matches) > 2 && strlen($matches[2]) > 0 ? $matches[2] : null;
		unset($matches);
		return $id;
	}
	function BK_create_place_url($place_id) {
		return BK_HOST.'/places/'.$place_id;
	}
	function BK_create_object_url($object_id) {
		return BK_HOST.'/objects/'.$object_id;
	}

	function BK_construct_hierarchy(array &$hierarchy) {
		$html = array();
		for ($i = count($hierarchy); $i > 0; --$i) {
			$place = $hierarchy[$i - 1];
			if (is_array($place) && array_key_exists('id', $place) && array_key_exists('name', $place)) {
				$html[] = '<a href="'.BK_create_place_url($place['id']).'">'.$place['name'].'</a>';
			}
		}
		return implode(' &raquo; ', $html);
	}
