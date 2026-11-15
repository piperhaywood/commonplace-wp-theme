  <div class="post-loader" hidden>
    <button class="button post-loader__button" type="button"
      data-next="<?php echo esc_url( $args['next'] ?? '' ); ?>"
      data-loading="<?php esc_attr_e( 'Loading posts…', 'commonplace' ); ?>"
      data-error="<?php esc_attr_e( 'Could not load posts. Please try again or use the page links below.', 'commonplace' ); ?>"
      data-loaded="<?php esc_attr_e( 'More posts loaded.', 'commonplace' ); ?>"
      data-end="<?php esc_attr_e( 'All posts loaded.', 'commonplace' ); ?>">
      <?php esc_html_e( 'Load more posts', 'commonplace' ); ?>
    </button>
    <p class="post-loader__status visuallyhidden" role="status" aria-live="polite"></p>
  </div>
