<?php 
/**********************************************************/
/*
/* Vagabond Maps - Admin Subpage Content
/*
/**********************************************************/

// Get the saved map start location
$map_start_location = $this->get_map_start_loc();

// Get all WordPress post types
$post_types = get_post_types( '', 'names' );

// Get all map enabled post types
$enabled_post_types = $this->get_enabled_post_types();

/*--------------------------------------------------------*/
/* Messages
/*--------------------------------------------------------*/

// If there are update or error messages, display them on the subpage
if( isset($_SESSION['vmaps_admin_message']) && !empty($_SESSION['vmaps_admin_message']) ) {
	?>
	<div class="updated">
		<p><?php _e( 'Your settings have been saved.' ); ?></p>
	</div>
	<?php
}
elseif( isset($_SESSION['vmaps_admin_message']) && $_SESSION['vmaps_admin_message'] === false ) {
	?>
	<div class="error">
		<p><?php _e( 'There was an issue saving your settings.  Please try again.' ); ?></p>
	</div>
	<?php
}

// Clean up after yourself
if( isset($_SESSION['vmaps_admin_message']) ) unset($_SESSION['vmaps_admin_message']);

?>
<!-- Vagabond Maps - /vagabond-maps/admin/admin.php -->
<div class="vmaps_admin wrap">
	<h2><?php echo $title; ?></h2>
	<div class="section vmaps_admin_settings">
		<div class="content">
			<h3><?php _e( 'Enabled Post Types' ); ?></h3>
			<p><?php _e( 'Select the post types from the list below that you would like to use to store and display location data.' ); ?></p>
			<form id="vmaps_enabled_post_types_form" action="" method="post">
				<input type="hidden" name="vmaps_enabled_post_types_update" value="true" />
				<table>
					<tbody>
						<?php
							foreach( $post_types as $post_type ) {
								$checked = (in_array($post_type, $enabled_post_types)) ? 'checked="checked" ' : '';
								?>
								<tr>
									<td><input type="checkbox" name="vmaps_enabled_post_types[]" value="<?php echo $post_type; ?>" <?php echo $checked; ?>/></td>
									<td><?php echo $post_type; ?></td>
								</tr>
								<?php
							}
						?>
					</tbody>
				</table>
				<input type="submit" value="Update Settings" />
			</form>
			<h3><?php _e( 'Starting Location'); ?></h3>
			<p><?php _e( 'Select a starting location and zoom for the main map that will display all location data and markers.'); ?></p>
			<p><?php _e( 'Note: The blue marker is just for reference and will not be shown on the map.'); ?></p>
			<p><input type="text" id="vmaps_loc_search" name="vmaps_loc_search" /></p>
			<p id="vmaps_start_loc"><strong>Current Location: </strong><?php echo $map_start_location['complete_address']; ?></p>
			<div id="vmaps_map_container"></div>
			<form id="vmaps_start_loc_form" action="" method="post">
				<input type="hidden" name="vmaps_start_location_update" value="true" />
				<input type="hidden" id="vmaps_start_loc_address" name="vmaps_start_loc_address" value="<?php echo $map_start_location['complete_address']; ?>" />
				<input type="hidden" id="vmaps_start_loc_lat" name="vmaps_start_loc_lat" value="<?php echo $map_start_location['lat']; ?>" />
				<input type="hidden" id="vmaps_start_loc_lng" name="vmaps_start_loc_lng" value="<?php echo $map_start_location['lng']; ?>" />
				<input type="hidden" id="vmaps_start_loc_zoom" name="vmaps_start_loc_zoom" value="<?php echo $map_start_location['zoom']; ?>" />
				<input type="submit" value="Update Settings" />
			</form>
		</div>
	</div>
</div>
