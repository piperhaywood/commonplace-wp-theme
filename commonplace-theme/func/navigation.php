<?php

add_action( 'wp_footer', 'cp_related_posts', 2 );
function cp_related_posts() {
  global $post;
  if ( shortcode_exists( 'related_posts_by_tax' ) && is_singular( 'post' ) && is_main_query() ) {
    echo do_shortcode( '[related_posts_by_tax post_id="' . $post->ID . '" taxonomies="post_tag" title="' . esc_attr__( 'See also', 'commonplace' ) . '" public_only="true"]' );
  }
}
