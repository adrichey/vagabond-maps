<?php
/*
Plugin Name: Vagabond Maps
Plugin URI: https://github.com/adrichey/
Description: A simple map system for WordPress.  Attach Google Maps location data to a post type and embed various maps on them.
Author: Alan Richey
Version: 1.0.0
Author URI: http://alanrichey.net
*/

define( 'VAGABOND_MAPS_VERSION', '1.0.0' );

class vagabond_maps {
	
	protected $start_location_defaults = array(
		'complete_address' => 'Kansas City, MO USA',
		'lat' => '39.0997265',
		'lng' => '-94.57856670000001',
		'zoom' => '10'
	);
	
	protected $enabled_post_types_defaults = array();
	
	/*--------------------------------------------------------*/
	/* function __construct - Since 1.0.0
	/* Class constructor.  But you knew that, you little minx.
	/*--------------------------------------------------------*/
	function __construct() {
		$this->plugin_file_name = 'vagabond-maps';
		$this->plugin_name = str_replace('-','_',$this->plugin_file_name);
		$this->plugin_url = get_option('siteurl') . '/wp-content/plugins/' . $this->plugin_file_name;
		$this->plugin_dir = ABSPATH . 'wp-content/plugins/' . $this->plugin_file_name;
		
		// Admin settings page functionality
		add_action('admin_menu', array(&$this, 'add_admin_subpage'));
		add_action('admin_print_scripts-settings_page_' . $this->plugin_name, array(&$this,'admin_subpage_scripts'));
		add_action('admin_print_styles-settings_page_' . $this->plugin_name, array(&$this,'admin_subpage_styles'));
		
		// Post editor functionality
		add_action('add_meta_boxes', array(&$this, 'post_editor_meta_boxes'), 220, 2);
		add_action('admin_head-post-new.php', array(&$this, 'post_editor_scripts_and_styles'), 10, 0);
		add_action('admin_head-post.php', array(&$this, 'post_editor_scripts_and_styles'), 10, 0);
		add_action('save_post', array(&$this, 'post_editor_handler'), 220, 2 );
		add_action('init', array(&$this, 'add_post_editor_embed_buttons'));
		
		// Map embed functionality
		add_shortcode('vmaps_spm', array(&$this, 'shortcode_single_post_map'));
		add_shortcode('vmaps_fm', array(&$this, 'shortcode_full_map'));
		add_action('init', array(&$this, 'iframe_map_handler'));
		add_action('wp_ajax_get_location_markers', array(&$this, 'get_location_markers'));
		add_action('wp_ajax_nopriv_get_location_markers', array(&$this, 'get_location_markers'));
		
		// Register activation and deactivation functionality
		if( is_admin() ) {
			register_activation_hook(__FILE__, array(&$this,'activation'));
			register_deactivation_hook(__FILE__, array(&$this,'deactivate'));
		}
		
		// Initialize all the things
		add_action('init', array(&$this, 'init'), 10, 0);
	}
	
	/*--------------------------------------------------------*/
	/* function get_instance - Since 1.0.0
	/* Gets the current object instance for the class.
	/*--------------------------------------------------------*/
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/*--------------------------------------------------------*/
	/* function init - Since 1.0.0
	/* Run code upon initialization.
	/*--------------------------------------------------------*/
	function init() {
		// Flush rules after (site-wide) plugin upgrade
		if( get_option($this->plugin_name.'_version') != VAGABOND_MAPS_VERSION ) {
			update_option($this->plugin_name.'_version', VAGABOND_MAPS_VERSION);
		}
	}
	
	/*--------------------------------------------------------*/
	/* function activation - Since 1.0.0
	/* Run code upon plugin activation.
	/*--------------------------------------------------------*/
	function activation() {
		// Save the default start location to the database if not already found
		$start_location = get_option($this->plugin_name . '_start_location');
		if( $start_location === false ) 
			$start_location = update_option($this->plugin_name . '_start_location', $this->start_location_defaults);
		
		// Save the default enabled post types (none) to the database if not already found
		$post_types = get_option($this->plugin_name . '_enabled_post_types');
		if( $post_types === false ) 
			$post_types = update_option($this->plugin_name . '_enabled_post_types', $this->enabled_post_types_defaults);
	}
	
