<?php

if ( !function_exists('resume_trigger_fields_register') ) :

function resume_trigger_fields_register() {
	try {
		do_action('resume_register_fields');
		do_action('resume_after_register_fields');	
	} catch (Resume_Exception $e) {
		$callback = '';
		foreach ($e->getTrace() as $trace) {
			$callback .= '<br/>' . (isset($trace['file']) ? $trace['file'] . ':' . $trace['line'] : $trace['function'] . '()');
		}
		wp_die( '<h3>' . $e->getMessage() . '</h3><small>' . $callback . '</small>' );
	}
}

endif;


if ( !function_exists('resume_init_containers') ) :

function resume_init_containers() {
	Resume_Container::init_containers();
}

endif;


if ( !function_exists('resume_init_scripts') ) :

function resume_init_scripts() {
	wp_enqueue_script('resume-app', RESUME_PLUGIN_URL . '/js/app.js', array('jquery', 'backbone', 'underscore', 'jquery-touch-punch', 'jquery-ui-sortable'));
	wp_enqueue_script('resume-ext', RESUME_PLUGIN_URL . '/js/ext.js', array('resume-app'));

	$active_fields = Resume_Container::get_active_fields();
	$active_field_types = array();

	foreach ($active_fields as $field) {
		if (in_array($field->type, $active_field_types)) {
			continue;
		}

		$active_field_types[] = $field->type;

		$field->admin_enqueue_scripts();
	}
}

endif;

if ( !function_exists('resume_json') ) :

function resume_json() {
	$json_data = resume_get_json_data();

	if (wp_script_is('resume-app', 'enqueued')) {
		$json_options = WP_DEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null;
		$json_data_encoded = function_exists('wp_json_encode') ? wp_json_encode($json_data, $json_options) : json_encode($json_data, $json_options);
		?>
		<script>
			/* <![CDATA[ */
			var resume_json = <?php echo $json_data_encoded; ?>;
			/* ]]> */
		</script>
		<?php
	}
}

endif;

if ( !function_exists('resume_get_json_data') ) :

function resume_get_json_data() {
	global $wp_registered_sidebars;

	$resume_data = array(
		'containers' => array(),
		'sidebars' => array(),
	);

	$containers = Resume_Container::get_active_containers();

	foreach ($containers as $container) {
		$container_data = $container->to_json(true);

		$resume_data['containers'][] = $container_data;
	}

	foreach ($wp_registered_sidebars as $sidebar) {
		// Check if we have inactive sidebars
		if (isset($sidebar['class']) && strpos($sidebar['class'], 'inactive-sidebar') !== false) {
			continue;
		}

		$resume_data['sidebars'][] = array(
			'name' => $sidebar['name'],
		);
	}

	return $resume_data;
}

endif;


if ( !function_exists('resume_get_post_meta') ) :

function resume_get_post_meta($id, $name, $type = null) {
	$name = $name[0] == '_' ? $name : '_' . $name;

	switch ($type) {
		case 'complex':
			$value = resume_get_complex_fields('CustomField', $name, $id);
		break;

		case 'map':
		case 'map_with_address':
			$value =  array(
				'lat' => (float) get_post_meta($id, $name . '-lat', true),
				'lng' => (float) get_post_meta($id, $name . '-lng', true),
				'address' => get_post_meta($id, $name . '-address', true),
				'zoom' => (int) get_post_meta($id, $name . '-zoom', true),
			);
			
			if ( !array_filter($value) ) {
				$value = array();
			}
		break;

		case 'association':
			$raw_value = get_post_meta($id, $name, true);
			$value = resume_parse_relationship_field($raw_value, $type);
		break;

		default:
			$value = get_post_meta($id, $name, true);

			// backward compatibility for the old Relationship field
			$value = rbf_maybe_old_relationship_field($value);
	}

	return $value;
}

endif;


if ( !function_exists('resume_get_the_post_meta') ) :

function resume_get_the_post_meta($name, $type = null) {
	return resume_get_post_meta(get_the_ID(), $name, $type);
}

