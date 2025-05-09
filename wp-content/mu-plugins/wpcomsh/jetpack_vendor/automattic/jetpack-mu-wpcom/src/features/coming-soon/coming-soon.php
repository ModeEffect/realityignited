<?php
/**
 * Coming Soon
 *
 * @package A8C\FSE\Coming_soon
 */

namespace A8C\FSE\Coming_soon;

use Automattic\Jetpack\Jetpack_Mu_Wpcom;

require_once __DIR__ . '/../../utils.php';

/**
 * Determines whether the coming soon page should be shown.
 *
 * @return boolean
 */
function should_show_coming_soon_page() {
	if ( ! is_singular() && ! is_archive() && ! is_search() && ! is_front_page() && ! is_home() && ! is_404() ) {
		return false;
	}

	$share_code = get_share_code();
	if ( is_accessed_by_valid_share_link( $share_code ) ) {
		track_preview_link_event();
		setcookie( 'wp_share_code', $share_code, time() + 3600, '/', false, is_ssl(), true );
		return false;
	}

	$should_show = ( (int) get_option( 'wpcom_public_coming_soon' ) === 1 );

	// Everyone from Administrator to Subscriber will be able to see the site.
	// See https://wordpress.org/support/article/roles-and-capabilities/ for all roles and capabilities.
	// We can update to `edit_post` to be stricter, or open it up as an editable feature.
	if ( is_user_logged_in() && current_user_can( 'read' ) ) {
		$should_show = false;
	}

	// Allow folks to hook into this method to set their own rules.
	// We'll use to on WordPress.com to check further user privileges.
	return apply_filters( 'a8c_show_coming_soon_page', $should_show );
}

/**
 * Gets share code from URL or Cookie.
 *
 * @return string
 */
