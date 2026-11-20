<?php
/**
 * Run with: wp eval-file tests/query-efficiency.php
 * Requires WordPress with Commonplace active. Does not write database records.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'cp_get_post_terms' ) ) {
  throw new RuntimeException( 'Load WordPress with the Commonplace theme active.' );
}

global $wpdb;

// Check aggregate counts against the previous per-year post-ID queries.
foreach ( cp_get_year_counts() as $year => $count ) {
  $ids = get_posts( array( 'posts_per_page' => -1, 'fields' => 'ids', 'year' => $year ) );
  if ( count( $ids ) !== $count ) {
    throw new RuntimeException( "Incorrect count for {$year}." );
  }
}

$posts = get_posts( array( 'posts_per_page' => -1 ) );
foreach ( $posts as $post ) {
  foreach ( array( array( 'post_tag' ), array( 'post_tag', 'category', 'post_format' ) ) as $taxonomies ) {
    $previous = get_terms( array( 'taxonomy' => $taxonomies, 'object_ids' => $post->ID ) );
    $current  = cp_get_post_terms( $post->ID, $taxonomies );
    $old_ids  = wp_list_pluck( $previous, 'term_id' );
    $new_ids  = wp_list_pluck( $current, 'term_id' );
    sort( $old_ids );
    sort( $new_ids );
    if ( $old_ids !== $new_ids ) {
      throw new RuntimeException( "Term membership changed for {$post->ID}." );
    }
  }
}

// Exercise ordering, empty terms, errors, and translated post-format labels
// without creating fixtures in the database. The queried post must exist.
$post_id = $posts ? $posts[0]->ID : 0;
if ( $post_id ) {
  $fixtures = array(
    'post_tag' => array(
      (object) array( 'term_id' => 4, 'taxonomy' => 'post_tag', 'name' => 'zebra', 'slug' => 'zebra' ),
      (object) array( 'term_id' => 2, 'taxonomy' => 'post_tag', 'name' => 'Apple', 'slug' => 'apple' ),
      (object) array( 'term_id' => 1, 'taxonomy' => 'post_tag', 'name' => 'apple', 'slug' => 'apple-2' ),
      (object) array( 'term_id' => 5, 'taxonomy' => 'post_tag', 'name' => 'Éclair', 'slug' => 'eclair' ),
    ),
    'post_format' => array(
      (object) array( 'term_id' => 3, 'taxonomy' => 'post_format', 'name' => 'Image translated', 'slug' => 'post-format-image' ),
    ),
    'category' => false,
  );
  $filter = static function ( $terms, $id, $taxonomy ) use ( $post_id, &$fixtures ) {
    return $id === $post_id ? ( $fixtures[ $taxonomy ] ?? false ) : $terms;
  };
  add_filter( 'get_the_terms', $filter, 10, 3 );
  try {
    // Prime relationship caches before checking that the helper adds no queries.
    foreach ( array_keys( $fixtures ) as $taxonomy ) {
      get_the_terms( $post_id, $taxonomy );
    }
    $before = $wpdb->num_queries;
    $terms  = cp_get_post_terms( $post_id, array_keys( $fixtures ) );
    if ( array( 1, 2, 5, 3, 4 ) !== wp_list_pluck( $terms, 'term_id' ) ) {
      throw new RuntimeException( 'Incorrect alphabetical, tie, or post-format ordering.' );
    }
    if ( $wpdb->num_queries !== $before ) {
      throw new RuntimeException( 'Cached term rendering issued a query.' );
    }
    $fixtures['post_tag'] = new WP_Error( 'test_error' );
    if ( array() !== cp_get_post_terms( $post_id, array( 'post_tag', 'category' ) ) ) {
      throw new RuntimeException( 'Empty terms or errors were not handled.' );
    }
  } finally {
    remove_filter( 'get_the_terms', $filter, 10 );
  }
} else {
  echo "SKIP: ordering fixtures require at least one published post.\n";
}
echo 'PASS: year counts, term membership for ' . count( $posts ) . " posts, ordering, and cached query checks.\n";