endif;


if ( !function_exists('resume_get_theme_option') ) :

function resume_get_theme_option($name, $type = null) {
	switch ($type) {
		case 'complex':
			$value = resume_get_complex_fields('ThemeOptions', $name);
		break;

		case 'map':
		case 'map_with_address':
			$value =  array(
				'lat' => (float) get_option($name . '-lat'),
				'lng' => (float) get_option($name . '-lng'),
				'address' => get_option($name . '-address'),
				'zoom' => (int) get_option($name . '-zoom'),
			);

			if ( !array_filter($value) ) {
				$value = array();
			}
		break;

		case 'association':
			$raw_value = get_option($name);
			$value = resume_parse_relationship_field($raw_value, $type);
		break;

		default:
			$value = get_option($name);

			// backward compatibility for the old Relationship field
			$value = rbf_maybe_old_relationship_field($value);
	}

	return $value;
}

endif;


if ( !function_exists('resume_get_term_meta') ) :

function resume_get_term_meta($id, $name, $type = null) {
	$name = $name[0] == '_' ? $name: '_' . $name;

	switch ($type) {
		case 'complex':
			$value = resume_get_complex_fields('TermMeta', $name, $id);
		break;

		case 'map':
		case 'map_with_address':
			$value =  array(
				'lat' => (float) get_metadata('term', $id, $name . '-lat', true),
				'lng' => (float) get_metadata('term', $id, $name . '-lng', true),
				'address' => get_metadata('term', $id, $name . '-address', true),
				'zoom' => (int) get_metadata('term', $id, $name . '-zoom', true),
			);

			if ( !array_filter($value) ) {
				$value = array();
			}
		break;

		case 'association':
			$raw_value = get_metadata('term', $id, $name, true);
			$value = resume_parse_relationship_field($raw_value, $type);
		break;

		default:
			$value = get_metadata('term', $id, $name, true);

			// backward compatibility for the old Relationship field
			$value = rbf_maybe_old_relationship_field($value);
	}

	return $value;
}

endif;


if ( !function_exists('resume_get_user_meta') ) :

function resume_get_user_meta($id, $name, $type = null) {
	$name = $name[0] == '_' ? $name: '_' . $name;

	switch ($type) {
		case 'complex':
			$value = resume_get_complex_fields('UserMeta', $name, $id);
		break;

		case 'map':
		case 'map_with_address':
			$value =  array(
				'lat' => (float) get_metadata('user', $id, $name . '-lat', true),
				'lng' => (float) get_metadata('user', $id, $name . '-lng', true),
				'address' => get_metadata('user', $id, $name . '-address', true),
				'zoom' => (int) get_metadata('user', $id, $name . '-zoom', true),
			);

			if ( !array_filter($value) ) {
				$value = array();
			}
		break;

		case 'association':
			$raw_value = get_metadata('user', $id, $name, true);
			$value = resume_parse_relationship_field($raw_value, $type);
		break;

		default:
			$value = get_metadata('user', $id, $name, true);

			// backward compatibility for the old Relationship field
			$value = rbf_maybe_old_relationship_field($value);
	}

	return $value;
}

endif;


if ( !function_exists('resume_get_complex_fields') ) :

function resume_get_complex_fields($type, $name, $id = null) {
	$datastore = Resume_DataStore_Base::factory($type);
	
	if ( $id ) {
		$datastore->set_id($id);
	}

	$group_rows = $datastore->load_values($name);
	$input_groups = array();

	foreach ($group_rows as $row) {
		if ( !preg_match('~^' . preg_quote($name, '~') . '(?P<group>\w*)-_?(?P<key>.*?)_(?P<index>\d+)_?(?P<sub>\w+)?(-(?P<trailing>.*))?$~', $row['field_key'], $field_name) ) {
				continue;
		}
		
		$row['field_value'] = maybe_unserialize($row['field_value']);

		// backward compatibility for Relationship field
		$row['field_value'] = resume_parse_relationship_field($row['field_value']);

		$input_groups[ $field_name['index'] ]['_type'] = $field_name['group'];
		if ( !empty($field_name['trailing']) ) {
			$input_groups = resume_expand_nested_field($input_groups, $row, $field_name);
		} else if ( !empty($field_name['sub']) ) {
			$input_groups[ $field_name['index'] ][ $field_name['key'] ][$field_name['sub'] ] = $row['field_value'];
		} else {
			$input_groups[ $field_name['index'] ][ $field_name['key'] ] = $row['field_value'];
		}
	}

	// create groups list with loaded fields
	rbf_ksort_recursive($input_groups);

	return $input_groups;
}

