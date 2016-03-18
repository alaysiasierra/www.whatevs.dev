<?php
/*
Plugin Name: Resume Fields
Description: Provides additional custom fields for posts, categories, users, widgets and more
Version: 0.4.2
Requires at least: 3.9
Tested up to: 3.9.1
*/

define('RESUME_PLUGIN_ROOT', dirname(__FILE__));
define('RESUME_PLUGIN_URL', plugin_dir_url(__FILE__));

do_action('resume_before_include');

include_once 'Resume_Exception.php';

include_once 'Resume_DataStore.php';
include_once 'Resume_Templater.php';
include_once 'Resume_Sidebar_Manager.php';

include_once 'Resume_Container.php';
include_once 'Resume_Container_CustomFields.php';
include_once 'Resume_Container_ThemeOptions.php';
include_once 'Resume_Container_TermMeta.php';
include_once 'Resume_Container_UserMeta.php';
include_once 'Resume_Container_Widget.php';

include_once 'Resume_Field.php';
include_once 'Resume_Field_Complex.php';

include_once 'Resume_Widget.php';

include_once 'resume-functions.php';

do_action('resume_after_include');

# Add Actions
add_action('wp_loaded', 'resume_trigger_fields_register');
add_action('resume_after_register_fields', 'resume_init_containers');
add_action('admin_footer', 'resume_init_scripts', 0);
add_action('admin_print_footer_scripts', 'resume_json', 999);

/**
 * A safer alternative of $_REQUEST - only for $_GET and $_POST
 * @param  string $key the name of the requested parameter
 * @return the requested parameter value
 */
function rbf_request_param($key = '') {
	$value = false;
	if (!$key) {
		return $value;
	}
 
	if ( isset($_POST[$key]) ) {
		$value = $_POST[$key];
	} elseif ( isset($_GET[$key]) ) {
		$value = $_GET[$key];
	}
 
	return $value;
}