<?php

add_action('after_setup_theme', 'cp_start_cleanup');
function cp_start_cleanup() {
  add_action('init', 'cp_cleanup_head');
  add_filter('the_generator', 'cp_remove_rss_version');
  add_filter('gallery_style', 'cp_gallery_style');
  add_filter('allowed_block_types', 'cp_allowed_block_types');
}

function cp_cleanup_head() {
  remove_action ('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
  add_filter('style_loader_src', 'cp_alter_wp_ver_css_js', 9999);
  add_filter('script_loader_src', 'cp_alter_wp_ver_css_js', 9999);
}

function cp_allowed_block_types($allowed_blocks) {
  return array(
    'core/paragraph',
    'core/image',
    'core/heading',
    'core/gallery',
    'core/list',
    'core/quote',
    'core/audio',
    'core/file',
    'core/video',
    'core/code',
    'core/freeform',
    'core/html',
    'core/preformatted',
    'core/pullquote',
    'core/buttons',
    'core/media-text',
    'core/more',
    'core/nextpage',
    'core/separator',
    'core/spacer',
    'core/shortcode',
    'core/archives',
    'core/categories',
    'core/latest-posts',
    'core/search',
    'core/embed',
    'core-embed/youtube',
    'core-embed/soundcloud',
    'core-embed/vimeo',
    'core-embed/twitter',
    'core-embed/spotify'
  );
}

function cp_remove_rss_version() { return ''; }

function cp_gallery_style($css) {
  return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}

// Add the theme version to the css and js, not the WP version
function cp_alter_wp_ver_css_js($src) {
  if (strpos($src, 'ver=')) {
    $version = cp_get_theme_version();
    $src = remove_query_arg('ver', $src);
    $src = add_query_arg('ver', $version, $src);
  }
  return $src;
}

// Add loading attribute to images
add_filter( 'the_content', 'cp_lazyload_content_images' );
function cp_lazyload_content_images( $content ) {
  if ( ! preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
    return $content;
  }
  foreach ( $matches[0] as $image ) {
    if ( false === strpos( $image, ' loading=' ) ) {
      $content = str_replace( $image, preg_replace( '/>/', 'loading="lazy">', $image ), $content);
    }
  }
  return $content;
}