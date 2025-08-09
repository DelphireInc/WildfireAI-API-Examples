<?php
/*
Plugin Name: WildfireAI Image Proxy
Description: Exposes /wildfireai-image?device_id=<device_id>&cam={a|b} to fetch and serve the latest image from WildfireAI.
Version:     1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WFAI_API_KEY',  '<api_key>' );

/**
 * Add rewrite rule for our endpoint.
 */
function wfai_add_rewrite_rule() {
    add_rewrite_rule(
        '^wildfireai-image/?$',
        'index.php?wfai_image=1',
        'top'
    );
}
add_action( 'init', 'wfai_add_rewrite_rule' );

/**
 * Allow our custom query var.
 */
function wfai_query_vars( $vars ) {
    $vars[] = 'wfai_image';
    return $vars;
}
add_filter( 'query_vars', 'wfai_query_vars' );

/**
 * Flush rewrite rules on activation/deactivation.
 */
function wfai_flush_rewrite() {
    wfai_add_rewrite_rule();
    flush_rewrite_rules();
}
register_activation_hook(   __FILE__, 'wfai_flush_rewrite' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Intercept requests to our endpoint and proxy the image.
 */
function wfai_template_redirect() {
    global $wp_query;

    if ( empty( $wp_query->query_vars['wfai_image'] ) ) {
        return;
    }

    // Validate and sanitize device_id parameter.
    $device_id = isset( $_GET['device_id'] )
         ? sanitize_text_field( $_GET['device_id'] )
         : '';

    if ( empty( $device_id ) ) {
        status_header( 400 );
        wp_die( 'Missing "device_id" parameter' );
    }

    // Validate and sanitize cam parameter.
    $cam = isset( $_GET['cam'] )
         ? strtolower( sanitize_text_field( $_GET['cam'] ) )
         : '';

    if ( $cam !== 'a' && $cam !== 'b' ) {
        status_header( 400 );
        wp_die( 'Invalid "cam" parameter: use ?cam=a or ?cam=b' );
    }

    $sensor = $cam === 'a' ? 'camera_a' : 'camera_b';

    // Build JSON endpoint URL.
    $json_url = sprintf(
        'https://wildfireai.com/devices/%s/sensors/%s/image?apikey=%s',
        $device_id,
        $sensor,
        WFAI_API_KEY
    );

    $resp = wp_remote_get( $json_url );
    if ( is_wp_error( $resp ) ) {
        status_header( 502 );
        wp_die( 'Error fetching data from WildfireAI.' );
    }

    $data = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( empty( $data['data'][0]['id'] ) ) {
        status_header( 502 );
        wp_die( 'Unexpected response structure from WildfireAI.' );
    }

    // Stream the actual image.
    $image_id  = rawurlencode( $data['data'][0]['id'] );
    $image_url = sprintf(
        'https://wildfireai.com/images/%s?apikey=%s',
        $image_id,
        WFAI_API_KEY
    );

    $img_resp = wp_remote_get( $image_url );
    if ( is_wp_error( $img_resp ) ) {
        status_header( 502 );
        wp_die( 'Error fetching image.' );
    }

    $content_type = wp_remote_retrieve_header( $img_resp, 'content-type' ) ?: 'application/octet-stream';
    header( 'Content-Type: ' . $content_type );
    echo wp_remote_retrieve_body( $img_resp );
    exit;
}
add_action( 'template_redirect', 'wfai_template_redirect' );
