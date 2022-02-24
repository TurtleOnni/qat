<?php

require_once( ABSPATH . 'wp-admin/includes/user.php' );

/** User */
class Qat_User {

	public function __construct() {

	}

	/**
	 * Get user by id
	 *
	 * @param WP_Rest_Request data
	 *
	 * @return WP_REST_Response
	 */
	public function get_user( $data ) {
		$user_id   = $data->get_param( 'id' );
		$user_data = get_userdata( $user_id );

		if ( $user_data === get_userdata( $user_id ) ) {  // TODO
			$user_meta_data  = get_user_meta( $user_id  );
			$user_data->meta = $user_meta_data;
			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $user_data,
				),
				200
			);
		} else { //TODO WP ERROR
			return new WP_REST_Response(
				array(
					'status' => 'failed',
					'data'   => 'No user was found.',
				),
				400
			);
		}
	}

	public function get_users( $request ) {
		$args       = array();
		$meta_query = $request->get_param( 'meta' );
//TODO $meta?
		foreach ( $meta_query as $meta ) {
			array_push(
				$args
			);
		}
		// TODO
		$args = array(
			'fields'     => 'all_with_meta',
			'meta_query' =>	array(
				array(
					'relation' => 'AND',
					array(
						'key' => 'phone',
					),
					array(
						'key' => 'whatsapp',
					),
					array(
						'key' => 'additional_emails',
					),
					array(
						'key'     => 'company',
						'value'   => $request->get_param('company'),
						'compare' => '=',
					),
				),
			)
		);

		return get_users( $args );
	}

	/**
	 * Add new user
	 *
	 * @param WP_Rest_Request data
	 *
	 * @return WP_Error | WP_REST_Response
	 */

	public function post_user( $data ) {
		$random_password = wp_generate_password( 12 );
		$user_id         = wp_create_user(
			$data->get_param( 'user_login' ),
			$random_password,
			$data->get_param( 'user_email' )
		);
		if ( is_wp_error( $user_id ) ) {
			return new WP_Error(
				'create_error',
				esc_html__( 'Create user error' )
			);
		} else {
			$user             = new WP_User( $user_id );
			$user->set_role( $data->get_param( 'role' ) );
			$user_meta_update = array();
			$display_name     = $data->get_param( 'display_name' );

			if ( isset( $display_name ) ) {
				$user_meta_update['display_name'] = $display_name;
			}
			$user_url = $data->get_param( 'user_url' );

			if ( isset( $user_url ) ) {
				$user_meta_update['user_url'] = $user_url;
			}
			$first_name = $data->get_param( 'first_name' );

			if ( isset( $first_name ) ) {
				wp_update_user(
					array(
						'ID'         => $user_id,
						'first_name' => $first_name
					)
				);
			}
			$last_name = $data->get_param( 'last_name' );

			if ( isset( $last_name ) ) {
				wp_update_user(
					array(
						'ID'        => $user_id,
						'last_name' => $last_name
					)
				);
			}

			foreach ( $user_meta_update as $meta ) {
				update_user_meta( $user_id, $meta['key'], $meta['value'] );
			}
			$user_meta_array = $data->get_param( 'meta' );
			$this->create_token_user( $user_id );

			foreach ( $user_meta_array as $meta ) {
				add_user_meta(
					$user_id,
					$meta['key'],
					$meta['value']
				);
			}

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $user_id,
				),
				200
			);
		}
	}

	/**
	 * Update user
	 *
	 * @param WP_Rest_Request
	 *
	 * @return WP_REST_Response
	 */
	public function put_user( $data ) {
		$user_id = $data->get_param( 'id' );

		if ( ! empty( $data->get_param( 'user_login' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'         => absint( $user_id ),
					'user_login' => $data->get_param( 'user_login' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'user_pass' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'        => absint( $user_id ),
					'user_pass' => $data->get_param( 'user_pass' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'user_nicename' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'            => absint( $user_id ),
					'user_nicename' => $data->get_param( 'user_nicename' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'user_email' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'         => absint( $user_id ),
					'user_email' => $data->get_param( 'user_email' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'user_url' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'       => absint( $user_id ),
					'user_url' => $data->get_param( 'user_url' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'display_name' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'           => absint( $user_id ),
					'display_name' => $data->get_param( 'display_name' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'last_name' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'        => absint( $user_id ),
					'last_name' => $data->get_param( 'last_name' ),
				)
			);
		}

		if ( ! empty( $data->get_param( 'first_name' ) ) ) {
			$user_id = wp_update_user(
				array(
					'ID'         => absint( $user_id ),
					'first_name' => $data->get_param( 'first_name' ),
				)
			);
		}
		$user_meta_array = $data->get_param( 'meta' );

		foreach ( $user_meta_array as $meta ) {
			update_user_meta(
				absint( $user_id ),
				$meta['key'],
				$meta['value']
			);
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
			),
			200
		);
	}


	/**
	 * Delete user
	 *
	 * @param WP_Rest_Request id
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_user( $params ) {

		if ( wp_delete_user( $params->get_param( 'id' ) ) ) {

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $params->get_param( 'id' ),
				),
				200
			);
		} else {

			return new WP_Error(
				'delete_error',
				esc_html__( 'Delete user error' )
			);
		}
	}

	/**
	 * Generate qat_token user
	 *
	 * @param string user_id
	 *
	 * @return boolean
	 */
	public function create_token_user( $user_id ) {
		$token = random_bytes( 28 );
		add_user_meta(
			$user_id,
			'autorization_qat_token',
			bin2hex( $token )
		);
		return true;
	}

	/**
	 * Check qat_token user
	 *
	 * @param string token
	 *
	 * @return bool|WP_Error
	 */
	public function check_token_user( $token ) {
		$token_query = array(
			'meta_key'   => 'autorization_qat_token',
			'meta_value' => $token,
			'number'     => 1,
			'fields'     => 'ID',
		);

		$user_token  = get_users( $token_query );

		if ( isset( $user_token[0] ) ) {
			return true;
		} else {

			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You cannot view the post resource.' )
			);
		}
	}

	/*
	 * FUNCTIONS
	 */

	public function get_user_by_id( $id ) {
		return get_userdata( absint( $id ) );
	}
}