endif;


if ( !function_exists('resume_expand_nested_field') ) :

function resume_expand_nested_field($input_groups, $row, $field_name) {
	if ( !preg_match('~^' . preg_quote($field_name['key'], '~') . '(?P<group>\w*)-_?(?P<key>.*?)_(?P<index>\d+)_?(?P<sub>\w+)?(-(?P<trailing>.*))?$~', $field_name['key'] . '_' . $field_name['sub'] . '-' . $field_name['trailing'], $subfield_name) ) {
		return $input_groups;
	}

	$input_groups[ $field_name['index'] ][$field_name['key']][ $subfield_name['index'] ]['_type'] = $subfield_name['group'];

	if ( !empty($subfield_name['trailing']) ) {
		$input_groups[ $field_name['index'] ][$field_name['key']] = resume_expand_nested_field($input_groups[ $field_name['index'] ][$field_name['key']], $row, $subfield_name);
	} else if ( !empty($subfield_name['sub']) ) {
		$input_groups[ $field_name['index'] ][$field_name['key']][ $subfield_name['index'] ][$subfield_name['key']][$subfield_name['sub']] = $row['field_value'];
	} else {
		$input_groups[ $field_name['index'] ][$field_name['key']][ $subfield_name['index'] ][$subfield_name['key']] = $row['field_value'];
	}

	return $input_groups;
}

endif;

if ( !function_exists('resume_parse_relationship_field') ) :

function resume_parse_relationship_field($raw_value = '', $type = '') {
	if ($raw_value && is_array($raw_value)) {
		$value = array();
		foreach ($raw_value as $raw_value_item) {
			if (is_string($raw_value_item) && strpos($raw_value_item, ':') !== false) {
				$item_data = explode(':', $raw_value_item);
				$item = array(
					'id' => $item_data[2],
					'type' => $item_data[0],
				);

				if ($item_data[0] === 'post') {
					$item['post_type'] = $item_data[1];
				} elseif ($item_data[0] === 'term') {
					$item['taxonomy'] = $item_data[1];
				}

				$value[] = $item;
			} elseif ( $type === 'association' ) {
				$value[] = array(
					'id' => $raw_value_item,
					'type' => 'post',
					'post_type' => get_post_type($raw_value_item),
				);
			} else {
				$value[] = $raw_value_item;
			}
		}

		$raw_value = $value;
	}

	return $raw_value;
}

endif;

if ( !function_exists('rbf_maybe_old_relationship_field') ) :

function rbf_maybe_old_relationship_field($value) {
	if (is_array($value) && !empty($value)) {
		if (preg_match('~^\w+:\w+:\d+$~', $value[0])) {
			$new_value = array();
			foreach ($value as $value_entry) {
				$pieces = explode(':', $value_entry);
				$new_value[] = $pieces[2];
			}
			$value = $new_value;
		}
	}

	return $value;
}

endif;

if ( !function_exists('rbf_ksort_recursive') ) :

function rbf_ksort_recursive( &$array, $sort_flags = SORT_REGULAR ) {
	if (!is_array($array)) {
		return false;
	}

	ksort($array, $sort_flags);
	foreach ($array as $key => $value) {
		rbf_ksort_recursive($array[$key], $sort_flags);
	}

	return true;
}

endif;