	/*--------------------------------------------------------*/
	/* function deactivate - Since 1.0.0
	/* Run code upon plugin deactivation.
	/*--------------------------------------------------------*/
	function deactivate() {
		delete_option($this->plugin_name.'_version');
	}
	
	/**********************************************************/
	/*
	/* ADMIN SUBPAGE FUNCTIONALITY
	/*
	/**********************************************************/
	
	/*--------------------------------------------------------*/
	/* function admin_subpage_scripts - Since 1.0.0
	/* Register and enqueue scripts for the admin subpage.
	/*--------------------------------------------------------*/
	function admin_subpage_scripts() {
		wp_enqueue_script('jquery');
		wp_register_script($this->plugin_name . '_admin_scripts', $this->plugin_url . '/admin/admin.js', array('jquery'));
		wp_enqueue_script($this->plugin_name . '_admin_scripts');
		wp_localize_script($this->plugin_name . '_admin_scripts', 'map_start_location', $this->get_map_start_loc());
	}
	
	/*--------------------------------------------------------*/
	/* function admin_subpage_styles - Since 1.0.0
	/* Register and enqueue styles for the admin subpage.
	/*--------------------------------------------------------*/
	function admin_subpage_styles() {
		wp_register_style($this->plugin_name . '_admin_styles', $this->plugin_url . '/admin/admin.css');
		wp_enqueue_style($this->plugin_name . '_admin_styles');
	}
	
	/*--------------------------------------------------------*/
	/* function add_admin_subpage - Since 1.0.0
	/* Add the admin subpage to the general options menu.
	/*--------------------------------------------------------*/
	function add_admin_subpage() {
		$hook = add_submenu_page('options-general.php', 'Vagabond Maps Settings', 'Vagabond Maps', 'administrator', $this->plugin_name, array(&$this, 'admin_subpage_content'));
		add_action('load-' . $hook, array(&$this,'admin_subpage_handler')); 
	}
	
	/*--------------------------------------------------------*/
	/* function admin_subpage_content - Since 1.0.0
	/* Displays the admin subpage HTML content.
	/*--------------------------------------------------------*/
	function admin_subpage_content() {
		if( !current_user_can('manage_options') )
			wp_die('You do not have sufficient permissions to access this page.');
	
		global $title;
	
		require('admin/admin.php');
	}
	
	/*--------------------------------------------------------*/
	/* function admin_subpage_handler - Since 1.0.0
	/* Handles the data submitted by the forms on the admin
	/* subpage.
	/*--------------------------------------------------------*/
	function admin_subpage_handler() {
		
		// Starting location form submission handling
		if( isset($_POST['vmaps_enabled_post_types_update']) && $_POST['vmaps_enabled_post_types_update'] == 'true' ) {
			
			// Validate that the form data is set and is an array
			$update_post_types = true;
			$submitted_post_types = ( !isset($_POST['vmaps_enabled_post_types']) ) ? array() : $_POST['vmaps_enabled_post_types'];
			if( !is_array($submitted_post_types) ) $update_post_types = false;
			
			// If the data submitted by the form checks out, then save it to the database
			if( $update_post_types === true )
				$_SESSION['vmaps_admin_message'] = $this->set_enabled_post_types($submitted_post_types);
			else
				$_SESSION['vmaps_admin_message'] = false;
			
		}
		
		// Starting location form submission handling
		if( isset($_POST['vmaps_start_location_update']) && $_POST['vmaps_start_location_update'] == 'true' ) {
		
			// Validate that all information needed to update the start location is there and populated
			$update_location = true;
			if( !isset($_POST['vmaps_start_loc_address'])	|| empty($_POST['vmaps_start_loc_address']) )	$update_location = false;
			if( !isset($_POST['vmaps_start_loc_lat'])		|| empty($_POST['vmaps_start_loc_lat']) )		$update_location = false;
			if( !isset($_POST['vmaps_start_loc_lng'])		|| empty($_POST['vmaps_start_loc_lng']) )		$update_location = false;
			if( !isset($_POST['vmaps_start_loc_zoom'])		|| empty($_POST['vmaps_start_loc_zoom']) )		$update_location = false;
			
			// If the data submitted by the form checks out, then save it to the database
			if( $update_location === true ) {
				$new_start_location = array(
					'complete_address' => $_POST['vmaps_start_loc_address'],
					'lat' => $_POST['vmaps_start_loc_lat'],
					'lng' => $_POST['vmaps_start_loc_lng'],
					'zoom' => $_POST['vmaps_start_loc_zoom']
				);
				
				$old_start_location = $this->get_map_start_loc();
				
				$update_location = false;
				foreach( $old_start_location as $key => $value ) {
					if( $new_start_location[$key] != $value )
						$update_location = true;
				}
				
				if( $update_location === false )
					$_SESSION['vmaps_admin_message'] = null;
				else
					$_SESSION['vmaps_admin_message'] = $this->set_map_start_loc($new_start_location);
			}
			
		}
		
	}
	
