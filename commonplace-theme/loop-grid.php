<main id="main" class="main" role="main">
  <section class="post-grid" aria-label="<?php esc_attr_e( 'Posts', 'commonplace' ); ?>">
    <?php while ( have_posts() ) : ?>
      <?php
      the_post();
      $card_title = trim( get_the_title() );
      if ( '' === $card_title ) {
        $card_title = wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 12, '…' );
      }
      if ( '' === $card_title ) {
        $card_title = __( 'Untitled post', 'commonplace' );
      }
      ?>
      <article <?php post_class( 'post-grid__card' ); ?>>
        <header class="post-header">
          <p class="post-time">
            <span class="visuallyhidden"><?php esc_html_e( 'Published', 'commonplace' ); ?> </span>
            <time datetime="<?php echo esc_attr( cp_date( true, false ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
          </p>
          <h2 class="post-title"><?php echo esc_html( $card_title ); ?></h2>
        </header>
        <div class="post-grid__image">
          <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'large', array( 'class' => 'post-grid__thumbnail' ) ); ?>
          <?php endif; ?>
        </div>
        <a class="post-grid__link" href="<?php the_permalink(); ?>">
          <span class="visuallyhidden">
            <?php
            /* translators: %s: Post title. */
            echo esc_html( sprintf( __( 'View post %s', 'commonplace' ), $card_title ) );
            ?>
          </span>
        </a>
      </article>
    <?php endwhile; ?>
  </section>
  <?php
  global $wp_query;
  $next_grid_page = get_next_posts_page_link( $wp_query->max_num_pages );
  ?>
  <div class="grid-loader" hidden>
    <button class="button grid-loader__button" type="button"
      data-next="<?php echo $next_grid_page ? esc_url( add_query_arg( 'cp_view', 'grid', $next_grid_page ) ) : ''; ?>"
      data-loading="<?php esc_attr_e( 'Loading posts…', 'commonplace' ); ?>"
      data-error="<?php esc_attr_e( 'Could not load posts. Please try again or use the page links below.', 'commonplace' ); ?>"
      data-loaded="<?php esc_attr_e( 'More posts loaded.', 'commonplace' ); ?>"
      data-end="<?php esc_attr_e( 'All posts loaded.', 'commonplace' ); ?>">
      <?php esc_html_e( 'Load more posts', 'commonplace' ); ?>
    </button>
    <p class="grid-loader__status visuallyhidden" role="status" aria-live="polite"></p>
  </div>
</main>
