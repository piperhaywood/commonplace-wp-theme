<?php
/**
 * Run with wp eval-file tests/related-posts-efficiency.php.
 * Read-only comparison with the former correlated ranking query.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'cp_get_related_posts' ) ) {
  throw new RuntimeException( 'Load WordPress with Commonplace active.' );
}

global $wpdb;
$posts   = get_posts( array( 'numberposts' => -1, 'post_type' => 'post' ) );
$checked = 0;
foreach ( $posts as $source ) {
  foreach ( array( array( 'post_tag' ), array( 'post_tag', 'category', 'post_format' ) ) as $taxonomies ) {
    $terms = array();
    foreach ( $taxonomies as $taxonomy ) {
      $post_terms = get_the_terms( $source, $taxonomy );
      if ( is_array( $post_terms ) ) {
        $terms = array_merge( $terms, wp_list_pluck( $post_terms, 'term_taxonomy_id' ) );
      }
    }
    $expected = array();
    if ( $terms ) {
      $term_ids = implode( ',', array_map( 'absint', $terms ) );
      $score    = "(SELECT COUNT(*) FROM {$wpdb->term_relationships} r WHERE r.object_id = p.ID AND r.term_taxonomy_id IN ($term_ids))";
      $expected = $wpdb->get_col(
        $wpdb->prepare(
          "SELECT p.ID FROM {$wpdb->posts} p WHERE p.post_type = 'post' AND p.post_status = 'publish' AND p.ID <> %d AND $score > 0 ORDER BY $score DESC, p.post_date DESC, p.ID DESC LIMIT 5",
          $source->ID
        )
      );
    }

    // Source relationships are already cached above. A ranking request must not
    // ask WP_Term_Query to retrieve those relationships again, even from its cache.
    $term_requests = 0;
    $watch_terms   = static function ( $results, $requested_taxonomies, $args ) use ( &$term_requests, $source ) {
      if ( in_array( $source->ID, (array) ( $args['object_ids'] ?? array() ), true ) ) {
        ++$term_requests;
      }
      return $results;
    };
    add_filter( 'get_terms', $watch_terms, 10, 3 );
    try {
      $actual = cp_get_related_posts( $source->ID, $taxonomies );
    } finally {
      remove_filter( 'get_terms', $watch_terms, 10 );
    }
    if ( array_map( 'intval', $expected ) !== array_map( 'intval', wp_list_pluck( $actual, 'ID' ) ) ) {
      throw new RuntimeException( "Ranking changed for {$source->ID}." );
    }
    if ( $term_requests ) {
      throw new RuntimeException( "Cached source relationships queried again for {$source->ID}." );
    }
    ++$checked;
  }
}
echo "PASS: {$checked} ranking comparisons and cached source-term lookups.\n";
