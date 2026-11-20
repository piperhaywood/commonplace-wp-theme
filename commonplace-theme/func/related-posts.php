<?php
/**
 * Related posts ranked by shared taxonomy terms, then publication date.
 *
 * @package Commonplace
 */

/**
 * Find published related posts without changing the main query.
 *
 * @param int          $post_id Source post ID.
 * @param string|array $taxonomies Public taxonomies to match.
 * @param int          $limit Result count, or -1 for all results.
 * @return WP_Post[] Related posts.
 */
function cp_get_related_posts( $post_id, $taxonomies = 'post_tag', $limit = 5 ) {
  $post_id = absint( $post_id );
  if ( ! $post_id || ! get_post( $post_id ) ) {
    return array();
  }

  $taxonomies = is_array( $taxonomies ) ? $taxonomies : explode( ',', $taxonomies );
  $taxonomies = array_intersect( array_map( 'trim', $taxonomies ), get_taxonomies( array( 'public' => true ) ) );
  if ( ! $taxonomies ) {
    return array();
  }

  $term_ids = array();
  foreach ( array_unique( $taxonomies ) as $taxonomy ) {
    $terms = get_the_terms( $post_id, $taxonomy );
    if ( is_wp_error( $terms ) ) {
      return array();
    }
    if ( $terms ) {
      $term_ids = array_merge( $term_ids, wp_list_pluck( $terms, 'term_taxonomy_id' ) );
    }
  }
  if ( ! $term_ids ) {
    return array();
  }

  // Use cached relationships; term order has no bearing on the shared-term score.
  $term_ids = implode( ',', array_unique( array_map( 'absint', $term_ids ) ) );
  $query    = new WP_Query();
  $rank     = static function ( $clauses, $current_query ) use ( $query, $term_ids ) {
    if ( $current_query !== $query ) {
      return $clauses;
    }
    global $wpdb;
    // Aggregate only matching relationships, once, instead of counting per post.
    $clauses['join']   .= " INNER JOIN (SELECT object_id, COUNT(*) AS score FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ($term_ids) GROUP BY object_id) AS cp_related_matches ON cp_related_matches.object_id = {$wpdb->posts}.ID";
    $clauses['orderby'] = "cp_related_matches.score DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
    return $clauses;
  };

  add_filter( 'posts_clauses', $rank, 10, 2 );
  try {
    return $query->query(
      array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'post__not_in'        => array( $post_id ),
        'posts_per_page'      => -1 === (int) $limit ? -1 : max( 1, absint( $limit ) ),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
      )
    );
  } finally {
    remove_filter( 'posts_clauses', $rank, 10 );
  }
}

/**
 * Render the theme list, retaining the historical shortcode's wrappers.
 *
 * @param array $atts Shortcode attributes.
 * @return string Related post markup, or an empty string.
 */
function cp_related_posts_shortcode( $atts = array() ) {
  $atts = shortcode_atts(
    array(
      'post_id'          => get_the_ID(),
      'taxonomies'       => implode( ',', get_taxonomies( array( 'public' => true ) ) ),
      'posts_per_page'   => 5,
      'title'            => __( 'Related Posts', 'commonplace' ),
      'before_shortcode' => '<div class="rpbt_shortcode">',
      'after_shortcode'  => '</div>',
      'before_title'     => '<h3>',
      'after_title'      => '</h3>',
    ),
    $atts,
    'related_posts_by_tax'
  );

  $posts = cp_get_related_posts( $atts['post_id'], $atts['taxonomies'], $atts['posts_per_page'] );
  if ( ! $posts ) {
    return '';
  }

  $title = $atts['title'] ? wp_kses_post( $atts['before_title'] ) . esc_html( $atts['title'] ) . wp_kses_post( $atts['after_title'] ) : '';
  return trim(
    wp_kses_post( $atts['before_shortcode'] ) . "\n" .
    $title . "\n" .
    trim( cp_get_list_arr( $posts ) ) . "\n" .
    wp_kses_post( $atts['after_shortcode'] )
  );
}
add_shortcode( 'related_posts_by_tax', 'cp_related_posts_shortcode' );

/**
 * Display tag-related posts in the footer of single posts.
 */
function cp_related_posts() {
  if ( is_singular( 'post' ) && is_main_query() ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer escapes the title and wrappers; theme list escapes post values.
    echo cp_related_posts_shortcode(
      array(
        'post_id'    => get_queried_object_id(),
        'taxonomies' => 'post_tag',
        'title'      => __( 'See also', 'commonplace' ),
      )
    );
  }
}
add_action( 'wp_footer', 'cp_related_posts', 2 );
