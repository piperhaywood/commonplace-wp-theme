<?php

function cp_widgets_init() {
  register_sidebar( array(
    'name' => 'Header Area',
    'id' => 'header-area-1',
    'description' => 'Appears in the header area, best for short text like an email address',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget' => '</aside>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );
  register_sidebar( array(
    'name' => 'Footer Area #1',
    'id' => 'footer-area-1',
    'description' => 'Appears in the footer area',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget' => '</aside>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );
  register_sidebar( array(
    'name' => 'Footer Area #2',
    'id' => 'footer-area-2',
    'description' => 'Appears in the footer area',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget' => '</aside>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );
  register_sidebar( array(
    'name' => 'Footer Area #3',
    'id' => 'footer-area-3',
    'description' => 'Appears in the footer area, smallest text',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget' => '</aside>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );
}
add_action('widgets_init', 'cp_widgets_init');

function cp_unregister_default_widgets() {
  unregister_widget('WP_Widget_Pages');
  unregister_widget('WP_Widget_Media_Audio');
  unregister_widget('WP_Widget_Media_Image');
  unregister_widget('WP_Widget_Media_Gallery');
  unregister_widget('WP_Widget_Media_Video');
  unregister_widget('WP_Widget_Calendar');
  unregister_widget('WP_Widget_Archives');
  unregister_widget('WP_Widget_Links');
  unregister_widget('WP_Widget_Meta');
  unregister_widget('WP_Widget_Categories');
  unregister_widget('WP_Widget_Recent_Comments');
  unregister_widget('WP_Widget_RSS');
  unregister_widget('WP_Widget_Tag_Cloud');
}
add_action('widgets_init', 'cp_unregister_default_widgets', 11);

function cp_footer_widgets() {
  ob_start(); ?>
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
<?php echo ob_get_clean();
}
add_action('wp_footer', 'cp_footer_widgets', 5);