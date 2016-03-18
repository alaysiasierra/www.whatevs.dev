<?php

if ( !class_exists('Resume_Sidebar_Manager') ) :

/**
 * This class is responsible for handling custom sidebars.
 */
class Resume_Sidebar_Manager {

	/**
	 * Singleton implementation.
	 *
	 * @return Resume_Sidebar_Manager
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication.
		static $instance;
		if ( ! is_a( $instance, 'Resume_Sidebar_Manager' ) ) {
			$instance = new Resume_Sidebar_Manager;
			$instance->setup();
		}
		return $instance;
	}

	/**
	 * Register actions, filters, etc...
	 */
	private function setup() {
		// Register the custom sidebars
		add_action( 'widgets_init', array( $this, 'register_sidebars' ), 100 );

		// Enqueue the UI scripts on the widgets page
		add_action( 'sidebar_admin_setup', array( $this, 'enqueue_scripts' ) );

		// Set the default options
		if ( function_exists( 'rbf_get_default_sidebar_options' ) ) {
			add_filter( 'resume_custom_sidebar_default_options', 'rbf_get_default_sidebar_options', -1 );
		}

		// Ajax listeners
		add_action( 'wp_ajax_resume_add_sidebar', array( $this, 'action_handler' ) );
		add_action( 'wp_ajax_resume_remove_sidebar', array( $this, 'action_handler' ) );
	}

	/**
	 * Handle action requests.
	 *
	 * @return array|void Output JSON if DOING_AJAX, otherwise return an array
	 */
	public function action_handler() {
		$response = array(
			'success' => false,
			'error' => null,
		);

		if ( empty( $_POST['action'] ) || empty( $_POST['name'] ) ) {
			return false;
		}

		$action = $_POST['action'];
		$name = $_POST['name'];
		$result = false;

		switch ($action) {
			case 'resume_add_sidebar':
				$result = $this->add_sidebar( $name );
			break;

			case 'resume_remove_sidebar':
				$result = $this->remove_sidebar( $name );
			break;
		}

		if ( is_wp_error( $result ) ) {
			$response['error'] = $result->get_error_message();
		} else {
			$response['success'] = (bool) $result;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json( $response );
		} else {
			return $response;
		}
	}

	/**
	 * Add a new custom sidebar.
	 *
	 * @see Resume_Sidebar_Manager::register_sidebars()
	 * @param string $id Sidebar ID
	 * @param string $name Sidebar Name
	 * @return bool|WP_Error
	 */
	public function add_sidebar( $name, $id = '' ) {
		$registered_sidebars = $this->get_sidebars();
		$id = $id ? $id : $name;

		// Sanitize the sidebar ID the same way as dynamic_sidebar()
		$id = sanitize_title( $id );

		if ( isset( $registered_sidebars[$id] ) ) {
			return new WP_Error( 'sidebar-exists', __( 'Sidebar with the same ID is already registered.', 'rbf' ) );
		}

		$registered_sidebars[$id] = array(
			'id' => $id,
			'name' => $name,
		);

		return update_option( 'resume_custom_sidebars', $registered_sidebars );
	}

	/**
	 * Remove a custom sidebar by ID.
	 *
	 * @see Resume_Sidebar_Manager::register_sidebars()
	 * @param string $id Sidebar ID
	 * @return bool|WP_Error
	 */
	public function remove_sidebar( $id ) {
		$registered_sidebars = $this->get_sidebars();

		// Sanitize the sidebar ID the same way as dynamic_sidebar()
		$id = sanitize_title( $id );

		if ( isset( $registered_sidebars[$id] ) ) {
			unset( $registered_sidebars[$id] );
		} else {
			return new WP_Error( 'sidebar-not-found', __( 'Sidebar not found.', 'rbf' ) );
		}

		return update_option( 'resume_custom_sidebars', $registered_sidebars );
	}

	/**
	 * Get all the registered custom sidebars.
	 *
	 * @return string
	 */
	public function get_sidebars() {
		return apply_filters( 'resume_custom_sidebars', get_option( 'resume_custom_sidebars', array() ) );
	}

	/**
	 * Register the custom sidebars.
	 */
	public function register_sidebars() {
		$registered_sidebars = $this->get_sidebars();
		$default_options = apply_filters( 'resume_custom_sidebar_default_options', array() );

		foreach ( $registered_sidebars as $id => $options ) {
			$options['class'] = 'resume-sidebar';
			$options = wp_parse_args( $options, $default_options );
			$options = apply_filters( 'resume_custom_sidebar_options', $options, $id );

			register_sidebar( $options );
		}
	}

	/**
	 * Enqueue the UI scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'resume-sidebar-ui', RESUME_PLUGIN_URL . '/js/sidebar-ui.js', array('resume-app') );
	}

}

/**
 * The main function responsible for returning the Resume_Sidebar_Manager instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @example $resume_sidebar_manager = resume_sidebar_manager()
 *
 * @return Resume_Sidebar_Manager instance
 */
function resume_sidebar_manager() {
	return Resume_Sidebar_Manager::instance();
}

// Initialization
resume_sidebar_manager();

endif;
