<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="referrer" content="origin-when-cross-origin" />
  <?php wp_head(); ?>
  </head>

  <body <?php body_class( implode( ' ', array( 'is-not-tabbing' ) ) ); ?>>

  <header id="top" class="header<?php echo is_active_sidebar( 'header-area-1' ) ? '' : ' header--without-intro'; ?>">
    <div class="container container--xl">
    <a class="visuallyhidden button" href="#main"><?php esc_html_e( 'Skip to main content', 'commonplace' ); ?></a>
    </div>

    <?php if ( is_active_sidebar( 'header-area-1' ) ) : ?>
    <div class="header-intro cp-widget cp-widget--header-1">
      <?php dynamic_sidebar( 'header-area-1' ); ?>
    </div>
    <?php endif; ?>

    <?php $archive_term = ( is_tag() || is_category() || is_tax() ) ? get_queried_object() : null; ?>
    <?php $archive_title = ( is_archive() || is_search() || is_404() || is_home() ) ? cp_context_heading() : ''; ?>
    <?php $archive_description = cp_archivedesc( false ); ?>
    <?php if ( $archive_title || $archive_description ) : ?>
    <div class="page-context">
      <?php if ( $archive_term instanceof WP_Term ) : ?>
        <?php
        $archive_taxonomy = get_taxonomy( $archive_term->taxonomy );
        $archive_type     = $archive_taxonomy->labels->singular_name;
        if ( 'post_tag' === $archive_term->taxonomy ) {
          $archive_type = _x( 'tag', 'archive type', 'commonplace' );
        } elseif ( 'category' === $archive_term->taxonomy ) {
          $archive_type = _x( 'category', 'archive type', 'commonplace' );
        } elseif ( 'post_format' === $archive_term->taxonomy ) {
          $archive_type = _x( 'format', 'archive type', 'commonplace' );
        }
        ?>
      <div class="archive-heading">
        <h1 class="archive-heading__title">
        <?php
        $archive_term_pill = cp_term_pill( $archive_term->name, $archive_term->count );
        /* translators: 1: Term pill HTML, 2: Taxonomy type (tag, category, or format). */
        echo wp_kses_post( sprintf( __( 'You are viewing the %1$s %2$s archive.', 'commonplace' ), $archive_term_pill, esc_html( $archive_type ) ) );
        ?>
        </h1>
      </div>
      <?php elseif ( $archive_title ) : ?>
      <h1 class="archive-heading__title"><?php echo esc_html( cp_context_heading() ); ?></h1>
      <?php endif; ?>

      <?php if ( get_query_var( 'paged' ) > 1 ) : ?>
      <p class="meta">
        <?php
        /* translators: %s: Archive page number. */
        echo esc_html( sprintf( __( 'Page %s', 'commonplace' ), number_format_i18n( get_query_var( 'paged' ) ) ) );
        ?>
      </p>
      <?php endif; ?>

      <?php if ( $archive_description ) : ?>
      <div class="page-description prose">
        <?php echo wp_kses_post( $archive_description ); ?>
      </div>
      <?php endif; ?>
      <?php $related_archive_terms = cp_related_archive_terms(); ?>
      <?php if ( $related_archive_terms ) : ?>
      <nav class="archive-related" aria-label="<?php esc_attr_e( 'Related terms', 'commonplace' ); ?>">
        <p class="archive-related__eyebrow"><?php esc_html_e( 'Related tags', 'commonplace' ); ?></p>
        <p class="meta post-meta">
          <?php foreach ( $related_archive_terms as $related_term ) : ?>
            <?php
            $related_term_url = get_term_link( $related_term );
            if ( is_wp_error( $related_term_url ) ) {
              continue;
            }
            ?>
            <?php echo wp_kses_post( cp_term_pill( $related_term->name, $related_term->count, $related_term_url ) ); ?>
          <?php endforeach; ?>
        </p>
      </nav>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </header>
