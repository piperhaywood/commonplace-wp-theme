<?php

add_action('after_setup_theme', 'cp_theme_support'); 
function cp_theme_support() {
  load_theme_textdomain('commonplace', get_template_directory() . '/languages');
  add_theme_support('menus');
  add_theme_support('automatic-feed-links');
  add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio', 'chat'));
  add_theme_support('post-thumbnails'); 
}
