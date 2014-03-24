<?php
/**********************************************************/
/*
/* Vagabond Maps - Single Post Map View
/*
/**********************************************************/
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Vagabond Maps - <?php echo $location['title']; ?></title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<style>
			html, body, #vmaps_spm_canvas {
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
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
		<script type="text/javascript">
			var map;
			
			/*--------------------------------------------------------*/
			/* function initialize - Since 1.0.0
			/* Callback function for Google Maps API.  Creates map
			/* and sets the initial zoom and center marker.
			/*--------------------------------------------------------*/
			function initialize() {
			
				// Initialize the map
				var map_options = {
					zoom: <?php echo $location['zoom']; ?>,
					center: new google.maps.LatLng(<?php echo $location['lat']; ?>, <?php echo $location['lng']; ?>)
				};
				
				map = new google.maps.Map(document.getElementById('vmaps_spm_canvas'), map_options);
				
				// If user clicks the infowindow header link, redirect the top window to that page
				var heading_link_id = 'iw_heading_link_<?php echo $location['id']; ?>';
				var onclick = 'onclick="window.top.location.href = document.getElementById(\'' + heading_link_id + '\').getAttribute(\'href\', 2);"';
				
				// Setup the infowindow for this location
				var infowindow_content = '<div class="infowindow_content">' +
					'<a ' + onclick + ' id="' + heading_link_id + '" class="iw_heading_link" href="<?php echo $location['permalink']; ?>">' +
						'<h2 class="iw_heading">' +
							'<?php echo $location['title']; ?>' +
						'</h2>' +
					'</a>' +
					'<div class="iw_content">' +
						'<p><?php echo $location['excerpt']; ?></p>' +
						'<p><?php echo $location['complete_address']; ?></p>' +
					'</div>' +
				'</div>';
				
				var infowindow = new google.maps.InfoWindow({
					content: infowindow_content
				});
				
				// Add the marker to the map
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(<?php echo $location['lat']; ?>, <?php echo $location['lng']; ?>),
					map: map,
					title: "<?php echo $location['title']; ?>"
				});
				
				// Add a listner for when user clicks the marker
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
			}
			
			google.maps.event.addDomListener(window, 'load', initialize);
		</script>
	</head>
	<body>
		<div id="vmaps_spm_canvas"></div>
	</body>
</html>