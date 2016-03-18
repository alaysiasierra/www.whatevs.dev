window.resume = window.resume || {};

(function($) {

	var resume = window.resume;

	// Main app holder
	resume.main = {};

	// Views holder
	resume.views = {};

	// Lazyload views holder
	resume.lazyload = {};

	// Collections holder
	resume.collections = {};

	// Containers holder
	resume.containers = {};

	// Fields holder
	resume.fields = {};

	/*
	|--------------------------------------------------------------------------
	| Main App VIEW
	|--------------------------------------------------------------------------
	|
	| Responsible for initializing all the containers, including widgets.
	|
	| Views reflect what the applications data models look like.
	| They also listen to events and react accordingly.
	|
	| @element: document
	| @holder:  resume.views.main
	|
	*/
	resume.main.View = Backbone.View.extend({
		el: document,

		/*
		 * Hooks up all the needed events and prepares the container collection.
		 */
		initialize: function() {
			var _this = this;

			this.$body = this.$('body');
			this.$body.addClass('resume-fields');

			if ( !this.$body.is('.mobile') ) {
				this.$body.addClass('resume-desktop');
			}

			if ( ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch ) {
				this.$body.addClass('touchscreen');
			}

			// Define an empty containers collection, that should be populated by the setContainers method after events are hooked.
			this.containersCollection = resume.collections.containers = new resume.containers.Collection;

			// Listen for a collection reset and re-populate the containers. Useful for reinitialization.
			this.listenTo(this.containersCollection, 'reset', this.setContainers);

			// Render each container when it's added to the collection
			this.listenTo(this.containersCollection, 'add', this.renderContainer);

			// Create the sidebars collection and populate it.
			this.sidebarsCollection = resume.collections.sidebars = new Backbone.Collection(resume_json.sidebars);

			// Pass the sidebars collection event to the sidebar manager.
			this.listenTo(this.sidebarsCollection, 'add remove', function(model, collection, event) {
				var action = event.add ? 'add' : 'remove';
				var name = model.get('name');
				resume.sidebarManager(name, action);
			});

			// When a container is sorted (using ui-sortable), send the event to all fields using our custom "propagate" event
			this.$('div.widgets-sortables, .meta-box-sortables').on('sortstart sortstop', function(event, ui) {
				var containerID = $(ui.item).attr('id') || '';

				if ( containerID.indexOf('widget-') === 0 ) {
					var containerID = containerID.replace(/widget-\d+_/, '');
				}

				if ( containerID && !_.isUndefined( resume.views[containerID] ) ) {
					resume.views[containerID].trigger('propagate', event);
				}
			});

			// Listen for the WordPress widget events and handle the widget initialization/update
			this.$el.on('widget-added widget-updated', function() { 
				_this.widgetsHandler.apply(_this, arguments);
			});

			// Initialize the Lazyload interval
			this.on('app:rendered', function() {
				setTimeout(this.lazyload, 0);
				setInterval(this.lazyload, 1000);
			});
		},

		/*
		 * Populate the containers collection from resume_json
		 */
		setContainers: function() {
			this.containersCollection.set(resume_json.containers);
			this.trigger('app:rendered');
		},

		/*
		 * Renders a container. Fired when a container is added to the collection.
		 */
		renderContainer: function(model) {
			var type = model.get('type');
			var id = model.get('id');

			// Set the container view. If the view is not found, fallback to the base view
			var ContainerView = resume.containers.View[type];
			if ( _.isUndefined(ContainerView) ) {
				ContainerView = resume.containers.View; // fallback to the base view
			}

			// Initialize the view and store it in the views holder
			resume.views[id] = new ContainerView({
				el: '.container-' + id,
				model: model
			});

			// Render the container
			resume.views[id].render();
		},

		/*
		 * Handles the initialization/update of a Resume widgets.
		 * Hooked to the "widget-added" and "widget-updated" WordPress events.
		 */
		widgetsHandler: function(event, widget) {
			var widgetID = $(widget).attr('id')
			var containerID = widgetID.replace(/widget-\d+_/, '');
			var containerData = $(widget).find('.container-' + containerID).data('json');

			if (!containerData) {
				return;
			}

			var containerJSON = $.parseJSON(resume.urldecode(containerData));

			if (event.type === 'widget-updated') {
				var containerView = resume.views[containerID];
				var containerModel = containerView.model;

				// Completely unbind the old widget view
				containerView.undelegateEvents();
				containerView.$el.removeData().unbind(); 

				// Remove the old widget model from the collection
				this.containersCollection.remove(containerModel);

				// Remove the view from the DOM
				containerView.remove();
			}

			// Add the new/updated model to the collection, this will also render the widget
			this.containersCollection.add(containerJSON);
		},

		/*
		 * Handles the initialization of fields that should be rendered when they are in the viewport.
		 */
		lazyload: function() {
			if (_.isEmpty(resume.lazyload)) {
				return;
			}

			for (var id in resume.lazyload) {
				var view = resume.lazyload[id];

				if (!view.rendered && resume.isElementInViewport(view.$el)) {
					view.trigger('field:rendered');
				}
			}
		}
	});

	/**
	 * resume.template( id )
	 *
	 * Fetches a template by id.
	 *
	 * @param  {string} id   A string that corresponds to a DOM element with an id prefixed with "rbf-tmpl-".
	 *                       For example, "attachment" maps to "rbf-tmpl-attachment".
	 * @return {function}    A function that lazily-compiles the template requested.
	 */
	resume.template = _.memoize(function(id) {
		var compiled;
		var options = {
			evaluate:    /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape:      /\{\{([^\}]+?)\}\}(?!\})/g
		};

		return function(data) {
			var $template = $( '#rbf-tmpl-' + id );
			var html = $template.html() || '';
			html = $.trim(html);

			if (!$template.length || !html) {
				$.error('Cannot find the Backbone template for the following element: "' + id + '"');
			}

			compiled = compiled || _.template(html, null, options);
			return compiled(data);
		};
	});

	/**
	 * resume.isElementInViewport( $el )
	 *
	 * Checks if the element is in the viewport (visible)
	 *
	 * @param  {object} $el   jQuery DOM element
	 *
	 * @return bool
	 */
	resume.isElementInViewport = function($el) {
		if (!$el.is(':visible')) {
			return false;
		}

		var rect = $el[0].getBoundingClientRect();

		return (
			rect.bottom >= 0 && 
			rect.right >= 0 && 
			rect.top <= $(window).height() && 
			rect.left <= $(window).width()
		);
	}

	/**
	 * resume.urldecode( str )
	 *
	 * A JavaScript equivalent of PHP's urldecode
	 *
	 * @param  {string} str
	 *
	 * @return {string}
	 */
	resume.urldecode = function(str) {
		return decodeURIComponent((str + '')
			.replace(/%(?![\da-f]{2})/gi, function() {
				// PHP tolerates poorly formed escape sequences
				return '%25';
			})
			.replace(/\+/g, '%20'));
	}

	/**
	 * Handles sidebar requests
	 *
	 * @param  {string} name
	 * @param  {string} action
	 *
	 * @return {promise}
	 */
	resume.sidebarManager = function(name, action, reload) {
		var request = $.ajax({
			url: ajaxurl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'resume_' + action + '_sidebar',
				name: name
			}
		});

		request.done(function( response ) {
			if ( !response || !response.success ) {
				alert( response.error || 'An error occurred while trying to ' + action + ' the sidebar.' );
			}
		});

		request.fail(function( jqXHR, textStatus ) {
			alert( 'Request failed: ' + textStatus );
		});

		if (reload) {
			request.always(function() {
				window.location.reload();
			});
		}

		return request;
	}

	/**
	 * Initializes the main view
	 */
	resume.init = function() {
		if ( _.isUndefined(resume_json) || _.isEmpty(resume_json.containers) ) {
			return false;
		}

		// Hook up the events and prepare the containers collection
		resume.views.main = new resume.main.View();

		// Populate the containers
		resume.views.main.setContainers();
	}

	/**
	 *	Run the app when the dom is ready
	 */
	$(document).ready(function() {
		resume.init(); // Abracadabra! Poof! Containers everywhere ...
	});

}(jQuery));