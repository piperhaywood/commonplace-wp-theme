<?php
/**
 * Run with: wp eval-file tests/related-posts-rest.php
 * Requires WordPress with this theme active. Does not write users or posts.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'km_rpbt_plugin_supports' ) ) {
  throw new RuntimeException( 'Load WordPress with the Commonplace theme active.' );
}

// Even an upstream opt-in must not restore the bundled endpoint.
add_filter( 'related_posts_by_taxonomy_wp_rest_api', '__return_true' );

foreach ( array( 'anonymous', 'subscriber' ) as $role ) {
  wp_set_current_user( 0 );
  if ( 'subscriber' === $role ) {
    // An in-memory user avoids creating or modifying a database account.
    $user          = wp_get_current_user();
    $user->ID      = PHP_INT_MAX;
    $user->roles   = array( 'subscriber' );
    $user->allcaps = array( 'read' => true );
  }

  // Reinitialize routes so registration executes for each authentication state.
  $GLOBALS['wp_rest_server'] = null;
  $server                   = rest_get_server();
  foreach ( array_keys( $server->get_routes() ) as $route ) {
    if ( str_starts_with( $route, '/related-posts-by-taxonomy/' ) ) {
      throw new RuntimeException( "Related-posts route exposed to {$role}." );
    }
  }
  $response = $server->dispatch( new WP_REST_Request( 'GET', '/related-posts-by-taxonomy/v1/posts/1' ) );
  if ( 404 !== $response->get_status() || 'rest_no_route' !== $response->get_data()['code'] ) {
    throw new RuntimeException( "Expected rest_no_route for {$role}." );
  }
  echo "PASS: no related-posts REST route for {$role}, even with API opt-in.\n";
}

wp_set_current_user( 0 );
remove_filter( 'related_posts_by_taxonomy_wp_rest_api', '__return_true' );
if ( ! shortcode_exists( 'related_posts_by_tax' ) || ! class_exists( 'Related_Posts_By_Taxonomy' ) ) {
  throw new RuntimeException( 'Related-posts shortcode or widget is unavailable.' );
}
echo "PASS: related-posts shortcode and widget remain registered.\n";
