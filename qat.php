<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/TurtleOnni
 * @since             1.0.0
 * @package           Qat
 *
 * @wordpress-plugin
 * Plugin Name:       Tickets manager
 * Plugin URI:        https://
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Turtle_Onni
 * Author URI:        https://github.com/TurtleOnni
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       qat
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TICKETS_M_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-qat-activator.php
 */
function activate_qat() {

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-qat-deactivator.php
 */
function deactivate_qat() {
	/*remove roles*/
	remove_role( 'owner' );
	remove_role( 'project_manager' );
	remove_role( 'agent' );
}

/**
 * The code that runs before activation plugin.
 * This action is documented in includes/class-qat-variables.php
 */

/**
 * The code that runs before activation plugin.
 * This action is documented in includes/class-qat-variables.php
 */
function remove_variables_qat() {

}

function qat_add_taxonomy() {
	if ( ! taxonomy_exists( 'project' ) ) {
		$tax_args = array(
			'description'   => 'Project',
			'public'        => true,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'label'         => __( 'Project' ),
			'labels'                => [
				'name'              => 'Projects',
				'singular_name'     => 'Project',
				'search_items'      => 'Search Projects',
				'all_items'         => 'All Projects',
				'view_item '        => 'View Project',
				'parent_item'       => 'Parent Project',
				'parent_item_colon' => 'Parent Project:',
				'edit_item'         => 'Edit Project',
				'update_item'       => 'Update Project',
				'add_new_item'      => 'Add New Project',
				'new_item_name'     => 'New Project Name',
				'menu_name'         => 'Project',
			],
			'rewrite'               => array( 'slug' => 'project' ),
			'hierarchical'          => false,
			'query_var'             => true,
		);
		register_taxonomy(
			'project',
			array( 'ticket' ),
			$tax_args
		);
	}
}

function qat_add_types() {

	if ( ! post_type_exists( 'ticket' ) ) {
		register_post_type( 'ticket', array(
			'label'              => 'Ticket',
			'labels'             => array(
				'name'          => 'Tickets',
				'singular_name' => 'Ticket',
			),
			'description'        => '',
			'public'             => true,
			'publicly_queryable' => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'query_var'          => true,
			'supports'           => array( 'title', 'editor' ),
		) );
	}

	if ( ! post_type_exists( 'company' ) ) {
		register_post_type('company', array(
			'label'               => 'Companies',
			'labels'              => array(
				'name'          => 'Company',
				'singular_name' => 'Company',
			),
			'description'         => '',
			'public'              => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'query_var'           => true,
			'supports'            => array( 'title', 'editor' ),
			'taxonomies'          => array( 'project' ),
		) );
	}
}

function qat_add_roles() {
	add_role(
		'owner',
		'Owner',
		array(
			'read'      => true,
			'level_0'   => true,
		)
	);
	add_role(
		'project_manager',
		'Project Manager',
		array(
			'read'      => true,
			'level_0'   => true,
		)
	);
	add_role(
		'agent',
		'Agent',
		array(
			'read'      => true,
			'level_0'   => true,
		)
	);

}

add_action( 'init', 'qat_add_taxonomy' );
add_action( 'init', 'qat_add_types' );
add_action( 'init', 'qat_add_roles' );
//register_activation_hook( __FILE__, 'create_variables_qat' );
register_activation_hook( __FILE__, 'activate_qat' );

register_deactivation_hook( __FILE__, 'deactivate_qat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-qat.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_qat() {

	$plugin = new Qat();
	$plugin->run();

}

run_qat();
