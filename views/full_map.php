<?php
/**********************************************************/
/*
/* Vagabond Maps - Full Map View
/*
/**********************************************************/

// Get post type object for the proper page title
$post_type_obj = ( isset($post_type) && !empty($post_type) && post_type_exists($post_type) ) ? get_post_type_object($post_type) : false;
$title = ( $post_type_obj !== false ) ? 'All locations for ' . $post_type_obj->labels->singular_name : 'All locations';

// Get the map starting location
$starting_location = $this->get_map_start_loc();

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Vagabond Maps - All locations for "<?php echo $post_type; ?>"</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<style>
			html, body, #vmaps_fm_canvas {
				height: 100%;
				margin: 0;
				padding: 0;
			}
			.iw_heading_link {
				color: inherit;
				text-decoration: none;
			}
			.iw_heading {
				margin: 1em 0;
				font-size: 1.6em;
				line-height: 0.8em;
			}
		</style>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
		<script type="text/javascript">
			var map;
			var markers = new Array();
			
			/*--------------------------------------------------------*/
			/* function initialize - Since 1.0.0
			/* Callback function for Google Maps API.  Creates map
			/* and sets the initial zoom and center marker.
			/*--------------------------------------------------------*/
			function initialize() {
			
				// Initialize the map
				var map_options = {
					zoom: <?php echo $starting_location['zoom']; ?>,
					center: new google.maps.LatLng(<?php echo $starting_location['lat']; ?>, <?php echo $starting_location['lng']; ?>)
				};
				
				map = new google.maps.Map(document.getElementById('vmaps_fm_canvas'), map_options);
				
			}
			
			google.maps.event.addDomListener(window, 'load', initialize);
			
		</script>
		<script type="text/javascript">
			/*--------------------------------------------------------*/
			/* AJAX Functionality
			/* Get a JSON object from the WordPress AJAX function
			/* get_location_markers found in the main Vagabond Maps
			/* plugin file.  It grabs all locations in the system and
			/* drops them into the Google Map.
			/*--------------------------------------------------------*/
			jQuery(document).ready(function() {
				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data: {
						'action': 'get_location_markers',
						'post_type': '<?php echo $post_type; ?>'
					},
					success: function(data) {
						// Parse the JSON grabbed via AJAX; if not empty, then loop the object add the markers to the map
						var locations = jQuery.parseJSON(data);
						if( !jQuery.isEmptyObject(locations) ) {
							locations.forEach(function(location) {
							
								// If user clicks the infowindow header link, redirect the top window to that page
								var heading_link_id = 'iw_heading_link_' + location.id;
								var onclick = 'onclick="window.top.location.href = document.getElementById(\'' + heading_link_id + '\').getAttribute(\'href\', 2);"';
								
								// Setup the infowindow for this location
								var infowindow_content = '<div class="infowindow_content">' +
									'<a ' + onclick + ' id="' + heading_link_id + '" class="iw_heading_link" href="' + location.permalink + '">' +
										'<h2 class="iw_heading">' + location.title + '</h2>' +
									'</a>' +
									'<div class="iw_content">' +
										'<p>' + location.excerpt + '</p>' +
										'<p>' + location.complete_address + '</p>' +
									'</div>' +
								'</div>';
								
								var infowindow = new google.maps.InfoWindow({
									content: infowindow_content
								});
								
								// Add the marker to the map
								var marker = new google.maps.Marker({
									position: new google.maps.LatLng(location.lat, location.lng),
									map: map,
									title: location.title
								});
								
								// Add a listner for when user clicks the marker
								google.maps.event.addListener(marker, 'click', function() {
									infowindow.open(map, marker);
								});
								
								// Keep track of the markers added to the map
								markers.push(marker);
							});
						}
					},
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
			});
		</script>
	</head>
	<body>
		<div id="vmaps_fm_canvas"></div>
	</body>
</html>