	/**********************************************************/
	/*
	/* POST EDITOR FUNCTIONALITY
	/*
	/**********************************************************/
	
	/*--------------------------------------------------------*/
	/* function post_editor_meta_boxes - Since 1.0.0
	/* Create location meta boxes for enabled post types.
	/*--------------------------------------------------------*/
	function post_editor_meta_boxes($post_type, $post) {
		$enabled_post_types = $this->get_enabled_post_types();
		
		// If a post type is location enabled, show the meta box for it
		if( in_array($post_type, $enabled_post_types) )
			add_meta_box('vmaps_location_meta_box', 'Vagabond Maps - Location Data', array( &$this, 'post_editor_location_meta_box'), $post_type, 'normal', 'high');
	}
	
	/*--------------------------------------------------------*/
	/* function post_editor_location_meta_box - Since 1.0.0
	/* Create location meta boxes on posts.
	/*--------------------------------------------------------*/
	function post_editor_location_meta_box($post) {
		$post_location = $this->get_post_loc($post->ID);
		?>
		<p><input type="text" id="vmaps_location_search" name="vmaps_location_search" /></p>
		<p id="vmaps_location"><strong>Current Location: </strong><?php echo $post_location['complete_address']; ?></p>
		<div id="vmaps_map_container"></div>
		<div id="vmaps_location_data">
			<input type="hidden" id="vmaps_location_address" name="vmaps_location_address" value="<?php echo $post_location['complete_address']; ?>" />
			<input type="hidden" id="vmaps_location_lat" name="vmaps_location_lat" value="<?php echo $post_location['lat']; ?>" />
			<input type="hidden" id="vmaps_location_lng" name="vmaps_location_lng" value="<?php echo $post_location['lng']; ?>" />
			<input type="hidden" id="vmaps_location_zoom" name="vmaps_location_zoom" value="<?php echo $post_location['zoom']; ?>" />
		</div>
		<?php
		wp_nonce_field( plugin_basename( __FILE__ ), 'vmaps_location_editor_nonce' );
	}
	
	/*--------------------------------------------------------*/
	/* function post_editor_scripts_and_styles - Since 1.0.0
	/* Enqueue and localize scripts for the post editor.
	/*--------------------------------------------------------*/
	function post_editor_scripts_and_styles() {
		global $post;
		
		if( is_admin() ) {
			$enabled_post_types = $this->get_enabled_post_types();
			
			// If a post type has location editing enabled, enqueue scripts
			if( in_array($post->post_type, $enabled_post_types) ) {
			
				// Echo the post ID in a variable for the TinyMCE editor embed buttons
				echo '<script type="text/javascript"> var current_post_id = ' . $post->ID . ';</script>';
				
				// Scripts
				wp_enqueue_script('jquery');
				wp_register_script($this->plugin_name . '_post_editor_scripts', $this->plugin_url . '/post_editor/post_editor.js', array('jquery'));
				wp_enqueue_script($this->plugin_name . '_post_editor_scripts');
				wp_localize_script($this->plugin_name . '_post_editor_scripts', 'post_location', $this->get_post_loc($post->ID));
				
				// Styles
				wp_register_style($this->plugin_name . '_post_editor_styles', $this->plugin_url . '/post_editor/post_editor.css');
				wp_enqueue_style($this->plugin_name . '_post_editor_styles');
				
			}
		}
	}
	
