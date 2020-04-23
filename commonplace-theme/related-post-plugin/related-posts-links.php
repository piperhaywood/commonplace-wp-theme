<?php
/**
 * Widget and shortcode template: links template.
 *
 * This template is used by the plugin: Related Posts by Taxonomy.
 *
 * plugin:        https://wordpress.org/plugins/related-posts-by-taxonomy
 * Documentation: https://keesiemeijer.wordpress.com/related-posts-by-taxonomy/
 *
 * Only edit this file after you've copied it to your (child) theme's related-post-plugin folder.
 * See: https://keesiemeijer.wordpress.com/related-posts-by-taxonomy/templates/
 *
 * @package Related Posts by Taxonomy
 * @since 0.1
 *
 * The following variables are available:
 *
 * @var array $related_posts Array with related post objects or related post IDs.
 *                           Empty array if no related posts are found.
 * @var array $rpbt_args     Widget or shortcode arguments.
 */

?>

<?php
/**
 * Note: global $post; is used before this template by the widget and the shortcode.
 */
?>

<?php if ( $related_posts ) : ?>
  <?php echo cp_get_list($related_posts); ?>
<?php endif ?>

<?php
/**
 * Note: wp_reset_postdata(); is used after this template by the widget and the shortcode.
 */
?>
