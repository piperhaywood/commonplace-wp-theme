<?php
/**
 * Read-only archive/header regression checks.
 *
 * Run with wp eval-file tests/archive-context.php in a local WordPress install.
 * Does not change posts, users, or saved widgets; refreshes disposable caches.
 */

$_SERVER['SERVER_NAME'] = 'wordpress-personal-site.local';

$check = static function ( $condition, $message ) {
  if ( ! $condition ) {
    throw new RuntimeException( $message );
  }
};

$render_header = static function () {
  ob_start();
  include get_template_directory() . '/header.php';
  return ob_get_clean();
};

global $wp_query;
$original_query = $wp_query;

try {
  $wp_query = new WP_Query();
  $wp_query->set_404();
  $html = $render_header();
  $check( 1 === substr_count( $html, '<h1 ' ), '404 needs exactly one H1.' );
  $check( false === strpos( $html, 'class="breadcrumb"' ), '404 still uses breadcrumb.' );

  $wp_query = new WP_Query( array( 's' => 'cp-unmatched-regression-archive-query' ) );
  $html = $render_header();
  $check( false !== strpos( $html, 'Search results for' ), 'Search heading missing.' );
  $check( 1 === substr_count( $html, '<h1 ' ), 'Search needs exactly one H1.' );

  $tags = get_terms( array( 'taxonomy' => 'post_tag', 'number' => 1, 'orderby' => 'count', 'order' => 'DESC' ) );
  $check( ! is_wp_error( $tags ) && ! empty( $tags ), 'Need a published tag for related-tag checks.' );
  $tag = $tags[0];
  $wp_query = new WP_Query( array( 'tag_id' => $tag->term_id ) );
  $wp_query->set( 'paged', 2 );
  $html = $render_header();
  $check( false !== strpos( $html, 'Page 2' ), 'Archive page number missing.' );
  $check( 1 === substr_count( $html, '<h1 ' ), 'Term archive needs exactly one H1.' );

  delete_transient( 'cp_related_tags_13_' . $tag->term_taxonomy_id );
  $aggregations = 0;
  $track_query = static function ( $sql ) use ( &$aggregations ) {
    if ( false !== strpos( $sql, 'SELECT related.term_id' ) ) {
      ++$aggregations;
    }
    return $sql;
  };
  add_filter( 'query', $track_query );
  $terms = cp_related_archive_terms();
  $check( 1 === $aggregations, 'First lookup should aggregate once.' );
  cp_related_archive_terms();
  $check( 1 === $aggregations, 'Cached lookup should not aggregate again.' );
  do_action( 'clean_term_cache', array( $tag->term_id ), 'post_tag', true );
  cp_related_archive_terms();
  $check( 2 === $aggregations, 'Term changes must invalidate related tags.' );
  do_action( 'clean_post_cache', 0, null );
  cp_related_archive_terms();
  $check( 3 === $aggregations, 'Post changes must invalidate related tags.' );
  remove_filter( 'query', $track_query );
  $check( count( $terms ) <= 13, 'Too many related tags.' );
  $previous = PHP_INT_MAX;
  foreach ( $terms as $term ) {
    $check( 'post_tag' === $term->taxonomy && $term->count >= 3, 'Related tag eligibility failed.' );
    $check( $term->term_id !== $tag->term_id, 'Current term must be excluded.' );
    $check( $term->count <= $previous, 'Related tags must sort by total count.' );
    $previous = $term->count;
  }

  $index = get_notebook_index( array( 'post_tag', 'category', 'post_format' ), false, 1 );
  $check( false !== strpos( $index, '<ul role="list">' ), 'Explicit index list semantics missing.' );
  $emoji_seen = false;
  preg_match_all( '/<a[^>]*>(.*?)<span class="term__count"/s', $index, $cloud_names );
  foreach ( $cloud_names[1] as $cloud_name ) {
    $name = html_entity_decode( wp_strip_all_tags( $cloud_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    $emoji = preg_match( '/^\s*(?:\p{Extended_Pictographic}|[\x{1F1E6}-\x{1F1FF}]|[#*0-9]\x{FE0F}?\x{20E3})/u', $name );
    $check( ! $emoji_seen || $emoji, 'Text term follows an emoji term in the cloud.' );
    $emoji_seen = $emoji_seen || $emoji;
  }
  $check( false === strpos( $index, '<h2' ), 'Index should not include letter headings.' );
  $check( false === strpos( get_notebook_index( array( 'post_tag' ), true, 1 ), 'termgroup' ), 'Index should remain a flat cloud with legacy years enabled.' );
  $document = new DOMDocument();
  @$document->loadHTML( '<?xml encoding="utf-8" ?>' . $index );
  foreach ( $document->getElementsByTagName( 'a' ) as $link ) {
    $path = wp_parse_url( $link->getAttribute( 'href' ), PHP_URL_PATH );
    $check( 0 !== strpos( $path, '/type/' ), 'Index must omit formats.' );
  }
  // Inject explicit fixtures without saving terms or depending on site content.
  $seed = get_terms( array( 'taxonomy' => 'post_tag', 'number' => 1 ) )[0];
  $fixtures = array();
  foreach ( array( '🐚', '&#x1f331;', '&#11088;', 'Alpha fixture', 'Zulu fixture' ) as $name ) {
    $fixture = clone $seed;
    $fixture->name = $name;
    $fixture->count = 5;
    $fixtures[] = $fixture;
  }
  $fixture_filter = static function ( $terms ) use ( &$fixtures ) { return $fixtures; };
  add_filter( 'get_terms', $fixture_filter );
  try {
    $fixture_html = get_notebook_index( array( 'post_tag' ), true, 1 );
    $check( strpos( $fixture_html, 'Alpha fixture' ) < strpos( $fixture_html, 'Zulu fixture' ), 'Text order changed.' );
    foreach ( array( '🐚', '&#x1f331;', '&#11088;' ) as $emoji_name ) {
      $check( strpos( $fixture_html, 'Zulu fixture' ) < strpos( $fixture_html, $emoji_name ), 'Literal or encoded emoji did not move to the end.' );
    }
    $check( false !== strpos( $fixture_html, 'Tag: Alpha fixture, 5 posts' ), 'Cloud taxonomy label missing.' );
    $check( '' === get_notebook_index( array( 'post_tag' ), false, 6 ), 'Count threshold should produce no empty cloud.' );
    $fixtures = array();
    $check( '' === get_notebook_index( array( 'post_tag' ), false, 1 ), 'Empty terms should produce no cloud.' );
  } finally {
    remove_filter( 'get_terms', $fixture_filter );
  }
  $pill = cp_term_pill( '<unsafe>', 3, '/notes/' );
  $check( false !== strpos( $pill, '&lt;unsafe&gt;' ), 'Pill name escaping failed.' );
  $check( false !== strpos( $pill, 'term__count' ), 'Pill count missing.' );
  $header_active = true;
  wp_register_sidebar_widget( 'cp-regression-header', 'Regression header', static function ( $args ) {
    echo $args['before_widget'];
    echo '<p>Header paragraph with <a href="/notes/">notes</a>.</p><p>Second header paragraph.</p>';
    echo $args['after_widget'];
  } );
  $header_filter = static function ( $sidebars ) use ( &$header_active ) {
    $sidebars['header-area-1'] = $header_active ? array( 'cp-regression-header' ) : array();
    return $sidebars;
  };
  add_filter( 'sidebars_widgets', $header_filter );
  $html = $render_header();
  $check( false !== strpos( $html, 'Second header paragraph.' ), 'Multiple widget paragraphs missing.' );
  $header_active = false;
  $html = $render_header();
  $check( false === strpos( $html, '<div class="header-intro' ), 'Empty widget wrapper should be omitted.' );
  $check( false !== strpos( $html, 'header--without-intro' ), 'Empty header needs top spacing.' );
  remove_filter( 'sidebars_widgets', $header_filter );
  wp_unregister_sidebar_widget( 'cp-regression-header' );
  echo "Passed archive headings, pagination, related-tag eligibility/order/cache invalidation, index semantics, and pill escaping.\n";
} finally {
  $wp_query = $original_query;
}