	/*--------------------------------------------------------*/
	/* function post_editor_handler - Since 1.0.0
	/* Handles saving of post location data for single posts.
	/*--------------------------------------------------------*/
	function post_editor_handler($post_ID, $post) {

		// Keel over if nonce is missing, this is an autosave, or user doesn't have permissions to edit the post
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;
		if( empty($_POST['vmaps_location_editor_nonce']) || !wp_verify_nonce($_POST['vmaps_location_editor_nonce'], plugin_basename(__FILE__)) )
			return;
		if ( !current_user_can('edit_post', $post_ID) ) // Generic for all post types, especially important with custom post types
			return;
		
		$enabled_post_types = $this->get_enabled_post_types();
			
		// If a post type has location editing enabled, save the data
		if( in_array($post->post_type, $enabled_post_types) ) {
		
			// Validate that all information needed to update the post location is there and populated
			$update_location = true;
			if( !isset($_POST['vmaps_location_address'])	|| empty($_POST['vmaps_location_address']) )	$update_location = false;
			if( !isset($_POST['vmaps_location_lat'])		|| empty($_POST['vmaps_location_lat']) )		$update_location = false;
			if( !isset($_POST['vmaps_location_lng'])		|| empty($_POST['vmaps_location_lng']) )		$update_location = false;
			if( !isset($_POST['vmaps_location_zoom'])		|| empty($_POST['vmaps_location_zoom']) )		$update_location = false;
			
			// If the data submitted by the form checks out, then save it to the database
			if( $update_location === true ) {
				$new_location = array(
					'complete_address' => $_POST['vmaps_location_address'],
					'lat' => $_POST['vmaps_location_lat'],
					'lng' => $_POST['vmaps_location_lng'],
					'zoom' => $_POST['vmaps_location_zoom']
				);
				
				$old_location = $this->get_post_loc($post->ID);
				
				$update_location = false;
				foreach( $old_location as $key => $value ) {
					if( $new_location[$key] != $value )
						$update_location = true;
				}
				
				$updated = $this->set_post_loc($post->ID, $new_location);
			}
		}

	}
	
	/*--------------------------------------------------------*/
	/* function add_post_editor_embed_buttons - Since 1.0.0
	/* Load map embed buttons for TinyMCE.  Registers TinyMCE
	/* plugins and associated JavaScript files.
	/* https://codex.wordpress.org/TinyMCE_Custom_Buttons
	/*--------------------------------------------------------*/
	function add_post_editor_embed_buttons() {
		if( current_user_can('edit_posts') && current_user_can('edit_pages') && get_user_option('rich_editing') ) {
			add_filter('mce_external_plugins', array(&$this, 'register_tinymce_javascript'));
			add_filter('mce_buttons', array(&$this, 'register_editor_embed_buttons'));
		}
	}
	
	/*--------------------------------------------------------*/
	/* function register_tinymce_javascript - Since 1.0.0
	/* Adds the tinymce_plugins.js file to the TinyMCE
	/* plugins array.  Work it gurl.
	/*--------------------------------------------------------*/
	function register_tinymce_javascript($plugin_array) {
		$plugin_array['vagabond_maps_single_post_map_embed'] = $this->plugin_url . '/post_editor/tinymce_plugins.js';
		$plugin_array['vagabond_maps_full_map_embed'] = $this->plugin_url . '/post_editor/tinymce_plugins.js';
		return $plugin_array;
	}
	
	/*--------------------------------------------------------*/
	/* function register_editor_embed_buttons - Since 1.0.0
	/* Pushes the map embed buttons to the end of the TinyMCE
	/* buttons array.
	/*--------------------------------------------------------*/
	function register_editor_embed_buttons($buttons) {
		array_push($buttons, '|', 'vagabond_maps_single_post_map_embed');
		array_push($buttons, '|', 'vagabond_maps_full_map_embed');
		return $buttons;
	}
	
