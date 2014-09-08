jQuery(document).ready(function($) {
	$('.credits-overlay').each(function() {
		var $overlay = $(this).detach();
		$targets = 'img' + $overlay.data('target');

		$($targets).each(function() {
			$target = $(this);

			$container = $('<div class="credits-container"></div>');
			$container.addClass($target.attr('class'));

			$the_overlay = $overlay.clone().appendTo($container);
			$the_overlay.css({
				width: $target.css('width'),
				marginRight: $target.css('margin-right'),
				marginLeft: $target.css('margin-left'),
				marginBottom: $target.css('margin-bottom'),
				borderBottomLeftRadius: $target.css('border-bottom-left-radius'),
				borderBottomRightRadius: $target.css('border-bottom-right-radius') });

			$parent = $target.parent();

			if ($parent.is('a')) {
				$parent.clone().prependTo($container);
				$parent.replaceWith($container);
			} else {
				$target.clone().prependTo($container);
				$target.replaceWith($container);
			}
		});

		$overlay.remove();
	});
});