function get_share_code() {
	if ( isset( $_GET['share'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No data written to site, used in conditionally displaying coming-soon page and adding robots headers.
		return sanitize_key( wp_unslash( $_GET['share'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No data written to site, used in conditionally displaying coming-soon page and adding robots headers.
	} elseif ( isset( $_COOKIE['wp_share_code'] ) ) {
		return sanitize_key( wp_unslash( $_COOKIE['wp_share_code'] ) );
	}
	return '';
}

/**
 * Determines whether the coming soon page should be bypassed.
 *
 * It checks if share code is provided as GET parameter, or as a cookie.
 * Then it validates the code against blog option, and if sharing code is valid,
 * it allows bypassing the Coming Soon page.
 *
 * @param string $share_code Share code to check against blog option value.
 *
 * @return bool
 */
function is_accessed_by_valid_share_link( $share_code ) {
	$preview_links_option = get_option( 'wpcom_public_preview_links' );

	if ( ! is_array( $preview_links_option ) || array() === $preview_links_option || ! $share_code ) {
		return false;
	}

	if ( ! in_array( $share_code, array_column( $preview_links_option, 'code' ), true ) ) {
		return false;
	}

	return true;
}

/**
 * Add X-Robots-Tag header to prevent from indexing page that includes share code.
 *
 * @param array $headers Headers.
 * @return array Headers.
 */
function maybe_add_x_robots_headers( $headers ) {
	if ( get_share_code() ) {
		$headers['X-Robots-Tag'] = 'noindex, nofollow';
	}
	return $headers;
}
add_filter( 'wp_headers', __NAMESPACE__ . '\maybe_add_x_robots_headers' );

/**
 * Track site preview event.
 *
 * @return void
 */
function track_preview_link_event() {
	$event_name = 'wpcom_site_previewed';
	if ( function_exists( 'wpcomsh_record_tracks_event' ) ) {
		wpcomsh_record_tracks_event( $event_name, array() );
	} else {
		require_lib( 'tracks/client' );
		tracks_record_event( get_current_user_id(), $event_name, array() );
	}
}

/**
 * Returns the WP.com logo
 *
 * @return string
 */
function coming_soon_share_image() {
	return 'https://s1.wp.com/home.logged-out/images/wpcom-og-image.jpg';
}

/**
 * Renders a fallback coming soon page
 */
function render_fallback_coming_soon_page() {
	if ( ! defined( 'GRAVATAR_HOVERCARDS__DISABLE' ) ) {
		define( 'GRAVATAR_HOVERCARDS__DISABLE', true );
	}

	// Disable WP scripts, likes, social og meta, cookie banner.
	remove_action( 'wp_enqueue_scripts', 'wpcom_actionbar_enqueue_scripts', 101 );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'header_js', 5 );
	remove_action( 'wp_head', 'global_css', 5 );
	remove_action( 'wp_footer', 'stats_footer', 101 );
	add_filter( 'infinite_scroll_archive_supported', '__return_false', 99 ); // Disable infinite scroll feature.
	add_filter( 'jetpack_disable_eu_cookie_law_widget', '__return_true', 1 );
	add_filter( 'wpcom_disable_logged_out_follow', '__return_true', 10, 1 ); // Disable follow actionbar.
	add_filter( 'wpl_is_enabled_sitewide', '__return_false', 10, 1 ); // Disable likes.
	add_filter( 'woocommerce_demo_store', '__return_false' ); // Prevent the the wocommerce demo store notice from displaying.
	add_filter( 'jetpack_open_graph_image_default', __NAMESPACE__ . '\coming_soon_share_image' ); // Set the default OG image.
	add_filter( 'jetpack_twitter_cards_image_default', __NAMESPACE__ . '\coming_soon_share_image' ); // Set the default Twitter Card image.
	wp_enqueue_style( 'recoleta-font', '//s1.wp.com/i/fonts/recoleta/css/400.min.css', array(), Jetpack_Mu_Wpcom::PACKAGE_VERSION );

	include __DIR__ . '/fallback-coming-soon-page.php';
}

/**
 * This filter makes sure it's possible to fetch `wpcom_public_coming_soon` option via public-api.wordpress.com.
 *
 * @param object $options array Retrieved site options, ready to be returned as API respomnse.
 *
 * @return array current value of `wpcom_public_coming_soon`
 */
function add_public_coming_soon_to_settings_endpoint_get( $options ) {
	$wpcom_public_coming_soon            = (int) get_option( 'wpcom_public_coming_soon' );
	$options['wpcom_public_coming_soon'] = $wpcom_public_coming_soon;

	return $options;
}
add_filter( 'site_settings_endpoint_get', __NAMESPACE__ . '\add_public_coming_soon_to_settings_endpoint_get' );

/**
 * This filter makes sure it's possible to change `wpcom_public_coming_soon` option via public-api.wordpress.com.
 *
 * @param object $input Filtered POST input.
 * @param object $unfiltered_input Raw and unfiltered POST input.
 *
 * @return mixed
 */
function add_public_coming_soon_to_settings_endpoint_post( $input, $unfiltered_input ) {
	if ( is_array( $unfiltered_input ) && array_key_exists( 'wpcom_public_coming_soon', $unfiltered_input ) ) {
		$input['wpcom_public_coming_soon'] = (int) $unfiltered_input['wpcom_public_coming_soon'];

		// Avoid updating the value of the `wpcom_public_coming_soon` if the request wants to change the option.
		remove_action( 'update_option_blog_public', __NAMESPACE__ . '\disable_coming_soon_on_privacy_change', 10 );
	}
	return $input;
}
add_filter( 'rest_api_update_site_settings', __NAMESPACE__ . '\add_public_coming_soon_to_settings_endpoint_post', 10, 2 );

/**
 * Launch the site when the privacy mode changes from public-not-indexed
 * This can happen due to clicking the launch button from the banner
 * Or due to manually updating the setting in wp-admin or calypso settings page.
 *
 * @param  string $old_value the old value of blog_public.
 * @param  string $value     the new value of blog_public.
 * @return bool              whether an update occurred.
 */
function disable_coming_soon_on_privacy_change( $old_value, $value ) {
	if ( 0 !== (int) $old_value || 0 === (int) $value ) {
		// Do nothing if not moving from public-not-indexed.
		return false;
	}
	update_option( 'wpcom_public_coming_soon', 0 );
	return true;
}
add_action( 'update_option_blog_public', __NAMESPACE__ . '\disable_coming_soon_on_privacy_change', 10, 2 );

/**
 * Adds the `wpcom_public_coming_soon` option to new sites  if requested by the client.
 *
 * @param int    $blog_id    Blog ID.
 * @param int    $user_id    User ID.
 * @param string $domain     Site domain.
 * @param string $path       Site path.
 * @param int    $network_id Network ID. Only relevant on multi-network installations.
 * @param array  $meta       Meta data. Used to set initial site options.
 * @return bool              whether an update occurred.
 */
function add_option_to_new_site( $blog_id, $user_id, $domain, $path, $network_id, $meta ) {
	if ( isset( $meta['public'] )
		&& 0 === $meta['public']
		&& isset( $meta['options']['wpcom_public_coming_soon'] )
		&& 1 === (int) $meta['options']['wpcom_public_coming_soon']
	) {
		if ( function_exists( 'add_blog_option' ) ) {
			add_blog_option( $blog_id, 'wpcom_public_coming_soon', 1 );
		}
		return true;
	}
	return false;
}

add_action( 'wpmu_new_blog', __NAMESPACE__ . '\add_option_to_new_site', 10, 6 );

/**
 * Decides whether to render to the site's coming soon page and performs
 * the render.
 *
 * @param string $template The template to render.
 */
function coming_soon_page( $template ) {
	if ( ! should_show_coming_soon_page() ) {
		return $template;
	}

	render_fallback_coming_soon_page();
	die( 0 );
}
add_filter( 'template_include', __NAMESPACE__ . '\coming_soon_page' );
