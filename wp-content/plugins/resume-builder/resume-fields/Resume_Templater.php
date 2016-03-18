<?php

add_action('rbf_field_activated', 'resume_add_templates');
add_action('rbf_container_activated', 'resume_add_templates');
if (!function_exists('resume_add_templates')) {
	/**
	 * Adds the field/container template(s) to the templates stack.
	 *
	 * @param object $object field or container object
	 * @return void
	 **/
	function resume_add_templates($object) {
		$templates = $object->get_templates();

		if (!$templates) {
			return false;
		}

		foreach ($templates as $name => $callback) {
			ob_start();

			call_user_func($callback);

			$html = ob_get_clean();

			// Add the template to the stack
			Resume_Templater::add_template($name, $html);
		}
	}
}

/**
 * Handles the underscore templates rendering
 *
 **/
class Resume_Templater {
	static protected $templates = array();

	function __construct() {
		add_action('admin_footer', array($this, 'render_templates'), 999);
	}

	static function add_template($name, $html) {
		// Check if the template is already added
		if (isset(self::$templates[$name])) {
			return false;
		}

		// Add the template to the stack
		self::$templates[$name] = $html;
	}

	function render_templates() {
		foreach (self::$templates as $name => $html) {
			?>
			<script type="text/html" id="rbf-tmpl-<?php echo $name; ?>">
				<?php echo apply_filters('resume_template', apply_filters('resume_template_' . $name, $html), $name); ?>
			</script>
			<?php
		}
	}
}

$resume_templater = new Resume_Templater();