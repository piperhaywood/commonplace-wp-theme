<?php

function cp_gutenberg_scripts() {
  // wp_enqueue_script(
  //   'cp-editor',
  //   get_stylesheet_directory_uri() . '/assets/js/editor.js',
  //   array( 'wp-blocks', 'wp-dom' ),
  //   filemtime( get_stylesheet_directory() . '/assets/js/editor.js' ),
  //   true
  // );
  // wp_register_style(
  //   'cp-editor',
  //   get_stylesheet_directory_uri() . '/assets/editor.css',
  //   array( ),
  //   filemtime( get_stylesheet_directory() . '/assets/editor.css' )
  // );

	
}
add_action( 'enqueue_block_editor_assets', 'cp_gutenberg_scripts' );

function cp_aside_block_init() {

  // automatically load dependencies and version
  $asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');

  wp_register_script(
    'fancy-custom-block-block-editor',
    plugins_url( 'build/index.js', __FILE__ ),
    $asset_file['dependencies'],
    $asset_file['version']
  );

  wp_register_style(
    'fancy-custom-block-block-editor',
    plugins_url( 'editor.css', __FILE__ ),
    array( ),
    filemtime( plugin_dir_path( __FILE__ ) . 'editor.css' )
  );

  wp_register_style(
    'fancy-custom-block-block',
    plugins_url( 'style.css', __FILE__ ),
    array( ),
    filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
  );

  register_block_type( 'fancy-block-plugin/fancy-custom-block', array(
    'editor_script' => 'fancy-custom-block-block-editor',
    'editor_style'  => 'fancy-custom-block-block-editor',
    'style'         => 'fancy-custom-block-block',
  ));
}

// add_action( 'init', 'cp_aside_block_init' );