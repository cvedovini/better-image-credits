=== Better Image Credits ===
Author: Claude Vedovini
Contributors: cvedovini
Donate link: http://vedovini.net/plugins/?utm_source=wordpress&utm_medium=plugin&utm_campaign=better-image-credits
Tags: image, media, credits
Requires at least: 3.0
Tested up to: 4.3
Stable tag: 1.5.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Description ==

This plugin adds credits, link and license fields to media uploads and offer several
options to display image credits on your posts and pages. You can either:

- Use the [image-credit] shortcode (optional attributes are `sep`, `before`
and `after`).
- Use the `the_image_credits()` template tag (optional parameters are `sep`,
 `before` and `after`).
- Let the plugin automatically display the credits for you, before or after the
content or overlaid above each image.
- Use the widget to display the credits in the footer or the sidebars.

See the settings page to choose how you display the credits, including the HTML
used to build them.

This plugin is a fork of the [Image Credits plugin](http://wordpress.org/plugins/image-credits/)
by [Adam Capriola](http://profiles.wordpress.org/adamcapriola/). It is 100%
compatible, if you are already using the Image Credit plugin just replace it
with this one and it will work about the same way, except you will have more control.


== Installation ==

This plugin follows the [standard WordPress installation
method](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins):

1. Upload the `better-image-credits` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the plugin's settings page to configure the plugin.


== Changelog ==

= version 1.5.3 =
- Changes for WordPress 4.3

= Version 1.5.2 =
- More fix related to the admin hook issue.

= Version 1.5.1 =
- Fixed wrong hook used to initialized the admin part of the plugin causing the
cusom fields not to show up or not to save when adding an image from the post editor.

= Version 1.5 =
- Added support for a link to the license.

= Version 1.4 =
- Added support for custom header and background images.
- Added a widget to display the credits in footer or sidebars.

= Version 1.3 =
- Improved the support for the overlay display option
- Added templating for the individual credits
- Added a license field
- Simplified and moved around some code
- Added support for image galleries

= Version 1.2 =
- Added Italian and Serbian translations

= Version 1.1 =
- Added French translations
- Added Dutch translations
- Removed overlay script dependency on `jquery.tools`, replaced with `jquery`

= Version 1.0 =
- Initial release


== Credits ==

Following is the list of people and projects who helped me with this plugin,
many thanks to them :)

- [Jan Spoelstra](http://www.linkedin.com/in/janspoelstra): Contributed the
Dutch translations.
- Borisa Djuraskovic from [Web Hosting Hub](http://www.webhostinghub.com/): Contributed
the Serbian translations.
- [Luca Palli](http://lpal.li/): Contributed the Italian translations.
- [joerns](https://wordpress.org/support/profile/joerns): Contributed the code
to support the galleries.

