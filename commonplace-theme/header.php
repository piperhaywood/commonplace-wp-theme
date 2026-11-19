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
    <?php if ( is_archive() && ! $archive_term instanceof WP_Term ) : ?>
    <h1 class="visuallyhidden">
      <?php the_archive_title(); ?>
    </h1>
    <?php endif; ?>

    <?php $archive_title = cp_archive_str( false ); ?>
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
        <h1 class="archive-heading__title"><?php
        $archive_term_pill = '<span class="archive-heading__term">' . esc_html( $archive_term->name ) . '<span class="term__count" aria-hidden="true">' . esc_html( $archive_term->count ) . '</span></span>';
        /* translators: 1: Term pill HTML, 2: Taxonomy type (tag, category, or format). */
        echo wp_kses_post( sprintf( __( 'You are viewing the %1$s %2$s archive.', 'commonplace' ), $archive_term_pill, esc_html( $archive_type ) ) );
        ?></h1>
      </div>
      <?php elseif ( $archive_title ) : ?>
      <div class="breadcrumb">
        <p>
        <a href="<?php bloginfo( 'url' ); ?>" aria-label="Return home">..</a> / <?php echo wp_kses_post( $archive_title ); ?>
        </p>
      </div>
      <?php endif; ?>

      <?php $archive_description = cp_archivedesc( false ); ?>
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
            /* translators: 1: Term name, 2: Number of posts. */
            $related_term_label = sprintf( _n( '%1$s, %2$s post', '%1$s, %2$s posts', $related_term->count, 'commonplace' ), $related_term->name, $related_term->count );
            ?>
            <a class="term-link" aria-label="<?php echo esc_attr( $related_term_label ); ?>" href="<?php echo esc_url( $related_term_url ); ?>"><?php echo esc_html( $related_term->name ); ?><span class="term__count"><?php echo esc_html( $related_term->count ); ?></span></a>
          <?php endforeach; ?>
        </p>
      </nav>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </header>
