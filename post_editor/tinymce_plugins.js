/**********************************************************/
/*
/* Vagabond Maps - TinyMCE Embed Button Functionality
/*
/**********************************************************/

/*--------------------------------------------------------*/
/* Single Post Map Embed Button TinyMCE Plugin
/*--------------------------------------------------------*/
tinymce.PluginManager.add('vagabond_maps_single_post_map_embed', function(editor, url) {

	// Add an embed button that opens a dialog window to embed a map for an individual post
	editor.addButton('vagabond_maps_single_post_map_embed', {
		icon: 'vmaps_single_post_map_embed',
		onclick: function() {
			// On click of the embed button, display a dialog window with map options
			editor.windowManager.open({
				title: 'Vagabond Maps - Embed Post Map',
				body: [
					{type: 'textbox', name: 'height', label: 'Height'},
					{type: 'listbox', 
						name: 'height_in', 
						label: 'Height in', 
						'values': [
							{text: 'pixels', value: 'px'},
							{text: 'percentage', value: '%'}
						]
					},
					{type: 'textbox', name: 'width', label: 'Width'},
					{type: 'listbox', 
						name: 'width_in', 
						label: 'Width in', 
						'values': [
							{text: 'pixels', value: 'px'},
							{text: 'percentage', value: '%'}
						]
					}
				],
				onsubmit: function(e) {
					// Insert shortcode content into the post editor when the dialog form is submitted
					var output = '[vmaps_spm id="' + current_post_id + '" '; // Global post ID set in vagabond-maps.php -> post_editor_scripts_and_styles()
					output += 'h="' + e.data.height + '" ';
					output += 'h_in="' + e.data.height_in + '" ';
					output += 'w="' + e.data.width + '" ';
					output += 'w_in="' + e.data.width_in + '"]';
					editor.insertContent(output);
				}
			});
		}
	});
	
	// Adds a menu item to the tools menu
	editor.addMenuItem('vagabond_maps_single_post_map_embed', {
		text: 'Vagabond Maps - Embed Post Map',
		context: 'tools',
		onclick: function() {
			// Open window with my plugin author url
			editor.windowManager.open({
				title: 'Vagabond Maps by Alan Richey',
				url: 'http://alanrichey.net',
				width: 400,
				height: 300,
				buttons: [{
					text: 'Close',
					onclick: 'close'
				}]
			});
		}
	});
});

/*--------------------------------------------------------*/
/* Full Map Embed Button TinyMCE Plugin
/*--------------------------------------------------------*/
tinymce.PluginManager.add('vagabond_maps_full_map_embed', function(editor, url) {

	// Add an embed button that opens a dialog window to embed a map for an individual post
	editor.addButton('vagabond_maps_full_map_embed', {
		icon: 'vmaps_full_map_embed',
		onclick: function() {
			// On click of the embed button, display a dialog window with map options
			editor.windowManager.open({
				title: 'Vagabond Maps - Embed Full Map',
				body: [
					{type: 'textbox', name: 'height', label: 'Height'},
					{type: 'listbox', 
						name: 'height_in', 
						label: 'Height in', 
						'values': [
							{text: 'pixels', value: 'px'},
							{text: 'percentage', value: '%'}
						]
					},
					{type: 'textbox', name: 'width', label: 'Width'},
					{type: 'listbox', 
						name: 'width_in', 
						label: 'Width in', 
						'values': [
							{text: 'pixels', value: 'px'},
							{text: 'percentage', value: '%'}
						]
					}
				],
				onsubmit: function(e) {
					// Insert shortcode content into the post editor when the dialog form is submitted
					var output = '[vmaps_fm ';
					output += 'h="' + e.data.height + '" ';
					output += 'h_in="' + e.data.height_in + '" ';
					output += 'w="' + e.data.width + '" ';
					output += 'w_in="' + e.data.width_in + '"]';
					editor.insertContent(output);
				}
			});
		}
	});
	
	// Adds a menu item to the tools menu
	editor.addMenuItem('vagabond_maps_full_map_embed', {
		text: 'Vagabond Maps - Embed Full Map',
		context: 'tools',
		onclick: function() {
			// Open window with my plugin author url
			editor.windowManager.open({
				title: 'Vagabond Maps by Alan Richey',
				url: 'http://alanrichey.net',
				width: 400,
				height: 300,
				buttons: [{
					text: 'Close',
					onclick: 'close'
				}]
			});
		}
	});
});