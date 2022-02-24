<?php

/** Company */
class Qat_Company {

	public function __construct() {

	}

	/**
	 * Get company by id
	 *
	 * @param WP_Rest_Request data
	 *
	 * return WpUser with meta [ 'website', 'connected_users']
	 * meta returns by array { key: '', value: ''} in data
	 * exmp. data:[ { meta: [ key: '', value: '' ]}]
	 *
	 * @return WP_REST_Response
	 */

	public function get_company( $data ) {
		$company_id         = $data->get_param( 'id' );
		$company_data       = get_postdata( $company_id );
		$company_meta_array = array(
			'website',
			'connected_users',
		);
		$company_meta_data  = array();

		foreach ( $company_meta_array as $meta ) {
			$meta_field['key']   = $meta;
			$meta_field['value'] = get_post_meta(
				$company_id,
				$meta,
				false
			);
			array_push(
				$company_meta_data,
				$meta_field
			);
		}
		$company_data['meta'] = $company_meta_data;

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $company_data,
			),
			200
		);
	}
	/**
	 * Get companies by fields
	 * @param WP_REST_Request
	 *
	 * @return WP_REST_Response
	*/
	public function get_companies( $data ) {
		$user         = get_user_by('login', $data->get_param( 'user_login' ) );
		$company_args = array(
			'post_type'   => 'company',
			'post_status' => 'publish',
			'post_title'  => $data->get_param( 'post_title' ),
			'post_author' => $user->ID,
		);
		$companies    = get_posts( $company_args );

		if ( count( $companies ) !== 0 ) {

			foreach ( $companies as $company ) {
				$company->meta   = get_post_meta( $company->ID );
				$connected_users = unserialize( $company->meta[ 'connected_users' ][ 0 ] );

				if ( count( $connected_users ) !== 0 ) {
					$qat_user             = new Qat_User();
					$connected_users_info = array();

					foreach ( $connected_users as $user_id ) {
						$connected_user = $qat_user->get_user_by_id( $user_id );

						if ( $connected_user ) {
							array_push( $connected_users_info, $connected_user );
						}
					}
					$company->meta['connected_users'] = $connected_users_info;
				} else {
					$company->meta['connected_users'] = array();
				}
			}

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $companies,
				),
				200
			);
		} else {

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => 'List is empty',
				),
				200
			);
		}
	}

	/**
	 * Add Company
	 * @param $company
	 *
	 * @return WP_REST_Response | WP_Error
	 *
	 */
	public function post_company( $data ) {
		$user         = get_user_by('login', $data->get_param( 'user_login' ) );
		$company_args = array(
			'post_type'      => 'company',
			'post_title'     => $data->get_param( 'post_title' ),
			'post_name'      => $data->get_param( 'post_name' ),
			'post_author'    => $user->ID,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			);
		$company_id   = wp_insert_post( $company_args, true );

		if ( $company_id !== 0 && !is_wp_error( $company_id ) ) {
			$company_meta_array = $data->get_param( 'meta' );

			foreach ( $company_meta_array as $meta ) {
				add_post_meta( $company_id, $meta['key'], $meta['value'] );
			}

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $company_id,
				),
				200
			);
		} else {
			return new WP_Error(
				404,
				esc_html__( 'Failed add company' ),
				'Add company error'

			);
		}
	}

	/**
	 * Update company
	 *
	 * @param WP_Rest_Request company
	 */
	public function put_company( WP_Rest_Request $company ) {
		$company_id = $company->get_param( 'id' );

		if ( ! empty( $company->get_param( 'post_title' ) ) ) {
			$company_id = wp_update_post(
				array(
					'ID'         => absint( $company_id ),
					'post_title' => $company->get_param( 'post_title' ),
				)
			);
		}

		if ( ! empty( $company->get_param( 'post_name' ) ) ) {
			$company_id = wp_update_post(
				array(
					'ID'        => absint( $company_id ),
					'post_name' => $company->get_param( 'post_name' ),
				)
			);
		}

		$company_meta_array = $company->get_param( 'meta' );

		foreach ( $company_meta_array as $meta ) {
			update_post_meta( absint( $company_id ), $meta['key'], $meta['value'] );
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $company_id,
			),
			200
		);
	}

	/**
	 * Delete company by id
	 *
	 * @param WP_REST_Response
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_company( $params ) {
		$company_id = $params->get_param( 'id' );

		if ( wp_delete_post( $company_id ) ) {

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $company_id,
				),
				200
			);
		} else {

			return new WP_Error(
				'delete_error',
				esc_html__( 'Delete company error' )
			);
		}
	}

	/**
	 * Get companies by value of filter's fields
	 * @param WP_REST_Request data {
	 *
	 * @return WP_REST_Response
	 */
	public function get_companies_by_filter( $data ) {
		$args       = array();
		$search_val = $data->get_param( 'search_val' );
		global $wpdb;
		$query = 'SELECT * FROM `' . $wpdb->posts . '` AS p JOIN INNER ' . $wpdb->postmeta . ' AS m WHERE m.post_id = p.ID AND p.type = "company"';

		if ( !empty( $search_val ) && $search_val !== '') {
			if ( absint( $search_val ) ) {
				$query .= ' AND p.ID = ' . absint( $search_val ) . ' ';
			} else {
				$query .= ' AND p.post_title LIKE "%' . $search_val . '%" ';
			}
		}
		if ( !empty( $data->get_param( 'connected_users' ) ) &&  $data->get_param( 'connected_users' ) !== '' ) {
			$query .= 'AND m.meta_key = "connected_users" AND m.meta_value LIKE "%' . $data->get_param( 'connected_users' ) . '%"';
		}
		$companies = $wpdb->get_results( $query );

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $companies,
			),
			200
		);
	}

	/*
	 * FUNCTIONS
	 */

	public function get_company_by_id( $id ) {
		return get_post( absint ( $id ) );
	}
}

