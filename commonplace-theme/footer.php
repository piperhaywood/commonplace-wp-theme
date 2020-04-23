<footer class="site-footer" role="contentinfo">
  <p class="visuallyhidden"><a href="#top" class="visuallyhidden button"><?php _e('Back to top', 'commonplace'); ?></a></p>

  <?php if (is_home() || is_archive() || is_search()) : ?>
    <nav class="pagination pagination--archive">
      <?php echo paginate_links(array(
        'prev_next' => false,
        'end_size' => 2,
        'type' => 'list'
      )); ?>
    </nav>
  <?php elseif (is_singular('post')) : ?>
    <?php $prev = get_previous_post(); ?>
    <?php $next = get_next_post(); ?>
    <?php $p = array($prev, $next); ?>
    <?php $p = array_filter($p); ?>
    <?php if ($p) : ?>
      <nav class="pagination pagination--post">
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

  <div id="footer-sidebar" class="secondary">
<div id="footer-sidebar1">
<?php
if(is_active_sidebar('footer-sidebar-1')){
dynamic_sidebar('footer-sidebar-1');
}
?>
</div>
<div id="footer-sidebar2">
<?php
if(is_active_sidebar('footer-sidebar-2')){
dynamic_sidebar('footer-sidebar-2');
}
?>
</div>
<div id="footer-sidebar3">
<?php
if(is_active_sidebar('footer-sidebar-3')){
dynamic_sidebar('footer-sidebar-3');
}
?>
</div>
</div>

  <div class="footer-widgets">
    <?php if (is_active_sidebar('footer-area-1')) : ?>
      <div class="footer__widget cp-widget cp-widget--1">
        <?php dynamic_sidebar('footer-area-1'); ?>
      </div>
    <?php endif; ?>
    <?php if (is_active_sidebar('footer-area-2')) : ?>
      <div class="footer__widget cp-widget--2 cp-widget">
        <?php dynamic_sidebar('footer-area-2'); ?>
      </div>
    <?php endif; ?>
    <?php if (is_active_sidebar('footer-area-3')) : ?>
      <div class="footer__widget cp-widget cp-widget--3 cp-widget--small">
        <?php dynamic_sidebar('footer-area-3'); ?>
      </div>
    <?php endif; ?>
  </div>

  <p class="visuallyhidden"><a href="#top" class="visuallyhidden button"><?php _e('Back to top', 'commonplace'); ?></a></p>

</footer>

</body>
</html>