	/*--------------------------------------------------------*/
	/* function shortcode_single_post_map - Since 1.0.0
	/* Replaces the [vmaps_spm] shortcode with the iframe that
	/* handles the google map output for a single post.
	/*--------------------------------------------------------*/
	function shortcode_single_post_map($params = array()) {
		global $post;
		
		// Defaults
		extract(shortcode_atts(array(
			'id' => $post->ID,
			'h' => 300,
			'h_in' => 'px',
			'w' => 300,
			'w_in' => 'px'
		), $params));
		
		// Sanitize and verify all the things
		$id = ( !is_numeric($id) ) ? $post->ID : intval($id);
		$h = ( !is_numeric($h) ) ? 300 : intval($h);
		$h_in = ( $h_in != '%' && $h_in != 'px' ) ? 'px' : $h_in;
		$w = ( !is_numeric($w) ) ? 300 : intval($w);
		$w_in = ( $w_in != '%' && $w_in != 'px' ) ? 'px' : $w_in;
		
		// Return the output for the given shortcode
		return '<iframe src="' . site_url('/') . '?vmaps_spm=' . urlencode($id) .'" style="height:' . $h . $h_in . ';width:' . $w . $w_in . ';"></iframe>';
	}
	
	/*--------------------------------------------------------*/
	/* function shortcode_full_map - Since 1.0.0
	/* Replaces the [vmaps_fm] shortcode with the iframe that
	/* handles the google map output for a map of all
	/* locations.
	/*--------------------------------------------------------*/
	function shortcode_full_map($params = array()) {
		// Defaults
		extract(shortcode_atts(array(
			'pt' => '',
			'h' => 300,
			'h_in' => 'px',
			'w' => 300,
			'w_in' => 'px'
		), $params));
		
		// Sanitize and verify all the things
		$pt = ( !empty($pt) ) ? '&pt=' . urlencode($pt) : '';
		$h = ( !is_numeric($h) ) ? 300 : intval($h);
		$h_in = ( $h_in != '%' && $h_in != 'px' ) ? 'px' : $h_in;
		$w = ( !is_numeric($w) ) ? 300 : intval($w);
		$w_in = ( $w_in != '%' && $w_in != 'px' ) ? 'px' : $w_in;
		
		// Return the output for the given shortcode
		return '<iframe src="' . site_url('/') . '?vmaps_fm=1' . $pt . '" style="height:' . $h . $h_in . ';width:' . $w . $w_in . ';"></iframe>';
	}
	
	/*--------------------------------------------------------*/
	/* function iframe_map_handler - Since 1.0.0
	/* Handles iframe GET requests for the embedded maps
	/*--------------------------------------------------------*/
	function iframe_map_handler() {
	
		// Handle GET requests for displaying a map for a given post
		if( isset($_GET['vmaps_spm']) && !empty($_GET['vmaps_spm']) && is_numeric($_GET['vmaps_spm']) ) {
		
			// Get the post data for the supplied post ID
			$p_id = intval($_GET['vmaps_spm']);
			$map_post = get_post($p_id);
			
			if( !empty($map_post) ) {
			
				// Function setup_postdata MUST use global $post variable
				// https://codex.wordpress.org/Function_Reference/setup_postdata
				global $post;
				$original_post = $post;
				$post = $map_post;
				setup_postdata($post); // Needed for get_the_excerpt() below
				
				// Get the location information for this post
				$post_details = array(
					'id' => $post->ID, 
					'title' => $post->post_title, 
					'permalink' => get_permalink($post->ID),
					'excerpt' => mb_strimwidth(strip_shortcodes(strip_tags(get_the_excerpt())), 0, 220, '...') //http://stackoverflow.com/questions/15074423/remove-shortcode-from-excerpt-have-codes
				);
				$location_details = $this->get_post_loc($post->ID);
				$location = array_merge($post_details, $location_details);
				
				// Reset the post data
				wp_reset_postdata();
				$post = $original_post;
				
				// Everything should check out, display the map for the given post
				require('views/single_map.php');
				
			}
			
			exit;
		}
		
		// Handle GET requests for displaying a map with locations for a specific post type, or all locations
		if( isset($_GET['vmaps_fm']) && intval($_GET['vmaps_fm']) == 1 ) {
			
			$post_type = ( isset($_GET['pt']) && !empty($_GET['pt']) ) ? urldecode($_GET['pt']) : '';
			
			require('views/full_map.php');
			
			exit;
		}
	}
	
