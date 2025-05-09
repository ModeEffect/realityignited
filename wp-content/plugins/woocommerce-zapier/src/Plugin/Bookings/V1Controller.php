<?php

namespace OM4\WooCommerceZapier\Plugin\Bookings;

use OM4\WooCommerceZapier\API\API;
use OM4\WooCommerceZapier\Logger;
use OM4\WooCommerceZapier\TaskHistory\Task\TaskDataStore;
use WC_Booking;
use WC_Bookings_REST_Booking_Controller;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * Exposes WooCommerce Bookings' REST API v1 Bookings endpoint via the Zapier Integration endpoint namespace.
 *
 * @since 2.2.0
 */
class V1Controller extends WC_Bookings_REST_Booking_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = API::REST_NAMESPACE;

	/**
	 * Resource Type (used for Task History items).
	 *
	 * @var string
	 */
	protected $resource_type = 'booking';

	/**
	 * Prepare a single booking output for response.
	 *
	 * @param WC_Booking      $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $object, $request ) {
		$response = parent::prepare_object_for_response( $object, $request );
		if ( is_a( $response, 'WP_REST_Response' ) ) {
			$payload = $response->get_data();
			// Change date fields to the desired format.
			$payload['date_created']  = gmdate( 'Y-m-d\TH:i:s', $payload['date_created'] );
			$payload['date_modified'] = gmdate( 'Y-m-d\TH:i:s', $payload['date_modified'] );
			$payload['date_start']    = gmdate( 'Y-m-d\TH:i:s', $payload['start'] );
			$payload['date_end']      = gmdate( 'Y-m-d\TH:i:s', $payload['end'] );
			unset( $payload['end'] );
			unset( $payload['start'] );
			$response->set_data( $payload );
		}
		return $response;
	}
}
