<?php

require_once 'func/cleanup.php';
require_once 'func/customizer.php';
require_once 'func/enqueue.php';
// Gutenberg block experiments in func/gutenberg.php are not loaded.
require_once 'func/theme-support.php';
require_once 'func/widgets.php';
require_once 'func/related-posts.php';

add_shortcode( 'notebooksearch', 'get_search_form' );

function cp_is_plugin_active( $plugin ) {
  return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
}

function cp_excerpt_as_title() {
  return 10;
}

add_filter( 'excerpt_more', 'cp_excerpt_more' );
function cp_excerpt_more() {
  return '&hellip;';
}

add_filter( 'document_title_parts', 'filter_title_part' );
function filter_title_part( $title ) {
  if ( ! is_feed() && '' === $title['title'] ) {
    global $post;
    add_filter( 'excerpt_length', 'cp_excerpt_as_title' );
    $excerpt = get_the_excerpt();
    remove_filter( 'excerpt_length', 'cp_excerpt_as_title' );
    $title['title'] = $excerpt;
  }
  return $title;
}

add_shortcode( 'notebookindex', 'notebook_index' );
function notebook_index( $attr ) {
  $a          = shortcode_atts(
    array(
      'taxonomy' => 'post_tag, category',
      'years'    => 'false',
      'count'    => '1',
    ),
    $attr
  );
  $taxonomy   = explode( ',', str_replace( ', ', ',', $a['taxonomy'] ) );
  $show_years = ( 'true' === $a['years'] );
  $count      = intval( $a['count'] );
  return get_notebook_index( $taxonomy, $show_years, $count );
}

add_shortcode( 'notebooklist', 'notebook_list' );
function notebook_list() {
  static $instance = 0;
  ++$instance;
  $pages = get_query_var( 'cp_list_page', array() );
  $pages = is_array( $pages ) ? $pages : array();
  $page  = isset( $pages[ $instance ] ) && is_scalar( $pages[ $instance ] ) ? max( 1, absint( $pages[ $instance ] ) ) : 1;
  $posts = new WP_Query(
    array(
      'post_type'           => 'post',
      'posts_per_page'      => 50,
      'paged'               => $page,
      'ignore_sticky_posts' => true,
    )
  );
  $next  = '';
  if ( $page < $posts->max_num_pages ) {
    $pages[ $instance ] = $page + 1;
    $next               = add_query_arg( 'cp_list_page', $pages, get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ), false ) );
  }
  ob_start();
  ?>
  <div data-post-browser="<?php echo esc_attr( 'notebook-list-' . $instance ); ?>">
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-rendered list HTML is escaped by its row renderer.
    echo cp_get_list( $posts );
    get_template_part( 'load-more', null, array( 'next' => $next ) );
    ?>
    <?php if ( $next ) : ?>
      <nav class="post-browser__pagination" data-post-pagination aria-label="<?php esc_attr_e( 'More posts', 'commonplace' ); ?>">
        <a class="button" href="<?php echo esc_url( $next ); ?>"><?php esc_html_e( 'Next posts', 'commonplace' ); ?></a>
      </nav>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
}

