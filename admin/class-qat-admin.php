<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/TurtleOnni
 * @since      1.0.0
 *
 * @package    Qat
 * @subpackage Qat/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Qat
 * @subpackage Qat/admin
 * @author     Turtle_Onni <turtleonni@gmail.com>
 */
class Qat_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Qat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Qat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'bootstrap_css', plugin_dir_url( __FILE__ ) . 'css/libs/bootstrap/bootstrap.css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/qat-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Qat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Qat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'tinymce', 'https://cdn.tinymce.com/4/tinymce.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'bootstrap_js', plugin_dir_url( __FILE__ ) . 'js/libs/bootstrap/bootstrap.min.js');
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/qat-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	* Add menu btn
	*/
	public function qat_add_btn_to_admin_panel() {
		 add_menu_page(
			 'Ticket Manager',
			 'Ticket Manager',
			 'manage_options',
			 'qat',
			 [ $this, 'qat_main_page' ]
		 );
		add_submenu_page(
			'qat',
			'Company List',
			'Company List',
			'manage_options',
			'qat-company-list',
			[ $this, 'qat_company_list' ]
		);
		add_submenu_page(
			'qat',
			'Project List',
			'Project List',
			'manage_options',
			'qat-project-list',
			[ $this, 'qat_project_list' ]
		);
	}

	public function qat_main_page() {
		require_once plugin_dir_path( __FILE__ ) . '../includes/views/qat-main-page.php';
	}

	public function qat_company_list() {
		require_once plugin_dir_path( __FILE__ ) . '../includes/views/qat-company-list.php';
	}

	public function qat_project_list() {
		require_once plugin_dir_path( __FILE__ ) . '../includes/views/qat-project-list.php';
	}

	public function qat_gl_variables() {
		$current_user = wp_get_current_user();
		$user_login   = '';
		$user_token   = '';

		if ( $current_user->ID ) {
			$user_login = $current_user->user_login;
			$user_token = get_user_meta( $current_user->ID, 'autorization_qat_token', true );
		}
		$variables = array (
			'qat_ajax_url' => '/wp-json/qat/v1',
			'qat_username' => $user_login,
			'qat_token'    => $user_token,
		);
		echo(
			'<script type="text/javascript">window.qat_data = ' .
			json_encode($variables) .
			';</script>'
		);
	}
}
