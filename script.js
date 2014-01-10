jQuery(document).ready(function($) {
	$('.credits-overlay').each(function() {
		var $overlay = $(this).detach();

		$($overlay.data('target')).each(function() {
			$target = $(this);

			$the_overlay = $overlay.clone().appendTo('body');
			$the_overlay.outerWidth($target.innerWidth());
			$pos = $target.offset();
			$the_overlay.offset({ left: $pos.left, top: $pos.top + $target.innerHeight() - $the_overlay.outerHeight() });
			$the_overlay.css({ borderBottomLeftRadius: $target.css('border-bottom-left-radius'),
				borderBottomRightRadius: $target.css('border-bottom-right-radius') });
			$the_overlay.show();
		});

		$overlay.remove();
	});
});