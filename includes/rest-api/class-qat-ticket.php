<?php

/** Ticket */
class Qat_Ticket {

	public function __construct() {

	}

	/**
	 * Get ticket by id
	 *
	 * @param WP_Rest_Request data
	 *
	 * @return WP_REST_Response | WP_Error
	 */
	public function get_ticket( $data ) {
		global $wpdb;
		$ticket_id   = $data->get_param( 'id' );
		$ticket_data = get_post( $ticket_id , 'ARRAY_A' );

		if ( $ticket_data !== null ) {
			$ticket_meta           = $wpdb->get_results( 'SELECT `meta_key`,`meta_value` FROM ' . $wpdb->postmeta . ' WHERE `post_id` = ' . $ticket_id );
			$ticket_data['meta'] = $ticket_meta;

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $ticket_data,
				),
				200
			);
		} else {

			return new WP_Error(
				404,
				esc_html__( 'Failed get ticket by id' ),
				'Get ticket by id error'
			);
		}
	}
	/**
	 * Get tickets
	 * @param WP_REST_Request data {
	 *
	 * @return WP_REST_Response
	 */
	public function get_tickets( $data ) {
		global $wpdb;
		$user    = get_user_by('login', $data->get_param( 'user_login' ) );
		$query   = 'SELECT * FROM `' . $wpdb->posts . '` WHERE `post_type`="ticket" AND `post_status`="publish" AND `post_author`=' . $user->ID;
		$tickets = $wpdb->get_results( $query );

		foreach ( $tickets as $ticket ) {
			$ticket->meta = get_post_meta( $ticket->ID );
			$args         = array(
				'post_id' => $ticket->ID,
				'orderby' => array( 'comment_date' ),
				'order'   => 'DESC',
				'number'  => 1
			);
			$comments     = get_comments( $args );

			if ( count( $comments ) === 0 ) {
				$ticket->last_update        = $ticket->post_date;
				$ticket->new_comments_count = 0;
			} else {
				$ticket->last_update        = $comments[0]->comment_date;
				$query                      = 'SELECT COUNT(*) FROM ' . $wpdb->comments . ' WHERE comment_approved = 0 AND comment_post_ID = ' . $ticket->ID;
				$new_comments_count         = $wpdb->get_var( $query );
				$ticket->new_comments_count = $new_comments_count;
			}
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $tickets,
			),
			200
		);
	}

	/**
	 * Get tickets by value of filter's fields
	 * @param WP_REST_Request data {
	 *
	 * @return WP_REST_Response
	 */
	public function get_tickets_by_filter( $data ) {
		$args       = array();
		$search_val = $data->get_param( 'search_val' );

		if ( ! empty( $data->get_param( 'type' ) ) && $data->get_param( 'type' ) !== '') {
			array_push(
				$args,
				array(
					'key'     => 'type',
					'value'   => $data->get_param( 'type' ),
					'compare' => '=',
				)
			);
		}

		if ( ! empty( $data->get_param( 'status' ) ) && $data->get_param( 'status' ) !== '' ) {
			array_push(
				$args,
				array(
					'key'     => 'status',
					'value'   => $data->get_param( 'status' ),
					'compare' => '=',
				)
			);
		}

		if ( count( $args ) > 1 ) {
			$args['relation'] = 'AND';
		}
		$user           = get_user_by( 'login', $data->get_param( 'user_login' ) );
		$tickets        = array();
		$tickets_result = get_posts(
			array(
				'post_type'   => 'ticket',
				'post_status' => 'publish',
				'post_author' => $user->ID,
				'meta_query'  => $args
			)
		);

		if ( !empty( $search_val ) && $search_val !== '') {

			foreach ( $tickets_result as $ticket ) {

				if ( absint( $search_val ) && $ticket->ID === absint( $search_val ) ) {
					array_push( $tickets, $ticket );
				} else {
					$search_val_str = $ticket->post_title;

					if ( stristr( $search_val_str, $search_val ) ) {
						array_push( $tickets, $ticket );
					}
				}
			}
		} else {
			$tickets = $tickets_result;
		}

		foreach ( $tickets as $ticket ) {
			$ticket->meta = get_post_meta( $ticket->ID );
			$args         = array(
				'post_id' => $ticket->ID,
				'orderby' => array('comment_date'),
				'order'   => 'DESC',
				'number'  => 1
			);
			$comments     = get_comments( $args );

			if ( count( $comments ) === 0 ) {
				$ticket->last_update        = $ticket->post_date;
				$ticket->new_comments_count = 0;
			} else {
				global $wpdb;
				$ticket->last_update        = $comments[0]->comment_date;
				$query                      = 'SELECT COUNT(*) FROM ' . $wpdb->comments . ' WHERE comment_approved = 0 AND comment_post_ID = ' . $ticket->ID;
				$new_comments_count         = $wpdb->get_var( $query );
				$ticket->new_comments_count = $new_comments_count;
			}
		}

		return new WP_REST_Response(
			array(
				'status' => 'success',
				'data'   => $tickets,
			),
			200
		);
	}

	/**
	 * Update Ticket status
	 *
	 * @param WP_Rest_Request
	 *
	 * @return WP_Error | WP_REST_Response
	 */
	public function set_ticket_status( $data ) {
		$result = wp_update_post(
			array(
				'ID'          => absint( $data->get_param( 'ID' ) ),
				'post_status' => $data->get_param( 'post_status' ),
			)
		);

		if ( $result !== 0 && ! is_wp_error( $result ) ) {

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $result,
				),
				200
			);
		} else {

			return new WP_Error(
				404,
				esc_html__( 'Update status ticket error' ),
				'Update status ticket error'
			);
		}
	}

	/**
	 * Add Ticket
	 *
	 * @param WP_Rest_Request ticket
	 *
	 * @return WP_Error | WP_REST_Response
	 */
	public function post_ticket( $data ) {
		$user      = get_user_by( 'login', $data->get_param( 'user_login' ) );
		$ticket_id = wp_insert_post(
			array(
				'post_author'    => $user->ID,
				'post_type'      => 'ticket',
				'post_title'     => $data->get_param( 'post_title' ),
				'post_content'   => $data->get_param( 'post_content' ),
				'post_status'    => 'publish',
				'comment_status' => 'open',
			)
		);

		if ( $ticket_id !== 0 && ! is_wp_error( $ticket_id ) ){
			$ticket_meta_array = $data->get_param( 'meta' );
			//open ticket
			add_post_meta(
				$ticket_id,
				'status',
				'open'
			);

			foreach ( $ticket_meta_array as $meta ) {
				add_post_meta(
					$ticket_id,
					$meta['key'],
					$meta['value']
				);
			}

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $ticket_id,
				),
				200
			);
		} else {

			return new WP_Error(
				404,
				esc_html__( 'Failed add ticket' ),
				'Add ticket error'
			);
		}
	}

	/**
	 * Update ticket
	 *
	 * @param WP_Rest_Request ticket
	 *
	 * @return WP_REST_Response
	 */
	public function put_ticket( WP_Rest_Request $ticket ) {
		$success = $ticket->get_param( 'id' );

		if ( ! empty( $ticket->get_param( 'post_title' ) ) ) {
			$success = wp_update_post(
				array(
					'ID'         => absint( $ticket->get_param( 'id' ) ),
					'post_title' => $ticket->get_param( 'post_title' ),
				)
			);
		}

		if ( ! empty( $ticket->get_param( 'post_content' ) ) && $success !== 0 && ! is_wp_error( $success ) ) {
			$success = wp_update_post(
				array(
					'ID'           => absint( $ticket->get_param( 'id' ) ),
					'post_content' => $ticket->get_param( 'post_content' ),
				)
			);
		}

		if ( ! empty( $ticket->get_param( 'post_author' ) ) && $success !== 0 && ! is_wp_error( $success ) ) {
			$success = wp_update_post(
				array(
					'ID'          => absint( $ticket->get_param( 'id' ) ),
					'post_author' => $ticket->get_param( 'post_author' ),
				)
			);
		}

		if ( $success !== 0 && ! is_wp_error( $success ) ) {
			$ticket_meta_array = $ticket->get_param( 'meta' );

			foreach ( $ticket_meta_array as $meta ) {
				update_post_meta(
					absint( $ticket->get_param( 'id' ) ),
					$meta['key'],
					$meta['value']
				);
			}

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $success,
				),
				200
			);
		} else {

			return new WP_Error(
				404,
				esc_html__( 'Update ticket error' ),
				'Update error'
			);
		}
	}

	/**
	 * Delete ticket by id
	 *
	 * @param WP_REST_Request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_ticket( $params ) {
		$ticket_id = $params->get_param( 'id' );

		if ( wp_delete_post( $ticket_id ) ) {

			return new WP_REST_Response(
				array(
					'status' => 'success',
					'data'   => $ticket_id,
				),
				200
			);
		} else {

			return new WP_Error(
				'Delete error',
				esc_html__( 'Delete ticket error' )
			);
		}
	}

	/** FUNCTIONS CLASS TICKET **/

	/**
	 * Get Ticket types
	 *
	 * @return array
	 */
	public function get_ticket_types() {
		global $wpdb;
		return $wpdb->get_results( ' SELECT DISTINCT `meta_value` FROM `' . $wpdb->postmeta . "` WHERE `meta_key` = 'type'", ARRAY_A );
	}

	/**
	 * Get Ticket types
	 *
	 * @return array
	 */
	public function get_ticket_status_list() {
		global $wpdb;
		return $wpdb->get_results( ' SELECT DISTINCT `meta_value` FROM `' . $wpdb->postmeta . "` WHERE `meta_key` = 'status'", ARRAY_A );
	}

	/**
	 * Get Ticket urgency
	 *
	 * @return array
	 */
	public function get_ticket_urgency_list() {
		global $wpdb;
		return $wpdb->get_results( ' SELECT DISTINCT `meta_value` FROM `' . $wpdb->postmeta . "` WHERE `meta_key` = 'urgency'", ARRAY_A );
	}
}
