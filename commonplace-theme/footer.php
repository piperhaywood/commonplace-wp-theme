<footer class="site-footer" role="contentinfo">
  <?php if (is_home() || is_archive() || is_search()) : ?>
    <?php $prev = get_previous_posts_link(__('Previous page', 'commonplace')); ?>
    <?php $next = get_next_posts_link(__('Next page', 'commonplace')); ?>
    <?php if ($prev || $next) : ?>
      <nav class="pagination pagination--archive js-pagination">
        <?php if ($prev) : ?>
          <p class="pagination__link pagination__link--previous js-previous"><?php echo $prev; ?></p>
        <?php endif; ?>
        <?php if ($next) : ?>
          <p class="pagination__link pagination__link--next js-next"><?php echo $next; ?></p>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php elseif (is_singular('post')) : ?>
    <?php $prev = get_previous_post(); ?>
    <?php $next = get_next_post(); ?>
    <?php $p = array($prev, $next); ?>
    <?php $p = array_filter($p); ?>
    <?php if ($p) : ?>
      <nav class="pagination pagination--post js-pagination">
        <p class="pagination__label">
          <?php if ($prev && $next) : ?>
            <a href="<?php echo get_permalink($prev->ID); ?>"><?php _e('Previous', 'commonplace'); ?></a> / <a href="<?php echo get_permalink($next->ID); ?>"><?php _e('Next', 'commonplace'); ?></a>
          <?php elseif ($prev) : ?>
            <a href="<?php echo get_permalink($prev->ID); ?>"><?php _e('Previous', 'commonplace'); ?></a>
          <?php else : ?>
            <a href="<?php echo get_permalink($next->ID); ?>"><?php _e('Next', 'commonplace'); ?></a>
          <?php endif; ?>
        </p>
      </nav>
    <?php endif; ?>
  <?php endif; ?>

  <?php wp_footer(); ?>

  <p><a href="#top" class="visuallyhidden button"><?php _e('Back to top', 'commonplace'); ?></a></p>

</footer>

</body>
</html>