	/*--------------------------------------------------------*/
	/* function get_location_markers - Since 1.0.0
	/* Handles WordPress AJAX requests for fetching the list
	/* of post location markers in the system based on 
	/* post_type.  Returns a JSON object.
	/*--------------------------------------------------------*/
	function get_location_markers() {

		global $wpdb;
		$location_posts = false;
		$locations = array();
		
		// Filter by post type if one was supplied
		$post_type = ( isset($_REQUEST['post_type']) && !empty($_REQUEST['post_type']) ) ? $_REQUEST['post_type'] : '';
		$post_type = ( post_type_exists($post_type) ) ? $post_type : false;
		
		// Exclude all posts that are not enabled
		$enabled_post_types = $this->get_enabled_post_types();
		$post_type_query = ( !empty($enabled_post_types) ) ? "AND p.post_type IN ('" . implode("','",$enabled_post_types) . "')" : false;
		
		if( $post_type === false ) {
			if( $post_type_query !== false ) {
				// Fetch all location posts with location data attached
				$location_posts = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT p.ID, p.post_title
						FROM {$wpdb->posts} as p, {$wpdb->postmeta} as pm
						WHERE p.post_status = 'publish'
						AND p.ID = pm.post_id
						AND pm.meta_key = '%s'
						AND pm.meta_value IS NOT NULL {$post_type_query}
						",
						$this->plugin_name . '_location'
					)
				);
			}
		}
		else {
			if( $post_type_query !== false ) {
				// Fetch location posts with location data attached by post type
				$location_posts = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT p.ID, p.post_title
						FROM {$wpdb->posts} as p, {$wpdb->postmeta} as pm
						WHERE p.post_type = '%s'
						AND p.post_status = 'publish'
						AND p.ID = pm.post_id
						AND pm.meta_key = '%s'
						AND pm.meta_value IS NOT NULL {$post_type_query}
						",
						$post_type, $this->plugin_name . '_location'
					)
				);
			}
		}
		
		if( !empty($location_posts) ) {
		
			// Function setup_postdata MUST use global $post variable
			// https://codex.wordpress.org/Function_Reference/setup_postdata
			global $post;
			$original_post = $post;
			
			foreach( $location_posts as $location_post ) {
				$post = get_post($location_post->ID);
				setup_postdata($post); // Needed for get_the_excerpt() below
				
				// Get the location information for this post
				$post_details = array(
					'id' => $post->ID, 
					'title' => $post->post_title, 
					'permalink' => get_permalink($post->ID),
					'excerpt' => mb_strimwidth(strip_shortcodes(strip_tags(get_the_excerpt())), 0, 220, '...') //http://stackoverflow.com/questions/15074423/remove-shortcode-from-excerpt-have-codes
				);
				$location_details = $this->get_post_loc($post->ID);
				$locations[] = array_merge($post_details, $location_details);
			}
			
			// Reset the post data
			wp_reset_postdata();
			$post = $original_post;
		}
		
		// Output the locations as a JSON object
		$json = json_encode($locations);
		echo $json;
		die();
	}
	
	/**********************************************************/
	/*
	/* GENERAL FUNCTIONS
	/*
	/**********************************************************/
	
	/*--------------------------------------------------------*/
	/* function get_map_start_loc - Since 1.0.0
	/* Returns the saved start location array, or the 
	/* start_location_defaults if not found.
	/*--------------------------------------------------------*/
	function get_map_start_loc() {
		$start_location = get_option($this->plugin_name . '_start_location', $this->start_location_defaults);
		return $start_location;
	}
	
	/*--------------------------------------------------------*/
	/* function set_map_start_loc(array) - Since 1.0.0
	/* Return null if invalid parameter submitted, false if 
	/* option couldn't be updated, true upon success.
	/* Format of submitted parameter should match the 
	/* start_location_defaults structure above.
	/*--------------------------------------------------------*/
	function set_map_start_loc($new_location) {
		
		// Verify submitted parameters
		if( !is_array($new_location) || empty($new_location) ) return null;
		
		// Merge blank array into submitted data for validation
		$blank_array = array('complete_address' => '', 'lat' => '', 'lng' => '', 'zoom' => '');
		$new_location = array_merge($blank_array, (array)$new_location);
		
		// Validate submitted location
		if( empty($new_location['complete_address']) || empty($new_location['lat']) || empty($new_location['lng']) || empty($new_location['zoom']) )
			return null;
		
		// Everything is good to go; save the starting location to the database
		$updated = update_option($this->plugin_name . '_start_location', $new_location);
		
		return $updated;
	}
	
	/*--------------------------------------------------------*/
	/* function get_post_loc - Since 1.0.0
	/* Returns the saved location array for a given post id,
	/* or start_location_defaults location array if not found.
	/*--------------------------------------------------------*/
	function get_post_loc($post_id) {
		$post_location = get_post_meta( $post_id, $this->plugin_name . '_location', true );
		if( empty($post_location) )
			$post_location = $this->start_location_defaults;
		return $post_location;
	}
	
	/*--------------------------------------------------------*/
	/* function set_post_loc(array) - Since 1.0.0
	/* Return null if invalid parameter submitted, false if 
	/* option couldn't be updated, true upon success.
	/* Format of submitted parameter should match the 
	/* start_location_defaults structure above.
	/*--------------------------------------------------------*/
	function set_post_loc($post_id, $new_location) {
		
		// Verify submitted parameters
		if( !is_array($new_location) || empty($new_location) ) return null;
		
		// Merge blank array into submitted data for validation
		$blank_array = array('complete_address' => '', 'lat' => '', 'lng' => '', 'zoom' => '');
		$new_location = array_merge($blank_array, (array)$new_location);
		
		// Validate submitted location
		if( empty($new_location['complete_address']) || empty($new_location['lat']) || empty($new_location['lng']) || empty($new_location['zoom']) )
			return null;
		
		// Everything is good to go; save the starting location to the database
		$updated = update_post_meta($post_id, $this->plugin_name . '_location', $new_location);
		
		return $updated;
	}
	
	/*--------------------------------------------------------*/
	/* function get_enabled_post_types() - Since 1.0.0
	/* Returns the saved post types which will have location
	/* data attached to them.
	/*--------------------------------------------------------*/
	function get_enabled_post_types() {
		$post_types = get_option($this->plugin_name . '_enabled_post_types', $this->enabled_post_types_defaults);
		return $post_types;
	}
	
	/*--------------------------------------------------------*/
	/* function set_enabled_post_types(array) - Since 1.0.0
	/* Return null if invalid parameter submitted, false if 
	/* option couldn't be updated, true upon success.
	/* Format of submitted parameter should be a single 
	/* dimensional array of post types.
	/*--------------------------------------------------------*/
	function set_enabled_post_types($post_types) {
		
		// Verify submitted parameters
		if( !is_array($post_types) ) return null;
		
		// Make sure array is one dimensional, we like our data boring
		if( !empty($post_types) ) {
			foreach( $post_types as $post_type ) {
				if( is_array($post_type) ) return null;
			}
		}
		
		// If the saved post types match the submitted post types, return true
		$settings_changed = false;
		$saved_post_types = get_option($this->plugin_name . '_enabled_post_types', $this->enabled_post_types_defaults);
		
		// If a post type was removed from the selected post types, the settings have changed
		if( !empty($saved_post_types) ) {
			foreach( $saved_post_types as $saved_post_type ) {
				if( !in_array($saved_post_type, $post_types) ) {
					$settings_changed = true;
					break;
				}
			}
		}
		
		// If a post type was added to the selected post types, the settings have changed
		if( !empty($post_types) ) {
			foreach( $post_types as $post_type ) {
				if( !in_array($post_type, $saved_post_types) ) {
					$settings_changed = true;
					break;
				}
			}
		}
		
		if( $settings_changed === false ) return true;
		
		// Everything is good to go; save the enabled post types to the database
		$updated = update_option($this->plugin_name . '_enabled_post_types', $post_types);
		
		return $updated;
	}
}

// Instantiate the class, take it to limit, ride into the danger zone
$vagabond_maps = new vagabond_maps();