<?php
/*
Plugin Name: Better Image Credits
Plugin URI: http://vedovini.net/plugins/?utm_source=wordpress&utm_medium=plugin&utm_campaign=better-image-credits
Description: Adds credits and link fields for media uploads along with a shortcode and various options to display image credits in your posts.
Version: 1.2
Author: Claude Vedovini
Author URI: http://vedovini.net/?utm_source=wordpress&utm_medium=plugin&utm_campaign=better-image-credits
License: GPLv3
Text Domain: better-image-credits

# The code in this plugin is free software; you can redistribute the code aspects of
# the plugin and/or modify the code under the terms of the GNU Lesser General
# Public License as published by the Free Software Foundation; either
# version 3 of the License, or (at your option) any later version.

# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#
# See the GNU lesser General Public License for more details.
*/


define('IMAGE_CREDITS_SEP', get_option('better-image-credits_sep', ',&#32;'));
define('IMAGE_CREDITS_BEFORE', get_option('better-image-credits_before', '<p class="image-credits">' . __('Image Credits', 'better-image-credits') . ':&#32;'));
define('IMAGE_CREDITS_AFTER', get_option('better-image-credits_after', '</p>'));

define('IMAGE_CREDIT_BEFORE_CONTENT', 'before');
define('IMAGE_CREDIT_AFTER_CONTENT', 'after');
define('IMAGE_CREDIT_OVERLAY', 'overlay');


class BetterImageCreditsPlugin {

	function __construct() {
		add_action('init', array($this, 'init'));
		add_action('admin_menu', array(&$this, 'admin_init'));
	}

	function init() {
		// Make plugin available for translation
		// Translations can be filed in the /languages/ directory
		add_filter('load_textdomain_mofile', array(&$this, 'smarter_load_textdomain'), 10, 2);
		load_plugin_textdomain('better-image-credits', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

		// Manage additional media fields
		add_filter('attachment_fields_to_edit', array($this, 'add_fields' ), 10, 2);
		add_filter('attachment_fields_to_save', array($this, 'save_fields' ), 10 , 2);

		if (!is_admin()) {
			// Shortcode
			add_shortcode('image-credits', array($this, 'credits_shortcode'));

			if ($this->display_option(IMAGE_CREDIT_BEFORE_CONTENT) ||
					$this->display_option(IMAGE_CREDIT_AFTER_CONTENT)) {
				add_filter('the_content', array($this, 'filter_content'), 0);
			}

			if ($this->display_option(IMAGE_CREDIT_OVERLAY)) {
				wp_register_style('better-image-credits', plugins_url('style.css', __FILE__), false, '1.0');
				wp_register_script('better-image-credits', plugins_url('script.js', __FILE__), array('jquery'), '1.0', true);
				add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
				add_filter('post_thumbnail_html', array($this, 'filter_post_thumbnail'), 10, 5);
				add_filter('the_content', array($this, 'filter_content_images'), 20);
				add_filter('wp_get_attachment_image_attributes', array($this, 'filter_attachment_image_attributes'), 10, 2);
			}
		}
	}

	function display_option($option) {
		$options = get_option('better-image-credits_display', array());
		if (!is_array($options)) $options = array($options);
		return in_array($option, $options);
	}

	function enqueue_scripts() {
		wp_enqueue_style('better-image-credits');
		wp_enqueue_script('better-image-credits');
	}

	function filter_post_thumbnail($html, $post_id, $post_thumbnail_id, $size, $attr) {
		return $html . $this->get_overlay($post_thumbnail_id, '.wp-image-' . $post_thumbnail_id);
	}

	function filter_content_images($content) {
		$attachment_ids = array();

		if (preg_match_all('/wp-image-(\d+)/i', $content, $matches)) {
			foreach ($matches[1] as $id) {
				if (!in_array($id, $attachment_ids)) {
					$attachment_ids[] = $id;
					$content .= $this->get_overlay($id, '.wp-image-' . $id);
				}
			}
		}

		return $content;
	}

	function filter_attachment_image_attributes($attr, $attachment) {
		$attr['class'] = $attr['class'] . ' wp-image-' . $attachment->ID;
		return $attr;
	}

	function get_overlay($post_thumbnail_id, $target) {
		$credit_source = esc_attr(get_post_meta($post_thumbnail_id, '_wp_attachment_source_name', true));
		$credit_link = esc_url(get_post_meta($post_thumbnail_id, '_wp_attachment_source_url', true));

		if (!empty($credit_source)) {
			if (empty($credit_link)) {
				$credits = $credit_source;
			} else {
				$credits = '<a href="' . $credit_link . '">' . $credit_source . '</a>';
			}

			# $credits = IMAGE_CREDITS_BEFORE . $credits . IMAGE_CREDITS_AFTER;
			return '<div class="credits-overlay" data-target="' . $target . '">' . $credits . '</div>';
		}

		return '';
	}

	function admin_init() {
		require_once 'class-admin.php';
		$this->admin = new BetterImageCreditsAdmin($this);
	}

	function smarter_load_textdomain($mofile, $domain) {
		if ($domain == 'better-image-credits' && !is_readable($mofile)) {
			extract(pathinfo($mofile));
			$pos = strrpos($filename, '_');

			if ($pos !== false) {
				# cut off the locale part, leaving the language part only
				$filename = substr($filename, 0, $pos);
				$mofile = $dirname . '/' . $filename . '.' . $extension;
			}
		}

		return $mofile;
	}

	function add_fields($form_fields, $post) {
		$form_fields['credits_source'] = array(
				'label' => __( 'Credits', 'better-image-credits' ),
				'input' => 'text',
				'value' => get_post_meta($post->ID, '_wp_attachment_source_name', true),
				'helps' => __( 'Source name of the image.', 'better-image-credits' )
		);

		$form_fields['credits_link'] = array(
				'label' => __( 'Link', 'better-image-credits' ),
				'input' => 'text',
				'value' => get_post_meta($post->ID, '_wp_attachment_source_url', true),
				'helps' => __( 'URL where the original image was found.', 'better-image-credits' )
		);

		return $form_fields;
	}

	function save_fields($post, $attachment) {
		if (isset($attachment['credits_source'])) {
			$credits_source = get_post_meta($post['ID'], '_wp_attachment_source_name', true);

			if ($credits_source != esc_attr($attachment['credits_source'])) {
				if (empty($attachment['credits_source'])) {
					delete_post_meta($post['ID'], '_wp_attachment_source_name');
				} else {
					update_post_meta($post['ID'], '_wp_attachment_source_name', esc_attr($attachment['credits_source']));
				}
			}
		}

		if (isset($attachment['credits_link'])) {
			$credits_link = get_post_meta($post['ID'], '_wp_attachment_source_url', true);

			if ($credits_link != esc_url( $attachment['credits_link'])) {
				if (empty($attachment['credits_link'])) {
					delete_post_meta($post['ID'], '_wp_attachment_source_url');
				} else {
					update_post_meta($post['ID'], '_wp_attachment_source_url', esc_url( $attachment['credits_link']));
				}
			}
		}

		return $post;

	}

	function get_image_credits() {
		global $post;
		$attachment_ids = array();
		$credits = array();

		// First check for post thumbnail and save its ID in an array
		if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
			$attachment_ids[] = get_post_thumbnail_id($post->ID);
		}

		// Next look in post content and check for instances of wp-image-[digits]
		if (preg_match_all('/wp-image-(\d+)/i', $post->post_content, $matches)) {
			foreach ($matches[1] as $id) {
				if (!in_array($id, $attachment_ids)) {
					$attachment_ids[] = $id;
				}
			}
		}

		// Go through all our attachments IDs and generate credits
		foreach ($attachment_ids as $id) {
			$credit_source = esc_attr(get_post_meta($id, '_wp_attachment_source_name', true));
			$credit_link = esc_url(get_post_meta($id, '_wp_attachment_source_url', true));

			if (!empty($credit_source)) {
				if (empty($credit_link)) {
					$credits[] = $credit_source;
				} else {
					$credits[] = '<a href="' . $credit_link . '">' . $credit_source . '</a>';
				}
			}
		}

		return array_unique($credits);
	}

