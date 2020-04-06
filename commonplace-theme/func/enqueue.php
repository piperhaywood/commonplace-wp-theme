<?php

add_action('wp_enqueue_scripts', 'cp_assets', 10);
function cp_assets() {
  $version = cp_get_theme_version();

  wp_register_style(
    'cp-styles',
    get_template_directory_uri() . '/style.css',
    '',
    $version,
    'all'
  );
  wp_enqueue_style('cp-styles');

  wp_register_script(
    'cp-scripts',
    get_template_directory_uri() . '/scripts.js',
    array(),
    $version,
    true
  );
  wp_enqueue_script('cp-scripts');

}