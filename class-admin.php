<?php

class BetterImageCreditsAdmin {

	function __construct($plugin) {
		$this->plugin = $plugin;
		add_filter('plugin_action_links_better-image-credits/better-image-credits.php', array(&$this, 'add_settings_link'));
		add_filter('attachment_fields_to_edit', array($this, 'add_fields' ), 10, 2);
		add_filter('attachment_fields_to_save', array($this, 'save_fields' ), 10 , 2);

		add_filter('manage_media_columns', array(&$this, 'manage_media_columns'));
		add_action('manage_media_custom_column', array(&$this, 'manage_media_custom_column'), 10, 2);
		add_action('admin_menu', array(&$this, 'admin_menu'));

		add_action('admin_footer-upload.php', array(&$this, 'add_bulk_actions'));
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		add_action('admin_action_bulk_credits', array(&$this, 'bulk_credits'));
		add_action('admin_action_-1', array(&$this, 'bulk_credits')); // Bottom dropdown (assumes top dropdown = default value)
	}

	function add_fields($form_fields, $post) {
		$mime = get_post_mime_type($post->ID);

		if (preg_match('|image/.+|', $mime)) {
			$form_fields['credits_source'] = $this->get_field($post,
					'credits_source', '_wp_attachment_source_name',
					__('Credits', 'better-image-credits'),
					__('Source name', 'better-image-credits'));

			$form_fields['credits_link'] = $this->get_field($post,
					'credits_link', '_wp_attachment_source_url',
					__('Link', 'better-image-credits'),
					__('Source URL', 'better-image-credits'));

			$form_fields['license'] = $this->get_field($post,
					'license', '_wp_attachment_license',
					__('License', 'better-image-credits'),
					__('License type', 'better-image-credits'));

			$form_fields['license_link'] = $this->get_field($post,
					'license_link', '_wp_attachment_license_url',
					__('License link', 'better-image-credits'),
					__('License URL', 'better-image-credits'));
		}

		return $form_fields;
	}

	function get_field($post, $fid, $value, $label, $helps) {
		$value = get_post_meta($post->ID, $value, true);
		return array(
				'label' => $label,
				'input' => 'html',
				'html'  => "<input type='text' class='text' placeholder='$helps' name='attachments[{$post->ID}][$fid]' value='$value'>"
			);
	}

	function save_fields($post, $attachment) {
		if (isset($attachment['credits_source'])) {
			update_post_meta($post['ID'], '_wp_attachment_source_name',
					esc_attr($attachment['credits_source']));
		}

		if (isset($attachment['credits_link'])) {
			update_post_meta($post['ID'], '_wp_attachment_source_url',
					esc_url($attachment['credits_link']));
		}

		if (isset($attachment['license'])) {
			update_post_meta($post['ID'], '_wp_attachment_license',
					esc_attr($attachment['license']));
		}

		if (isset($attachment['license_link'])) {
			update_post_meta($post['ID'], '_wp_attachment_license_url',
					esc_url($attachment['license_link']));
		}

		return $post;

	}

	function admin_menu() {
		add_submenu_page('options-general.php', __('Image Credits Options', 'better-image-credits'), __('Image Credits', 'better-image-credits'), 'manage_options', 'image-credits', array(&$this, 'options_page'));
		add_settings_section('default', '', '', 'image-credits');
		$this->add_settings_field('better-image-credits_display', __('Display Credits', 'better-image-credits'), 'add_settings_field_display');
		$this->add_settings_field('better-image-credits_template', __('Template', 'better-image-credits'), 'add_settings_field_template');
		$this->add_settings_field('better-image-credits_sep', __('Separator', 'better-image-credits'), 'add_settings_field_sep');
		$this->add_settings_field('better-image-credits_before', __('Before', 'better-image-credits'), 'add_settings_field_before');
		$this->add_settings_field('better-image-credits_after', __('After', 'better-image-credits'), 'add_settings_field_after');
	}

	function add_settings_field($id, $title, $callback) {
		register_setting('image-credits', $id);
		add_settings_field($id, $title, array(&$this, $callback), 'image-credits');
	}

	function add_settings_link($links) {
		$url = admin_url('options-general.php?page=image-credits');
		$links[] = '<a href="' . $url . '">' . __('Settings') . '</a>';
		return $links;
	}

