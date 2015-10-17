<?php

class BetterImageCreditsWidget extends WP_Widget {

	public function __construct() {
		parent::__construct('better-image-credits-widget', __('Image Credits', 'better-image-credits'),
				array('description' => __('A widget displaying the image credits', 'better-image-credits')));
	}

	public function widget($args, $instance) {
		extract($args);
		$instance = wp_parse_args((array) $instance, array(
				'title' => ''
			));

		if (have_posts()) {
			rewind_posts();
			while (have_posts()) {
				the_post();
				$credits[] = get_the_image_credits($sep=IMAGE_CREDITS_SEP, $before='', $after='');
			}

			$credits = implode('', $credits);

			if (!empty($credits)) {
				$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

				echo $before_widget;
				if ($title) echo $before_title . $title . $after_title;

				echo IMAGE_CREDITS_BEFORE;
				echo $credits;
				echo IMAGE_CREDITS_AFTER;
				echo $after_widget;
			}
		}
	}

	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array(
				'title' => ''
			));
		$title = esc_attr($instance['title']);

?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<?php
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}
