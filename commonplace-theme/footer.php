<footer class="site-footer" role="contentinfo">
  <p class="visuallyhidden"><a href="#top" class="visuallyhidden button"><?php esc_html_e( 'Back to top', 'commonplace' ); ?></a></p>

  <?php if ( is_home() || is_archive() || is_search() ) : ?>
  <nav class="pagination pagination--archive">
    <?php
    echo wp_kses_post(
      (string) paginate_links(
        array(
          'prev_next' => false,
          'end_size'  => 2,
          'type'      => 'list',
        )
      )
    );
    ?>
  </nav>
  <?php elseif ( is_singular( 'post' ) ) : ?>
    <?php $prev = get_previous_post(); ?>
    <?php $next = get_next_post(); ?>
    <?php $p = array( $prev, $next ); ?>
    <?php $p = array_filter( $p ); ?>
    <?php if ( $p ) : ?>
    <nav class="pagination pagination--post">
    <p class="pagination__label">
      <?php if ( $prev && $next ) : ?>
      <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>"><?php esc_html_e( 'Next', 'commonplace' ); ?></a> / <a href="<?php echo esc_url( get_permalink( $prev->ID ) ); ?>"><?php esc_html_e( 'Previous', 'commonplace' ); ?></a>
      <?php elseif ( $prev ) : ?>
      <a href="<?php echo esc_url( get_permalink( $prev->ID ) ); ?>"><?php esc_html_e( 'Previous', 'commonplace' ); ?></a>
      <?php else : ?>
      <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>"><?php esc_html_e( 'Next', 'commonplace' ); ?></a>
      <?php endif; ?>
    </p>
    </nav>
  <?php endif; ?>
  <?php endif; ?>

  <?php wp_footer(); ?>

  <p class="visuallyhidden"><a href="#top" class="visuallyhidden button"><?php esc_html_e( 'Back to top', 'commonplace' ); ?></a></p>

</footer>

</body>
</html>