function get_notebook_index( $taxonomy, $show_years, $count ) {
  // Retain the legacy years argument for callers; the cloud contains terms only.
  $taxonomy = array_values( array_diff( $taxonomy, array( 'post_format' ) ) );
  $terms    = $taxonomy ? get_terms(
    array(
      'taxonomy' => $taxonomy,
      'orderby'  => 'name',
      'order'    => 'ASC',
    )
  ) : array();
  if ( is_wp_error( $terms ) || ! $terms ) {
    return '';
  }

  $terms = array_values(
    array_filter(
      $terms,
      static function ( $term ) use ( $count ) {
          return $term->count >= $count;
      }
    )
  );
  if ( ! $terms ) {
    return '';
  }

  // Partition without re-sorting, preserving alphabetical order within each group.
  $text_terms  = array();
  $emoji_terms = array();
  foreach ( $terms as $term ) {
    if ( preg_match( '/^\s*(?:\p{Extended_Pictographic}|[\x{1F1E6}-\x{1F1FF}]|[#*0-9]\x{FE0F}?\x{20E3})/u', html_entity_decode( $term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) {
      $emoji_terms[] = $term;
    } else {
      $text_terms[] = $term;
    }
  }
  $terms = array_merge( $text_terms, $emoji_terms );

  ob_start();
  ?>
  <div class="termindex">
    <ul role="list">
    <?php foreach ( $terms as $term ) : ?>
      <?php
      $url = get_term_link( $term );
      if ( is_wp_error( $url ) ) {
        continue;
      }
      ?>
      <?php
      $tax = get_taxonomy( $term->taxonomy );
      /* translators: 1: Taxonomy label, 2: Term name, 3: Number of posts. */
      $label = sprintf( _n( '%1$s: %2$s, %3$s post', '%1$s: %2$s, %3$s posts', $term->count, 'commonplace' ), $tax->labels->singular_name, $term->name, number_format_i18n( $term->count ) );
      ?>
      <li><?php echo wp_kses_post( cp_term_pill( $term->name, $term->count, $url, $label ) ); ?></li>
    <?php endforeach; ?>
    </ul>
  </div>
  <?php
  return ob_get_clean();
}


function cp_pagetype( $display = true ) {
  $class = is_singular() ? 'permalink' : '';
  if ( true === $display ) {
    echo esc_attr( $class );
  } else {
    return $class;
  }
}

function cp_permalink( $display = true, $prefix = '', $link_text = '', $suffix = '' ) {
  $return = '';

  if ( ! is_singular() ) {
    $return = $prefix . '<a href="' . esc_url( get_the_permalink() ) . '">' . $link_text . '</a>' . $suffix;
  }

  if ( true === $display ) {
    echo wp_kses_post( $return );
  } else {
    return $return;
  }
}

function cp_editlink( $display = true, $prefix = '', $link_text = '', $suffix = '' ) {
  $return = '';

  if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
    $return = $prefix . '<a href="' . esc_url( get_edit_post_link() ) . '">' . $link_text . '</a>' . $suffix;
  }

  if ( true === $display ) {
    echo wp_kses_post( $return );
  } else {
    return $return;
  }
}

function cp_date( $format = false, $display = true ) {
  if ( ! $format ) {
    $date = get_the_date();
  } else {
    $str  = 'Y-m-d H:i:s';
    $date = get_the_date( $str );
    $date = DateTime::createFromFormat( $str, $date );
    $date = $date->format( DateTime::RFC3339 );
  }

  if ( true === $display ) {
    echo esc_html( $date );
  } else {
    return $date;
  }
}

function cp_title( $display = true, $is_hidden = false ) {
  $title = get_the_title();
  if ( empty( $title ) ) {
    return;
  }

  if ( ! is_singular() && ! $is_hidden ) {
    $title = '<a href="' . esc_url( get_the_permalink() ) . '">' . $title . '</a>';
  }

  if ( true === $display ) {
    echo wp_kses_post( $title );
  } else {
    return $title;
  }
}

function cp_author( $display = true ) {
  $return = '';

  $id   = get_the_author_meta( 'ID' );
  $name = get_the_author_meta( 'display_name' );

  if ( $id && $name ) {
    $return .= '<a href="' . esc_url( get_author_posts_url( $id ) ) . '">' . esc_html( $name ) . '</a>';
  }

  if ( true === $display ) {
    echo wp_kses_post( $return );
  } else {
    return $return;
  }
}

/**
 * Render a term pill with a localized count and accessible link label.
 *
 * @param string $name  Term name.
 * @param int    $count Post count.
 * @param string $url   Optional destination; empty produces a heading pill.
 * @param string $label Optional accessible link label.
 * @return string Escaped pill HTML.
 */
function cp_term_pill( $name, $count, $url = '', $label = '' ) {
  $number = number_format_i18n( $count );
  $text   = esc_html( $name ) . '<span class="term__count" aria-hidden="true">' . esc_html( $number ) . '</span>';
  if ( ! $url ) {
    return '<span class="archive-heading__term">' . $text . '</span>';
  }
  if ( ! $label ) {
    /* translators: 1: Term name, 2: Number of posts. */
    $label = sprintf( _n( '%1$s, %2$s post', '%1$s, %2$s posts', $count, 'commonplace' ), $name, $number );
  }
  return '<a class="term-link" aria-label="' . esc_attr( $label ) . '" href="' . esc_url( $url ) . '">' . $text . '</a>';
}

/**
 * Find terms sharing the most published posts with the current archive term.
 *
 * @return WP_Term[] Related tags.
 */
function cp_related_archive_terms() {
  if ( ! is_tag() && ! is_category() && ! is_tax( 'post_format' ) ) {
    return array();
  }

  $current_term = get_queried_object();
  if ( ! $current_term instanceof WP_Term ) {
    return array();
  }

  global $wpdb;

  $cache_key = 'cp_related_tags_13_' . $current_term->term_taxonomy_id;
  $version   = get_transient( 'cp_related_tags_generation' );
  if ( false === $version ) {
    $version = wp_generate_uuid4();
    set_transient( 'cp_related_tags_generation', $version, YEAR_IN_SECONDS );
  }
  $cached = get_transient( $cache_key );
  if ( is_array( $cached ) && $cached['version'] === $version ) {
    $term_ids = $cached['ids'];
  } else {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Aggregate query cached below and invalidated by post and term changes.
    $term_ids = $wpdb->get_col(
      $wpdb->prepare(
        "SELECT related.term_id
        FROM {$wpdb->term_relationships} AS source
        INNER JOIN {$wpdb->posts} AS posts ON posts.ID = source.object_id
        INNER JOIN {$wpdb->term_relationships} AS relationships ON relationships.object_id = posts.ID
        INNER JOIN {$wpdb->term_taxonomy} AS related ON related.term_taxonomy_id = relationships.term_taxonomy_id
        INNER JOIN {$wpdb->terms} AS terms ON terms.term_id = related.term_id
        WHERE source.term_taxonomy_id = %d
          AND posts.post_type = 'post'
          AND posts.post_status = 'publish'
          AND related.taxonomy = 'post_tag'
          AND related.count >= 3
          AND related.term_taxonomy_id != %d
        GROUP BY related.term_id, terms.name
        ORDER BY COUNT(DISTINCT posts.ID) DESC, terms.name ASC, related.term_id ASC
        LIMIT 13",
        $current_term->term_taxonomy_id,
        $current_term->term_taxonomy_id
      )
    );
    if ( ! $wpdb->last_error ) {
      set_transient(
        $cache_key,
        array(
          'version' => $version,
          'ids'     => $term_ids,
        ),
        DAY_IN_SECONDS
      );
    }
  }

  if ( ! $term_ids ) {
    return array();
  }

  $terms = get_terms(
    array(
      'taxonomy'   => 'post_tag',
      'include'    => array_map( 'intval', $term_ids ),
      'orderby'    => 'count',
      'order'      => 'DESC',
      'hide_empty' => true,
    )
  );

  return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Invalidate related-tag results when posts or taxonomy relationships change.
 */
function cp_invalidate_related_tags() {
  delete_transient( 'cp_related_tags_generation' );
}
add_action( 'clean_post_cache', 'cp_invalidate_related_tags' );
add_action( 'clean_term_cache', 'cp_invalidate_related_tags' );
add_action( 'set_object_terms', 'cp_invalidate_related_tags' );
add_action( 'deleted_term_relationships', 'cp_invalidate_related_tags' );

function cp_archivedesc( $display = true, $prefix = '', $suffix = '' ) {
  global $wp_query;
  $total  = $wp_query->found_posts;
  $return = '';
  if ( is_tag() ) {
    $return .= tag_description();
  } elseif ( is_category() ) {
    $return .= category_description();
  } elseif ( is_author() ) {
    $return .= '<p>' . get_the_author_meta( 'description' ) . '</p>';
  } elseif ( is_home() && ! is_paged() ) {
    $intro = get_theme_mod( 'cp_blog_intro' );
    if ( $intro ) {
      $return .= '<p>' . strip_tags( $intro, '<em><a><img><br>' ) . '</p>';
    }
  }

  $return = ! empty( $return ) ? $prefix . $return . $suffix : '';

  if ( true === $display ) {
    echo wp_kses_post( $return );
  } else {
    return $return;
  }
}

function cp_get_theme_version() {
  $version = '1';
  $theme   = wp_get_theme();
  if ( ! $theme->exists() ) {
    return;
  }
  $version = $theme->get( 'Version' );
  return $version;
}

/**
 * Return a readable heading for non-term archive and utility pages.
 *
 * @return string Context heading.
 */
function cp_context_heading() {
  if ( is_404() ) {
    return __( 'Page not found.', 'commonplace' );
  }
  if ( is_search() ) {
    /* translators: %s: Search query. */
    return sprintf( __( 'Search results for “%s”.', 'commonplace' ), get_search_query( false ) );
  }
  if ( is_author() ) {
    /* translators: %s: Author name. */
    return sprintf( __( 'You are viewing posts by %s.', 'commonplace' ), get_queried_object()->display_name );
  }
  if ( is_year() || is_month() || is_day() ) {
    $year   = (int) get_query_var( 'year' );
    $month  = is_year() ? 1 : (int) get_query_var( 'monthnum' );
    $day    = is_day() ? (int) get_query_var( 'day' ) : 1;
    $format = is_year() ? 'Y' : ( is_month() ? _x( 'F Y', 'monthly archive date format', 'commonplace' ) : get_option( 'date_format' ) );
    $date   = wp_date( $format, ( new DateTimeImmutable( sprintf( '%04d-%02d-%02d 12:00:00', $year, $month, $day ), wp_timezone() ) )->getTimestamp() );
    /* translators: %s: Archive date. */
    return sprintf( __( 'You are viewing the %s archive.', 'commonplace' ), $date );
  }
  if ( is_home() ) {
    return __( 'You are viewing the notes archive.', 'commonplace' );
  }
  return wp_strip_all_tags( get_the_archive_title() );
}

function cp_archive_label() {
  global $wp_query;
  if ( is_singular() ) {
    return get_the_title();
  }
  if ( is_404() ) {
    return __( 'Page not found [404 error]', 'commonplace' );
  }
  $paged = get_query_var( 'paged' );
  /* translators: %s: Page number. */
  $page  = $paged > 0 ? ', ' . sprintf( __( 'page %s', 'commonplace' ), $paged ) : '';
  $label = false;
  if ( is_front_page() ) {
    $label = get_bloginfo( 'name' );
  } elseif ( is_tax( 'post_format' ) ) {
    $var = get_post_format();
    /* translators: %s: Post format. */
    $label = sprintf( __( 'Format: %s', 'commonplace' ), $var );
  } elseif ( is_tag() ) {
    $var = single_tag_title( '', false );
    /* translators: %s: Tag name. */
    $label = sprintf( __( 'Tag: %s', 'commonplace' ), $var );
  } elseif ( is_category() ) {
    $var = single_cat_title( '', false );
    /* translators: %s: Category name. */
    $label = sprintf( __( 'Category: %s', 'commonplace' ), $var );
  } elseif ( is_search() ) {
    $query = get_query_var( 's' );
    /* translators: %s: Search query. */
    $label = sprintf( __( 'You searched for &ldquo;%s&rdquo;', 'commonplace' ), $query );
  } elseif ( is_author() ) {
    $var = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
    /* translators: %s: Author name. */
    $label = sprintf( __( 'Author: %s', 'commonplace' ), $var );
  } elseif ( is_month() ) {
    $var = get_the_date( 'F Y' );
    /* translators: %s: Archive month and year. */
    $label = sprintf( __( 'Month: %s', 'commonplace' ), $var );
  } elseif ( is_year() ) {
    $var = get_the_date( 'Y' );
    /* translators: %s: Archive year. */
    $label = sprintf( __( 'Year: %s', 'commonplace' ), $var );
  }

  /* translators: %s: Number of posts. */
  $label = $label . $page . ' &mdash; ' . sprintf( _n( '%s post', '%s posts', $wp_query->found_posts, 'commonplace' ), $wp_query->found_posts );

  return $label;
}

function cp_get_copyright() {
  $copy = '';
  if ( is_single() ) {
    $copy .= get_the_date( 'Y' );
  } else {
    $args        = array(
      'order'          => 'ASC',
      'posts_per_page' => 1,
      'post_type'      => 'any',
      'post_status'    => 'publish,private,draft',
    );
    $posts_array = get_posts( $args );
    if ( ! empty( $posts_array ) ) {
      $copy .= get_the_date( 'Y', $posts_array[0]->ID ) . '&ndash;';
    }
    $copy .= gmdate( 'Y' );
  }
  $copy = '&copy; ' . $copy;
  return $copy;
}

function cp_tag_opacity( $tag, $max_opacity = 1, $min_opacity = .1 ) {
  $opacity = 1;
  $args    = array(
    'orderby' => 'count',
    'number'  => 1,
  );
  $tags    = get_tags( $args );
  $min_in  = $tags[0]->count;
  $tags    = array_reverse( $tags );
  $max_in  = $tags[0]->count;
  $max_in  = 8;
  $count   = $tag->count > $max_in ? $max_in : $tag->count;

  $percentage = ( $count - $min_in ) / ( $max_in - $min_in );

  $opacity = $percentage * ( $max_opacity - $min_opacity ) + $min_opacity;
  return round( $opacity, 4 );
}

function cp_map_hue( $timezone, $lat, $unix ) {
  $tz       = new DateTimeZone( $timezone );
  $datetime = DateTime::createFromFormat( 'U', $unix );
  $datetime->setTimezone( $tz );
  $dayofyear = $datetime->format( 'z' );
  $june_dt   = DateTime::createFromFormat( 'Y-m-d', $datetime->format( 'Y' ) . '-06-22' );
  $june_dt->setTimezone( $tz );
  $june_doy = $june_dt->format( 'z' );
  $last_dt  = DateTime::createFromFormat( 'Y-m-d', $datetime->format( 'Y' ) . '-12-31' );
  $last_dt->setTimezone( $tz );
  $last_doy = $last_dt->format( 'z' );
  $offset   = $lat > 0 ? 52 : 200;
  if ( $dayofyear < $june_doy ) {
    $diff = $dayofyear + ( $last_doy - $june_doy );
  } else {
    $diff = $dayofyear - $june_doy;
  }

  $return = 0 - $diff;
  $return = $return + $offset;
  $return = $return < 0 ? $return + 360 : $return;
  return $return;
}

function cp_get_hsl( $hsl_post = false ) {
  if ( ! $hsl_post ) {
    return 'hsl(1, 100%, 0%)';
  }
  $unix = get_the_date( 'U', $hsl_post->ID );
  $hue  = cp_map_hue( 'Europe/London', 51.567592, $unix );
  $hsl  = sprintf( 'hsl(%s, 70%%, 50%%)', $hue );
  return $hsl;
}

function cp_group_posts_by_year() {
  global $post;
  $years = array();
  $posts = get_posts(
    array(
      'posts_per_page' => -1,
    )
  );
  // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Template tags require the global post; reset below.
  foreach ( $posts as $post ) {
    setup_postdata( $post );
    $year = get_the_date( 'Y' );
    if ( ! isset( $years[ $year ] ) ) {
      $years[ $year ] = array();
    }
    $years[ $year ][] = array(
      'title'     => get_the_title(),
      'permalink' => get_the_permalink(),
      'hsl'       => cp_get_hsl( $post ),
      'id'        => get_the_id( $post ),
    );
  }
  wp_reset_postdata();
  return $years;
}

function cp_get_year_counts() {
  global $wpdb;
  $result = array();
  $years  = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT YEAR(post_date) AS year, COUNT(*) AS post_count FROM {$wpdb->posts} WHERE post_status = %s AND post_type = %s GROUP BY YEAR(post_date) ORDER BY YEAR(post_date) DESC",
      'publish',
      'post'
    )
  );
  if ( is_array( $years ) ) {
    foreach ( $years as $year ) {
      $result[ $year->year ] = (int) $year->post_count;
    }
  }
  return $result;
}

