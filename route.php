<?php
// Start our controllers and register routes

include plugin_dir_path( __FILE__ ) . 'includes/rest-api/class-qat-user.php';
include plugin_dir_path( __FILE__ ) . 'includes/rest-api/class-qat-ticket.php';
include plugin_dir_path( __FILE__ ) . 'includes/rest-api/class-qat-company.php';
include plugin_dir_path( __FILE__ ) . 'includes/rest-api/class-qat-project.php';

add_action( 'rest_api_init', 'qat_register_rest_routes' );
function qat_register_rest_routes() {
	$controller = new Qat_REST_Controller();
	$controller->register_routes();
}

class Qat_REST_Controller extends WP_REST_Controller {

	protected $qat_user;
	protected $qat_ticket;
	protected $qat_company;
	protected $qat_project;

	function __construct() {
		$this->namespace   = 'qat/v1';
		$this->qat_user    = new Qat_User();
		$this->qat_ticket  = new Qat_Ticket();
		$this->qat_company = new Qat_Company();
		$this->qat_project = new Qat_Project();
	}

	function register_routes() {
		/** roles DELETE AT THE END */
		register_rest_route(
			$this->namespace,
			'/roles',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this,
						'get_roles',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/** BEGIN route USERS  */
		/**Get Users */
		register_rest_route(
			$this->namespace,
			'/users',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this->qat_user,
						'get_users',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Add User*/
		register_rest_route(
			$this->namespace,
			'/user',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_user,
						'post_user',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Delete User by id*/
		register_rest_route(
			$this->namespace,
			'/user/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array(
						$this->qat_user,
						'delete_user',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Update User by id*/
		register_rest_route(
			$this->namespace,
			'/user',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array(
						$this->qat_user,
						'put_user',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Get User by id */
		register_rest_route(
			$this->namespace,
			'/user/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this->qat_user,
						'get_user',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);
		/** END route USERS  */

		/** BEGIN route TICKETS  */

		/**Get Tickets */
		register_rest_route(
			$this->namespace,
			'/tickets',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_ticket,
						'get_tickets',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Set Ticket status */
		register_rest_route(
			$this->namespace,
			'/setTicketStatus',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_ticket,
						'set_ticket_status',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Get Tickets by filter's fields */
		register_rest_route(
			$this->namespace,
			'/getTicketsByFilter',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_ticket,
						'get_tickets_by_filter',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Get Ticket by id */
		register_rest_route(
			$this->namespace,
			'/ticket/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this->qat_ticket,
						'get_ticket',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Delete Ticket by id */
		register_rest_route(
			$this->namespace,
			'/ticket/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array(
						$this->qat_ticket,
						'delete_ticket',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Update Ticket by id */
		register_rest_route(
			$this->namespace,
			'/ticket',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array(
						$this->qat_ticket,
						'put_ticket',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Add Ticket */
		register_rest_route(
			$this->namespace,
			'/ticket',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_ticket,
						'post_ticket',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);
		/** END route TICKETS  */

		/** BEGIN route COMPANY  */
		/**Get Company */
		register_rest_route(
			$this->namespace,
			'/companies',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_company,
						'get_companies',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Get Tickets by filter's fields */
		register_rest_route(
			$this->namespace,
			'/getCompaniesByFilter',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_ticket,
						'get_compaies_by_filter',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**Get Company by id */
		register_rest_route(
			$this->namespace,
			'/company/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this->qat_company,
						'get_company',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**delete Company by id */
		register_rest_route(
			$this->namespace,
			'/company/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array(
						$this->qat_company,
						'delete_company',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**update Company by id */
		register_rest_route(
			$this->namespace,
			'/company',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array(
						$this->qat_company,
						'put_company',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**add Company */
		register_rest_route(
			$this->namespace,
			'/company',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_company,
						'post_company',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/** END route COMPANY  */

		/** BEGIN route PROJECT  */

		/**get Projects */
		register_rest_route(
			$this->namespace,
			'/projects',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_project,
						'get_projects',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**get Project by id */
		register_rest_route(
			$this->namespace,
			'/project/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this->qat_project,
						'get_project',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**delete Project by id */
		register_rest_route(
			$this->namespace,
			'/project/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array(
						$this->qat_project,
						'delete_project',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**update Project by id */
		register_rest_route(
			$this->namespace,
			'/project',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array(
						$this->qat_project,
						'put_project',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/**add Project */
		register_rest_route(
			$this->namespace,
			'/project',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this->qat_project,
						'post_project',
					),
					'permission_callback' => array(
						$this,
						'get_permissions_check',
					),
				),
			)
		);

		/** END route PROJECT  */

	}

	/**check token */
	function get_permissions_check( $request ) {
		$header_token = $request->get_header( 'autorization' );

		if ( isset( $header_token ) ) {
			return $this->qat_user->check_token_user( $header_token );
		} else {
			return false;
		}
	}

	function get_roles() {
		global $wp_roles;

		return $wp_roles->roles;
	}
}