	function add_settings_field_display() { ?>
		<p><label><input type="checkbox" name="better-image-credits_display[]" value="<?php echo IMAGE_CREDIT_BEFORE_CONTENT;?>"
			<?php checked($this->plugin->display_option(IMAGE_CREDIT_BEFORE_CONTENT)); ?>><?php
			_e('Before the content', 'better-image-credits'); ?></label></p>
		<p><label><input type="checkbox" name="better-image-credits_display[]" value="<?php echo IMAGE_CREDIT_AFTER_CONTENT;?>"
			<?php checked($this->plugin->display_option(IMAGE_CREDIT_AFTER_CONTENT)); ?>><?php
			_e('After the content', 'better-image-credits'); ?></label></p>
		<p><label><input type="checkbox" name="better-image-credits_display[]" value="<?php echo IMAGE_CREDIT_OVERLAY;?>"
			<?php checked($this->plugin->display_option(IMAGE_CREDIT_OVERLAY)); ?>><?php
			_e('Overlay on images (results may vary depending on your theme)', 'better-image-credits'); ?></label></p>
		<p><label><input type="checkbox" name="better-image-credits_display[]" value="<?php echo IMAGE_CREDIT_INCLUDE_HEADER;?>"
			<?php checked($this->plugin->display_option(IMAGE_CREDIT_INCLUDE_HEADER)); ?>><?php
			_e('Include credits for header image (support for header image depends on you theme).', 'better-image-credits'); ?></label></p>
		<p><label><input type="checkbox" name="better-image-credits_display[]" value="<?php echo IMAGE_CREDIT_INCLUDE_BACKGROUND;?>"
			<?php checked($this->plugin->display_option(IMAGE_CREDIT_INCLUDE_BACKGROUND)); ?>><?php
			_e('Include credits for background image (support for background image depends on you theme).', 'better-image-credits'); ?></label></p>
		<p><em><?php _e('Choose how you want to display the image credits', 'better-image-credits'); ?></em></p>
		<?php }

	function add_settings_field_template() { ?>
		<p><input type="text" name="better-image-credits_template" id="better-image-credits_template" class="large-text code"
			value="<?php echo htmlspecialchars(IMAGE_CREDITS_TEMPLATE); ?>" /></p>
		<p><em><?php _e('HTML to output each individual credit line. Use [title], [source], [link], [license] or [license_link] as placeholders.', 'better-image-credits'); ?></em></p><?php
	}

	function add_settings_field_sep() { ?>
		<p><input type="text" name="better-image-credits_sep" id="better-image-credits_sep" class="large-text code"
			value="<?php echo htmlspecialchars(IMAGE_CREDITS_SEP); ?>" /></p>
		<p><em><?php _e('HTML to separate the credits (enter leading and trailing spaces using HTML entities).', 'better-image-credits'); ?></em></p><?php
	}

	function add_settings_field_before() { ?>
		<p><input type="text" name="better-image-credits_before" id="better-image-credits_before" class="large-text code"
			value="<?php echo htmlspecialchars(IMAGE_CREDITS_BEFORE); ?>" /></p>
		<p><em><?php _e('HTML to output before the credits (enter leading and trailing spaces using HTML entities).', 'better-image-credits'); ?></em></p><?php
	}

	function add_settings_field_after() { ?>
		<p><input type="text" name="better-image-credits_after" id="better-image-credits_after" class="large-text code"
			value="<?php echo htmlspecialchars(IMAGE_CREDITS_AFTER); ?>" /></p>
		<p><em><?php _e('HTML to output after the credits (enter leading and trailing spaces using HTML entities).', 'better-image-credits'); ?></em></p><?php
	}

	function options_page() { ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h1><?php _e('Image Credits Options', 'better-image-credits'); ?></h1>
	<div id="main-container" class="postbox-container metabox-holder" style="width:75%;"><div style="margin:0 8px;">
		<div class="postbox">
			<h3 style="cursor:default;"><span><?php _e('Options', 'better-image-credits'); ?></span></h3>
			<div class="inside">
				<form method="POST" action="options.php"><?php
				settings_fields('image-credits');
				do_settings_sections('image-credits');
				submit_button();
				?></form>
			</div> <!-- .inside -->
		</div> <!-- .postbox -->
	</div></div> <!-- #main-container -->

	<div id="side-container" class="postbox-container metabox-holder" style="width:24%;"><div style="margin:0 8px;">
		<div class="postbox">
			<h3 style="cursor:default;"><span><?php _e('Do you like this Plugin?', 'better-image-credits'); ?></span></h3>
			<div class="inside">
				<p><?php _e('Please consider a donation.', 'better-image-credits'); ?></p>
				<div style="text-align:center">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
					">
					<input type="image" src="https://www.paypalobjects.com/en_US/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
				<p><?php _e('We also need volunteers to translate that plugin into more languages.', 'better-image-credits'); ?>
					<?php _e('If you wish to help then contact <a href="https://twitter.com/cvedovini">@cvedovini</a> on Twitter or use that <a href="http://vdvn.me/contact/">contact form</a>.', 'better-image-credits'); ?></p>
			</div> <!-- .inside -->
		</div> <!-- .postbox -->
		<div>
			<a class="twitter-timeline" href="https://twitter.com/cvedovini" data-widget-id="377037845489139712">Tweets by @cvedovini</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div>
	</div></div> <!-- #side-container -->

</div><?php
	}