function cp_get_post_terms( $post_id, $taxonomies ) {
  $terms = array();
  foreach ( $taxonomies as $taxonomy ) {
    $post_terms = get_the_terms( $post_id, $taxonomy );
    if ( is_array( $post_terms ) ) {
      $terms = array_merge( $terms, $post_terms );
    }
  }
  if ( count( $terms ) < 2 ) {
    return $terms;
  }
  // Sort this post's cached terms by count, with a stable name fallback.
  usort(
    $terms,
    static function ( $a, $b ) {
      $a_name = 'post_format' === $a->taxonomy ? $a->slug : $a->name;
      $b_name = 'post_format' === $b->taxonomy ? $b->slug : $b->name;
      $result = $b->count <=> $a->count;
      if ( ! $result ) {
        $result = strcasecmp( remove_accents( $a_name ), remove_accents( $b_name ) );
      }
      return $result ? $result : $a->term_id <=> $b->term_id;
    }
  );
  return $terms;
}

function cp_get_list_item( $post = false ) {
  if ( ! $post ) {
    global $post;
  }
  ?>
  <?php $format = get_post_format( $post ); ?>
  <?php $hsl = cp_get_hsl( $post, 'thumbnail' ); ?>
  <?php $title = get_the_title( $post ); ?>
  <li class="post-index__post-item" style="--color:<?php echo esc_attr( $hsl ); ?>;">
  <?php if ( has_post_thumbnail() ) : ?>
    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates and escapes the thumbnail HTML, including theme CSS properties.
    echo cp_list_thumb( $post );
    ?>
  <?php endif; ?>
  <div class="post-item">
    <a class="post-item__link" href="<?php the_permalink(); ?>">
    <time class="post-item__time" datetime="<?php echo esc_attr( cp_date( true, false ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
    <?php
    if ( ! is_single() && is_sticky() ) :
      ?>
      <span aria-label="<?php esc_attr_e( 'Pinned', 'commonplace' ); ?>"> ◆</span><?php endif; ?>
    </a>
    <a class="post-item__link" href="<?php the_permalink(); ?>">
    <span class="post-item__text"><?php echo wp_kses_post( '' === $title ? get_the_excerpt() : $title ); ?></span>
    </a>
    <?php
    $terms = cp_get_post_terms( get_the_ID(), array( 'post_tag' ) );
    ?>
    <?php if ( $terms ) : ?>
    <ul class="post-item__terms">
      <?php foreach ( $terms as $slug => $term ) : ?>
        <?php if ( (int) get_option( 'default_category' ) !== (int) $term->term_id ) : ?>
        <li class="term term--<?php echo esc_attr( $term->taxonomy ); ?>">
          <?php echo wp_kses_post( cp_term_pill( $term->name, $term->count, get_term_link( $term ) ) ); ?>
        </li>
      <?php endif; ?>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
  </li>
  <?php
}

