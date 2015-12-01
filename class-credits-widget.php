<?php

class BetterImageCreditsWidget extends WP_Widget {

	public function __construct() {
		parent::__construct('better-image-credits-widget', __('Image Credits', 'better-image-credits'),
				array('description' => __('A widget displaying the image credits', 'better-image-credits')));
	}

	public function widget($args, $instance) {
		extract($args);
		$instance = wp_parse_args((array) $instance, array(
				'title' => '',
				'template' => IMAGE_CREDITS_TEMPLATE,
				'separator' => IMAGE_CREDITS_SEP,
				'before' => IMAGE_CREDITS_BEFORE,
				'after' => IMAGE_CREDITS_AFTER
			));
		extract($instance, EXTR_PREFIX_ALL | EXTR_OVERWRITE, 'credits');

		if (have_posts()) {
			$plugin = BetterImageCreditsPlugin::get_instance();
			$credits = array();

			rewind_posts();
			while (have_posts()) {
				the_post();
				$credits = array_merge($credits, $plugin->get_image_credits($credits_template));
			}

			// Filters empty strings
			$credits = array_filter($credits);

			if (!empty($credits)) {
				$title = apply_filters('widget_title', empty($credits_title) ? '' : $credits_title, $instance, $this->id_base);

				echo $before_widget;
				if ($title) echo $before_title . $title . $after_title;

				echo $plugin->format_credits($credits, $credits_separator, $credits_before, $credits_after);
				echo $after_widget;
			}
		}
	}

	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array(
				'title' => '',
				'template' => IMAGE_CREDITS_TEMPLATE,
				'separator' => IMAGE_CREDITS_SEP,
				'before' => IMAGE_CREDITS_BEFORE,
				'after' => IMAGE_CREDITS_AFTER
			));
		$title = esc_attr($instance['title']);
		$template = esc_attr($instance['template']);
		$separator = esc_attr($instance['separator']);
		$before = esc_attr($instance['before']);
		$after = esc_attr($instance['after']);
?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Template', 'better-image-credits'); ?>:</label>
	<input type="text" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" class="widefat code" value="<?php echo $template; ?>" /><br/>
	<em><?php _e('HTML to output each individual credit line. Use [title], [source], [link], [license] or [license_link] as placeholders.', 'better-image-credits'); ?></em>
</p>

<p>
	<label for="<?php echo $this->get_field_id('separator'); ?>"><?php _e('Separator', 'better-image-credits'); ?>:</label>
	<input type="text" id="<?php echo $this->get_field_id('separator'); ?>" name="<?php echo $this->get_field_name('separator'); ?>" class="widefat code" value="<?php echo $separator; ?>" /><br/>
	<em><?php _e('HTML to separate the credits (enter leading and trailing spaces using HTML entities).', 'better-image-credits'); ?></em>
</p>

<p>
	<label for="<?php echo $this->get_field_id('before'); ?>"><?php _e('Before', 'better-image-credits'); ?>:</label>
	<input type="text" id="<?php echo $this->get_field_id('before'); ?>" name="<?php echo $this->get_field_name('before'); ?>" class="widefat code" value="<?php echo $before; ?>" /><br/>
	<em><?php _e('HTML to output before the credits (enter leading and trailing spaces using HTML entities).', 'better-image-credits'); ?></em>
</p>

<p>
	<label for="<?php echo $this->get_field_id('after'); ?>"><?php _e('After', 'better-image-credits'); ?>:</label>
	<input type="text" id="<?php echo $this->get_field_id('after'); ?>" name="<?php echo $this->get_field_name('after'); ?>" class="widefat code" value="<?php echo $after; ?>" /><br/>
	<em><?php _e('HTML to output after the credits (enter leading and trailing spaces using HTML entities).', 'better-image-credits'); ?></em>
</p>
<?php
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['template'] = $new_instance['template'];
		$instance['separator'] = $new_instance['separator'];
		$instance['before'] = $new_instance['before'];
		$instance['after'] = $new_instance['after'];
		return $instance;
	}

}
