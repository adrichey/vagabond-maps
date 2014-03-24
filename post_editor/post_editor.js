/**********************************************************/
/*
/* Vagabond Maps - Post Editor Scripts
/*
/**********************************************************/

var marker, map;

/*--------------------------------------------------------*/
/* function initialize - Since 1.0.0
/* Callback function for Google Maps API.  Creates map
/* and sets the initial zoom and center marker.
/*--------------------------------------------------------*/
function initialize() {
	
	// Initialize the map
	var map_options = {
		zoom: parseInt(post_location.zoom),
		center: new google.maps.LatLng(parseFloat(post_location.lat), parseFloat(post_location.lng))
	};
	
	map = new google.maps.Map(document.getElementById('vmaps_map_container'), map_options);
	
	// Add the initial center marker to the map
	marker = new google.maps.Marker({
		position: new google.maps.LatLng(parseFloat(post_location.lat), parseFloat(post_location.lng)),
		map: map,
		title: post_location.complete_address
	});
	
	// Autocomplete functionality
	// https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform
	var autocomplete = new google.maps.places.Autocomplete(
		(document.getElementById('vmaps_location_search')),
		{ types: ['geocode'] }
	);
	google.maps.event.addListener(autocomplete, 'place_changed', function() {
		fill_in_address();
	});
	
	/*--------------------------------------------------------*/
	/* function fill_in_address - Since 1.0.0
	/* Function which is called when the autocomplete input
	/* is used.  Prepares data for saving and refreshes map.
	/*--------------------------------------------------------*/
	function fill_in_address() {
		// Get the place details from the autocomplete object and record them in hidden input variables
		var place = autocomplete.getPlace();
		console.log(place);
		document.getElementById('vmaps_location').innerHTML = '<strong>Current Location: </strong>' + place.formatted_address;
		document.getElementById('vmaps_location_address').value = place.formatted_address;
		document.getElementById('vmaps_location_lat').value = place.geometry.location.k;
		document.getElementById('vmaps_location_lng').value = place.geometry.location.A;
		document.getElementById('vmaps_location_zoom').value = map.getZoom();
		
		// Set the marker to the new location
		autocomplete_location = new google.maps.LatLng(place.geometry.location.k, place.geometry.location.A);
		marker.setMap(null);
		marker = new google.maps.Marker({
			position: autocomplete_location,
			map: map,
			title: place.formatted_address
		});
		
		// Center the map on the new location
		map.setCenter(autocomplete_location);
	}
	
	// Zoom functionality
	google.maps.event.addListener(map, 'zoom_changed', function() {
		document.getElementById('vmaps_location_zoom').value = map.getZoom();
	});
	
}

/*--------------------------------------------------------*/
/* function load_map_scripts - Since 1.0.0
/* Load the Google Maps script asynchronously to make sure
/* the page has properly loaded first.
/*--------------------------------------------------------*/
function load_map_scripts() {
	var script = document.createElement('script');
	script.type = 'text/javascript';
	
	// Callback needs to match initialization function above; libraries=places is needed for autocorrect functionality
	script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&callback=initialize';
	
	document.body.appendChild(script);
}

window.onload = load_map_scripts;