	function manage_media_columns($defaults) {
		$defaults['credits'] = __('Credits', 'better-image-credits');
		$defaults['license'] = __('License', 'better-image-credits');
		return $defaults;
	}

	function manage_media_custom_column($column, $post_id) {
		if ($column == 'credits') {
			$credit_source = esc_attr(get_post_meta($post_id, '_wp_attachment_source_name', true));
			$credit_link = esc_url(get_post_meta($post_id, '_wp_attachment_source_url', true));

			if (!empty($credit_source)) {
				if (empty($credit_link)) {
					echo $credit_source;
				} else {
					echo '<a href="' . $credit_link . '">' . $credit_source . '</a>';
				}
			}
		}

		if ($column == 'license') {
			$license = esc_attr(get_post_meta($post_id, '_wp_attachment_license', true));
			$license_link = esc_url(get_post_meta($post_id, '_wp_attachment_license_url', true));

			if (!empty($license)) {
				if (empty($license_link)) {
					echo $license;
				} else {
					echo '<a href="' . $license_link . '">' . $license . '</a>';
				}
			}
		}
	}

	function enqueue_scripts($hook) {
		if ('upload.php' !== $hook) return;

		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('wp-jquery-ui-dialog');
	}

	function add_bulk_actions() { ?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('select[name^="action"] option:last-child').before('<option value="bulk_credits"><?php echo esc_attr(__( 'Image Credits', 'better-image-credits')); ?></option>');
				$('#doaction,#doaction2').click(function() {
					if ($('select[name="action"]').val() == 'bulk_credits' ||
							$('select[name="action2"]').val() == 'bulk_credits') {
						$('#dialog-credits').dialog({
							resizable: false,
						    modal: true,
						    buttons: {
						      	'<?php _e('OK', 'better-image-credits'); ?>': function() {
						        	$(this).dialog('close');
						        	$('#dialog-credits input').appendTo('#posts-filter');
						      		$('#posts-filter').submit();
						        },
						        '<?php _e('Cancel'); ?>': function() {
						        	$(this).dialog('close');
						        }
						    }
						 });
						 return false;
					}
				});
			});
		</script>
		<div id="dialog-credits" title="<?php _e('Image Credits', 'better-image-credits'); ?>" style="display:none">
			<p><?php _e('Leave the fields blank to remove credits information.', 'better-image-credit'); ?></p>
  			<p>
  				<label for="credits_source"><?php _e('Credits', 'better-image-credits'); ?>:</label><br>
  				<input type="text" class="text widefat" placeholder="<?php _e('Source name', 'better-image-credits'); ?>" name="credits_source" value="">
  			</p>
  			<p>
  				<label for="credits_link"><?php _e('Link', 'better-image-credits'); ?>:</label><br>
  				<input type="text" class="text widefat" placeholder="<?php _e('Source URL', 'better-image-credits'); ?>" name="credits_link" value="">
  			</p>
  			<p>
  				<label for="license"><?php _e('License', 'better-image-credits'); ?>:</label><br>
  				<input type="text" class="text widefat" placeholder="<?php _e('License type', 'better-image-credits'); ?>" name="license" value="">
  			</p>
  			<p>
  				<label for="license_link"><?php _e('License link', 'better-image-credits'); ?>:</label><br>
  				<input type="text" class="text widefat" placeholder="<?php _e('License URL', 'better-image-credits'); ?>" name="license_link" value="">
  			</p>
  		</div>
		<?php
	}

	function bulk_credits() {
		if (empty($_REQUEST['action']) || ('bulk_credits' != $_REQUEST['action'] &&
				'bulk_credits' != $_REQUEST['action2']))
			return;

		if (empty($_REQUEST['media']) || !is_array($_REQUEST['media']))
			return;

		check_admin_referer('bulk-media');
		$ids = array_map('intval', $_REQUEST['media']);

		foreach ($ids as $id) {
			$mime = get_post_mime_type($id);

			if (preg_match('|image/.+|', $mime)) {
				update_post_meta($id, '_wp_attachment_source_name', esc_attr($_REQUEST['credits_source']));
				update_post_meta($id, '_wp_attachment_source_url', esc_url($_REQUEST['credits_link']));
				update_post_meta($id, '_wp_attachment_license', esc_attr($_REQUEST['license']));
				update_post_meta($id, '_wp_attachment_license_url', esc_url($_REQUEST['license_link']));
			}
		}

		wp_redirect(admin_url('upload.php'));
	}

}