function cp_get_list_arr( $posts = false ) {
  global $post;
  if ( ! $posts ) {
    $posts = get_posts( array( 'posts_per_page' => -1 ) );
  }
  if ( $posts ) {
    add_filter( 'excerpt_length', 'cp_excerpt_as_title' );
    ob_start();
    ?>
  <ul class="post-index">
    <?php // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Set up each post for template tags; reset below.
    foreach ( $posts as $post ) :
      ?>
      <?php setup_postdata( $post ); ?>
      <?php cp_get_list_item( $post ); ?>
      <?php wp_reset_postdata(); ?>
    <?php endforeach; ?>
  </ul>
    <?php
    remove_filter( 'excerpt_length', 'cp_excerpt_as_title' );
    return ob_get_clean();
  }
}

function cp_get_list( $posts = false ) {
  global $post;
  if ( ! $posts ) {
    $posts = new WP_Query(
      array(
        'post_type'      => 'post',
        'posts_per_page' => 50,
      )
    );
  }
  if ( $posts ) {
    add_filter( 'excerpt_length', 'cp_excerpt_as_title' );
    ob_start();
    ?>
  <ul class="post-index" data-post-items>
    <?php $orig = $post; ?>
    <?php if ( $posts->have_posts() ) : ?>
      <?php while ( $posts->have_posts() ) : ?>
        <?php $posts->the_post(); ?>
        <?php cp_get_list_item(); ?>
    <?php endwhile; ?>
    <?php endif; ?>
    <?php // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore the original global post after the custom loop.
    $post = $orig;
    wp_reset_postdata();
    ?>
  </ul>
    <?php
    remove_filter( 'excerpt_length', 'cp_excerpt_as_title' );
    return ob_get_clean();
  }
}

