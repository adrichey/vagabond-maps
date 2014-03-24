Title: Vagabond Maps  
Author: [Alan Richey][1]  
Version: 1.0.0  
Website: https://github.com/adrichey/vagabond-maps  
License: [GPL v3][3]

# Vagabond Maps

Vagabond Maps is a simple map system for WordPress.  It is a WordPress plugin used to attach Google Maps location information to posts for display on embedded maps.  It uses the [Google Maps API v3][4] and [jQuery][5], with a dash of [Dashicons][6] thrown into the mix.  A dash of Dashicons.  Wow.

## Compatibility

This plugin requires WordPress version 3.9 or higher.  It has been tested for compatibility with versions up to 3.9-beta2.  It was last updated on 2014-03-24.

## Installation and Setup

1. [Download][2] the plugin from GitHub and install the plugin files into the /wp-content/plugins/ directory of your WordPress installation in a new directory called "vagabond-maps".  The directory containing the plugin files must be named "vagabond-maps" or it will break.  The directory structure should look like the following:  

  * wordpress/wp-content/plugins/vagabond-maps/  
    * admin/  
        * admin.css  
        * admin.js  
        * admin.php  
    * LICENSE.txt  
    * post_editor/  
        * post_editor.css  
        * post_editor.js  
        * tinymce_plugins.js  
    * README.md  
    * vagabond-maps.php  
    * views/  
        * full_map.php  
        * single_map.php

2. Activate the plugin in the WordPress dashboard.
3. Navigate to Settings -> Vagabond Maps in the left-hand dashboard sidebar.
4. Select the post types that you would like to use to store and display location data.
5. Search for a starting location and select the zoom level for the full map.

## General Use

### Adding Location Information To A Post

Once you have enabled the plugin and set it up for use on the settings page, you can simply create or edit a post for an enabled post type and you will see a meta box labeled Vagabond Maps - Location Data.  Here you can search for a location and attach that information to the post along with a preview.  Simply update the post once you have entered a location and the plugin with save that data.

### Embedding A Map On A Post

This plugin uses shortcodes to handle map embeds.  It adds two buttons to the end of the content editor for enabled posts.  The embed button to add a map for the current post appears like a map pin icon, while the button to embed the full map of all locations appears like a folded map with a pin on top.  When you want to display a map, simply click one of these buttons and a dialog box will appear where you can input height and width information and embed the map.  The dialogs are labeled "Vagabond Maps - Embed Post Map" and "Vagabond Maps - Embed Full Map" respectively.  Alternatively, you can simply use the shortcodes in the section below within the content editor to display a map.

### Shortcodes For Developers

The following shortcodes can be used to display maps on posts.  They are useful when building WordPress themes as they are a little more flexible.  For example, you can display a full location map for specific enabled post types as opposed to all locations for enabled post types.  For more information on shortcodes, please visit the WordPress.org documentation at https://codex.wordpress.org/Shortcode.

You can use the following shortcodes in your themes by calling the following function, and replacing the shortcode with yours from the options below:

<?php echo do_shortcode('[vmaps_spm id="34" h="400" h_in="px" w="100" w_in="%"]'); ?>

#### Single Post Map Shortcode

[vmaps_spm id="34" h="400" h_in="px" w="100" w_in="%"]

Attributes:

* id: The post ID for the map you want to display
* h: The height of iframe embed
* h_in: "px" to render the height in pixels, "%" to render the height in a percentage
* w: The width of iframe embed
* w_in: "px" to render the width in pixels, "%" to render the width in a percentage

#### Full Location Map Shortcode

[vmaps_fm pt="page" h="600" h_in="px" w="100" w_in="%"]

Attributes:

* pt: The post type you wish to display all locations for.  This attribute can be omitted to display a map with all location posts.
* h: The height of iframe embed
* h_in: "px" to render the height in pixels, "%" to render the height in a percentage
* w: The width of iframe embed
* w_in: "px" to render the width in pixels, "%" to render the width in a percentage

## Change Log

1.0.0

* Initial Release

## License

Vagabond Maps: A simple map system for WordPress.  Attach Google Maps location data to a post type and embed various maps on them.

Copyright (C) 2014 Alan Richey

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.

## Author

This plugin was written by [Alan Richey][1].  He is a fan of trappist and abbey beers and if you feel inclined to buy him one, [you can send him pity money here][7].

 [1]: http://alanrichey.net
 [2]: https://github.com/adrichey/vagabond-maps/archive/master.zip
 [3]: http://www.gnu.org/licenses/gpl-3.0.html
 [4]: https://developers.google.com/maps/documentation/javascript/
 [5]: http://jquery.com/
 [6]: https://github.com/melchoyce/dashicons
 [7]: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=FVZSZ32KTTBD6&lc=US&item_name=Vagabond%20Maps&item_number=alanrichey%2enet%2dvagabond%2dmaps&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest