<?php
/**
 * Run with wp eval-file tests/related-posts.php in a development database.
 * Temporary posts and terms are deleted even when an assertion fails.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'cp_get_related_posts' ) ) {
  throw new RuntimeException( 'Load WordPress with Commonplace active.' );
}

$original_user_id = get_current_user_id();
$posts = array();
$terms = array();
$assert = static function ( $condition, $message ) {
  if ( ! $condition ) {
    throw new RuntimeException( $message );
  }
};
$make_post = static function ( $status, $tags, $date ) use ( &$posts ) {
  $id = wp_insert_post(
    array(
      'post_title'  => 'Commonplace related-post regression fixture',
      'post_status' => $status,
      'post_date'   => $date,
    ),
    true
  );
  if ( is_wp_error( $id ) ) {
    throw new RuntimeException( $id->get_error_message() );
  }
  $posts[] = $id;
  wp_set_post_terms( $id, $tags, 'post_tag' );
  return $id;
};

try {
  foreach ( array( 'a', 'b' ) as $suffix ) {
    $term = wp_insert_term( wp_unique_id( 'cp-related-' ) . uniqid() . $suffix, 'post_tag' );
    $assert( ! is_wp_error( $term ), 'Could not create fixture tag.' );
    $terms[] = $term['term_id'];
  }
  $source = $make_post( 'publish', $terms, '2020-01-01 12:00:00' );
  $best   = $make_post( 'publish', $terms, '2020-01-02 12:00:00' );
  $older  = $make_post( 'publish', array( $terms[0] ), '2020-01-03 12:00:00' );
  $newer  = $make_post( 'publish', array( $terms[0] ), '2020-01-04 12:00:00' );
  $make_post( 'draft', $terms, '2020-01-05 12:00:00' );
  $make_post( 'private', $terms, '2020-01-06 12:00:00' );
  $make_post( 'future', $terms, '2099-01-01 12:00:00' );
  $untagged = $make_post( 'publish', array(), '2020-01-07 12:00:00' );

  $ids = static function ( $results ) {
    return array_map( 'intval', wp_list_pluck( $results, 'ID' ) );
  };
  $assert( array( $best, $newer, $older ) === $ids( cp_get_related_posts( $source ) ), 'Ranking, dates, self exclusion, or visibility failed.' );
  wp_set_current_user( 0 );
  $user          = wp_get_current_user();
  $user->ID      = PHP_INT_MAX;
  $user->allcaps = array( 'read' => true, 'read_private_posts' => true, 'edit_posts' => true );
  $assert( array( $best, $newer, $older ) === $ids( cp_get_related_posts( $source ) ), 'Privileged viewers must only see published results.' );
  wp_set_current_user( $original_user_id );
  $assert( array( $best ) === $ids( cp_get_related_posts( $source, 'post_tag', 1 ) ), 'Result limit failed.' );
  $assert( array( $best, $newer, $older ) === $ids( cp_get_related_posts( $source, 'post_tag', -1 ) ), 'Unlimited results failed.' );
  $assert( array() === cp_get_related_posts( $untagged ), 'Untagged source returned results.' );
  $assert( array() === cp_get_related_posts( 0 ), 'Invalid source returned results.' );
  $assert( array() === cp_get_related_posts( $source, 'missing_taxonomy' ), 'Invalid taxonomy returned results.' );
  $html = cp_related_posts_shortcode( array( 'post_id' => $source, 'taxonomies' => 'post_tag', 'title' => '<script>bad</script>' ) );
  $assert( str_contains( $html, 'class="post-index"' ) && ! str_contains( $html, '<script>' ), 'Markup or title escaping failed.' );
  $assert( '' === cp_related_posts_shortcode( array( 'post_id' => $untagged, 'taxonomies' => 'post_tag' ) ), 'Empty shortcode must produce no markup.' );
  $assert( array( $source ) === $ids( get_posts( array( 'post__in' => array( $source ) ) ) ), 'Ranking filter leaked to another query.' );
  echo "PASS: ranking, visibility, limits, empty results, shortcode escaping, and query isolation.\n";
} finally {
  wp_set_current_user( $original_user_id );
  foreach ( $posts as $id ) {
    wp_delete_post( $id, true );
  }
  foreach ( $terms as $id ) {
    wp_delete_term( $id, 'post_tag' );
  }
}
