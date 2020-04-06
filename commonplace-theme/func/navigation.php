<?php

register_nav_menus(array(
  'cp-menu' => esc_attr__('Header Menu', 'commonplace')
));

function cp_menu() {
  wp_nav_menu(array(
    'container'       => false,
    'container_class' => '',
    'menu'            => '',
    'menu_class'      => 'header__nav',
    'theme_location'  => 'cp-menu',
    'before'          => '',
    'after'           => '',
    'link_before'     => '',
    'link_after'      => '',
    'depth'           => 5,
  ));
}

add_action('wp_footer', 'cp_related_posts');
function cp_related_posts( $content ) {
  global $post;
  if (shortcode_exists('related_posts_by_tax') && is_singular('post') && is_main_query()) {
    echo do_shortcode('[related_posts_by_tax post_id="' . $post->ID . '" taxonomies="post_tag" title="' . esc_attr__('See also', 'commonplace') . '" public_only="true"]');
  }
}