	function credits_shortcode($atts) {
		extract(shortcode_atts(array(
				'sep' => IMAGE_CREDITS_SEP,
				'before' => IMAGE_CREDITS_BEFORE,
				'after'  => IMAGE_CREDITS_AFTER,
		), $atts, 'image-credits'));

		return $this->the_image_credits($sep, $before, $after);
	}

	function the_image_credits($sep=IMAGE_CREDITS_SEP, $before=IMAGE_CREDITS_BEFORE, $after=IMAGE_CREDITS_AFTER) {
		$credits = $this->get_image_credits();

		if (!empty($credits)) {
			$credits = implode($sep, $credits);
			return $before . $credits. $after;;
		}

		return '';
	}

	function filter_content($content) {
		$credits = $this->the_image_credits();

		if ($this->display_option(IMAGE_CREDIT_BEFORE_CONTENT)) {
			$content = $credits . $content;
		}

		if ($this->display_option(IMAGE_CREDIT_AFTER_CONTENT)) {
			$content = $content . $credits;
		}

	    return $content;
	}
}

global $the_better_image_credits_plugin;
$the_better_image_credits_plugin = new BetterImageCreditsPlugin();

/**
 * Legacy template tag for compatibility with the image-credits plugin
 */
function get_image_credits($sep=IMAGE_CREDITS_SEP, $before=IMAGE_CREDITS_BEFORE, $after=IMAGE_CREDITS_AFTER) {
	the_image_credits($sep, $before, $after);
}

function the_image_credits($sep=IMAGE_CREDITS_SEP, $before=IMAGE_CREDITS_BEFORE, $after=IMAGE_CREDITS_AFTER) {
	echo get_the_image_credits($sep, $before, $after);
}

function get_the_image_credits($sep=IMAGE_CREDITS_SEP, $before=IMAGE_CREDITS_BEFORE, $after=IMAGE_CREDITS_AFTER) {
	global $the_better_image_credits_plugin;
	return $the_better_image_credits_plugin->the_image_credits($sep, $before, $after);
}