function max_img_width( $img_width, $img_height, $ideal_area ) {
  $max_width = round( $img_width * sqrt( $ideal_area / ( $img_width * $img_height ) ) );
  return $max_width;
}

function cp_list_thumb( $post, $size = 'cp-list-thumb' ) {
  $attachment_id = get_post_thumbnail_id( $post->ID );
  // Older uploads may not have the theme's list size until thumbnails are regenerated.
  if ( 'cp-list-thumb' === $size && ! image_get_intermediate_size( $attachment_id, $size ) ) {
    $size = 'thumbnail';
  }
  $data = wp_get_attachment_image_src( $attachment_id, $size );
  if ( ! $data ) {
    return '';
  }
  add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
  $rotations = array( wp_rand( 10, 20 ), wp_rand( -20, -10 ) );
  $y_offset  = array( wp_rand( 4, 6 ), wp_rand( -6, -4 ) );
  $style     = '--pos-right:' . wp_rand( 5, 95 ) . '%;--trans-y:' . $y_offset[ array_rand( $y_offset ) ] . 'rem;--rotate:' . $rotations[ array_rand( $rotations ) ] . 'deg;--max-width:' . max_img_width( $data[1], $data[2], 8000 ) / 16 . 'rem;';
  $html      = get_the_post_thumbnail(
    $post,
    $size,
    array(
      'loading'     => 'lazy',
      'style'       => $style,
      'class'       => 'post-item__image',
      'aria-hidden' => 'true',
      'role'        => 'decoration',
    )
  );
  remove_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
  return $html;
}

