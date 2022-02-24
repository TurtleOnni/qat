<?php

include_once ('class-qat-company.php');
include_once ('class-qat-user.php');
/** Project */
class Qat_Project {

	public function __construct() {

	}

	/**
	 * Get project by id
	 * @param $data
	 *
	 * @return WP_REST_Response
	 */
	public function get_project( $data ) {
		$project_id = $data->get_param( 'id' );
		$projects   = array();

		if ( isset( $project_id ) ) {
			$projects = get_term_by( 'id', $project_id, 'project' );
		} else {
			$project_slug = $data->get_param( 'slug' );

			if ( isset( $project_slug ) ) {
				$projects = get_term_by( 'slug', $project_slug, 'project' );
			} else {
				$project_slug = $data->get_param( 'name' );

				if ( isset( $project_slug ) ) {
					$projects = get_term_by( 'name', $project_slug, 'project' );
				}
			}
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $projects,
			),
			200
		);
	}

	/**
	 * Get projects by slug/name/id
	 * @param $data
	 *
	 * @return WP_REST_Response
	 */
	public function get_projects( $data ) {
		global $wpdb;
		$user           = get_user_by('login', $data->get_param( 'user_login' ) );
		$company_args   = array(
			'post_type'   => 'company',
			'post_status' => 'publish',
			'post_author' => $user->ID,
		);
		$companies      = get_posts( $company_args );
		$query          = 'SELECT DISTINCT t.term_id, t.name, t.slug FROM `' . $wpdb->terms . '` AS t INNER JOIN `' . $wpdb->termmeta . '` AS tm WHERE t.term_id = tm.term_id AND tm.meta_key = "connected_company" AND ';

		if ( count( $companies ) === 1 ) {
			$query .= 'tm.meta_value LIKE "%' . $companies[0]->ID .'%"';
		} else {
			$query .= '( ';
			foreach ( $companies as $company ) {
				//var_dump($query);
				if ( end( $companies ) === $company ) {
					$query .= 'tm.meta_value LIKE "%' . $company->ID .'%" ';
				} else {
					$query .= 'tm.meta_value LIKE "%' . $company->ID .'%" OR ';
				}
			}
			$query .= ')';
		}
		$projects = $wpdb->get_results( $query );

		if ( count( $projects ) !== 0 ) {

			foreach ( $projects as $project ) {
				$project->meta       = get_term_meta( absint( $project->term_id ) );
				$connected_companies = unserialize( $project->meta[ 'connected_company' ][ 0 ] );

				if ( count( $connected_companies ) !== 0 ) {
					$qat_company              = new Qat_Company();
					$connected_companies_info = array();

					foreach ( $connected_companies as $company_id ) {
						$connected_company = $qat_company->get_company_by_id( $company_id );
						array_push( $connected_companies_info, $connected_company );
					}
					$project->meta['connected_company'] = $connected_companies_info;
				} else {
					$project->meta['connected_company'] = array();
				}

				$connected_users = unserialize( $project->meta[ 'connected_user' ][ 0 ] );
				if ( count( $connected_users ) !== 0 ) {
					$qat_user              = new Qat_User();
					$connected_users_info = array();

					foreach ( $connected_users as $user_id ) {
						$connected_user = $qat_user->get_user_by_id( $user_id );

						if ( $connected_user ) {
							array_push( $connected_users_info, $connected_user );
						}
					}
					$project->meta['connected_user'] = $connected_users_info;
				} else {
					$project->meta['connected_user'] = array();
				}
			}
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $projects,
			),
			200
		);
	}

	/**
	 * Add project
	 * @param $project
	 *
	 * @return WP_REST_Response
	 */
	public function post_project( $project ) {
		$project_term = wp_insert_term(
			$project->get_param( 'name' ),
			'project',
			array(
				'description' => $project->get_param( 'description' ),
				'parent'      => 0,
				'slug'        => $project->get_param( 'slug' ),
			)
		);
		$project_meta_array = $project->get_param( 'meta' );

		foreach ( $project_meta_array as $meta ) {
			$id = add_term_meta(
				$project_term['term_id'],
				$meta['key'],
				$meta['value'],
				true
			);
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $project_term[ 'term_id' ],
			),
			200
		);
	}

	public function put_project( $project ) {

	}

	/**
	 * Delete project by id
	 * @param WP_REST_Response $params
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_project( $params ) {
		$project_id    = $params->get_param( 'id' );
		$delete_status = wp_delete_term(
			$project_id,
			'project'
		);

		if ( $delete_status ) {
			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $project_id,
				),
				200
			);
		} else {

			return new WP_Error(
				404,
				esc_html__( 'Delete project error' ),
				'Delete error'
			);
		}
	}

	/*
	 * FUNCTIONS
	 */

	//add here filter by company TODO
	public function get_project_list() {

		return get_terms(
			array(
				'taxonomy'   => 'project',
				'hide_empty' => false,
			)
		);
	}
}
