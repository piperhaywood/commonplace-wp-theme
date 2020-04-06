<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="referrer" content="origin-when-cross-origin" />
    <title><?php wp_title('—', 'false', 'right'); ?></title>
    <?php wp_head(); ?>
  </head>

  <body <?php body_class(implode(' ', ['is-not-tabbing'])); ?>>

    <header id="top" class="header js-header">
      <div class="container">
        <a class="visuallyhidden button" href="#main"><?php esc_html_e('Skip to main content', 'commonplace'); ?></a>

        <?php $items = wp_get_nav_menu_items('header'); ?>
        <?php wp_nav_menu( array(
          'theme_location' => 'cp-menu',
          'container' => 'nav',
          'menu_class' => 'menu',
          'depth' => 1
        ) ); ?>
      </div>
    </header>
