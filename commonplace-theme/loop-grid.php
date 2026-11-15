<main id="main" class="main" role="main" data-post-browser="archive">
  <section class="post-grid" data-post-items aria-label="<?php esc_attr_e( 'Posts', 'commonplace' ); ?>">
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
          <?php elseif ( ! post_password_required() ) : ?>
            <?php
            // A visual crop of the post, without executing blocks, embeds, or shortcodes.
            $preview_text = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', get_the_ID() ) ) );
            $preview_text = wp_html_excerpt( $preview_text, 4000, '' );
            ?>
            <div class="post-grid__text" aria-hidden="true">
              <?php echo wp_kses_post( wpautop( esc_html( $preview_text ) ) ); ?>
            </div>
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
  <?php get_template_part( 'load-more', null, array( 'next' => $next_grid_page ? add_query_arg( 'cp_view', 'grid', $next_grid_page ) : '' ) ); ?>
</main>
