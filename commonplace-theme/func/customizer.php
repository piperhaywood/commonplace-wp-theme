<?php

add_action('customize_register', 'cp_customize_register');
function cp_customize_register($wp_customize) {

  $wp_customize->add_section(
    'cp_display_options',
    array(
      'title'     => __('Options', 'commonplace'),
      'priority'  => 200
    )
  );

  $wp_customize->add_setting(
    'cp_display_authors',
    array(
      'default'    =>  'true',
      'transport'  =>  'postMessage'
    )
  );

  $wp_customize->add_control(
    'cp_display_authors',
    array(
      'section'   => 'cp_display_options',
      'label'     => __('Display Author?', 'commonplace'),
      'type'      => 'checkbox'
    )
  );

}

add_action('wp_head', 'cp_customizer_css');
function cp_customizer_css() {
  ?>
  <style type="text/css">
    <?php if (false === get_theme_mod('cp_display_authors')) { ?>
        .post__author { display: none; }
    <?php } ?>
  </style>
  <?php
}

add_action('customize_preview_init', 'cp_customizer_live_preview');
function cp_customizer_live_preview() {
  $version = cp_get_theme_version();

  wp_enqueue_script(
    'cp-theme-customizer',
    get_template_directory_uri() . '/customizer.js',
    array('jquery', 'customize-preview'),
    $version,
    true
  );
}
