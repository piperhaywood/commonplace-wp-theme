<main id="main" class="main" role="main" data-post-browser="archive">
  <?php
  global $wp_query;
  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-rendered list HTML is escaped by its row renderer.
  echo cp_get_list( $wp_query );
  $next_list_page = get_next_posts_page_link( $wp_query->max_num_pages );
  get_template_part( 'load-more', null, array( 'next' => $next_list_page ? add_query_arg( 'cp_view', 'list', $next_list_page ) : '' ) );
  ?>
</main>
