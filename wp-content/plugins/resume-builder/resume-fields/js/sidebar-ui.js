window.resume = window.resume || {};

(function($) {

	var resume = window.resume;

	$(document).ready(function() {

		// Append an add sidebar button
		$addButton = $('<a class="add-new-h2 resume-btn-add-sidebar" href="#">' + rbfl10n.add_sidebar + '</a>');

		$addButton.on('click', function(event) {
			var sidebarName = $.trim( window.prompt( rbfl10n.enter_name_of_new_sidebar ) );

			if (sidebarName) {
				resume.sidebarManager(sidebarName, 'add', true);
			}

			event.preventDefault();
		});

		$('#wpbody-content > .wrap > :first:header').append($addButton);

		// Add a remove sidebar button to each resume-sidebar
		$('.sidebar-resume-sidebar').each(function() {
			var sidebarId = $(this).find('.widgets-sortables').attr('id');
			var $removeButton = $('<a href="#" class="resume-btn-remove-sidebar" />');

			$removeButton.on('click', function(event) {
				var confirmation = confirm( rbfl10n.remove_sidebar_confirmation );

				if (confirmation) {
					resume.sidebarManager(sidebarId, 'remove', true);
					event.stopPropagation();
				}

				event.preventDefault();
			});

			$(this).find('> .widgets-sortables > .sidebar-name').append($removeButton);
		});

	});

}(jQuery));