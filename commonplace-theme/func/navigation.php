<?php

register_nav_menus(
  array(
    'cp-menu' => esc_attr__( 'Header Menu', 'commonplace' ),
  )
);

function cp_menu() {
  wp_nav_menu(
    array(
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
    )
  );
}