function cp_comment( $comment, $args, $depth ) {
  if ( 'div' === $args['style'] ) {
    $tag       = 'div';
    $add_below = 'comment';
  } else {
    $tag       = 'li';
    $add_below = 'div-comment';
  }
  ?>
  <<?php echo esc_html( $tag ); ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
  <?php
  if ( 'div' !== $args['style'] ) {
    ?>
  <div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
    <?php
  }
  ?>
  <div class="comment-avatar">
    <?php
    if ( 0 !== (int) $args['avatar_size'] ) {
      echo get_avatar( $comment, $args['avatar_size'] );
    }
    ?>
  </div>

  <div class="comment-content prose">
    <?php if ( '0' === (string) $comment->comment_approved ) : ?>
    <p><em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.' ); ?></em></p>
    <?php endif; ?>
    <?php comment_text(); ?>
  </div>

  <div class="comment-author vcard">
    <cite class="fn"><?php echo wp_kses_post( get_comment_author_link() ); ?></cite>,
    <span class="comment-meta commentmetadata">
    <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
    <?php
      printf(
        /* translators: 1: Comment date, 2: Comment time. */
        esc_html__( '%1$s at %2$s' ),
        esc_html( get_comment_date() ),
        esc_html( get_comment_time() )
      );
    ?>
    </a>
    </span><?php edit_comment_link( __( 'Edit' ), ' / ', '' ); ?>
  </div>

  <div class="reply">
  <?php
    comment_reply_link(
      array_merge(
        $args,
        array(
          'add_below' => $add_below,
          'depth'     => $depth,
          'max_depth' => $args['max_depth'],
        )
      )
    );
  ?>
  </div>
  <?php
  if ( 'div' !== $args['style'] ) :
    ?>
  </div>
    <?php
  endif;
}

/**
 * Register the opt-in archive layout preview.
 *
 * @param string[] $vars Public query variables.
 * @return string[]
 */
function cp_layout_preview_query_vars( $vars ) {
  $vars[] = 'cp_view';
  $vars[] = 'cp_list_page';
  return $vars;
}
add_filter( 'query_vars', 'cp_layout_preview_query_vars' );

/**
 * Set the batch sizes for the grid and list archive previews.
 *
 * @param WP_Query $query The current query.
 */
function cp_loop_posts_per_page( $query ) {
  if ( is_admin() || ! $query->is_main_query() || $query->is_feed() ) {
    return;
  }

  if ( in_array( $query->get( 'cp_view' ), array( 'grid', 'list' ), true ) && ( $query->is_home() || $query->is_archive() || $query->is_search() ) ) {
    $query->set( 'posts_per_page', 'list' === $query->get( 'cp_view' ) ? 50 : 24 );
  }
}
add_action( 'pre_get_posts', 'cp_loop_posts_per_page' );
