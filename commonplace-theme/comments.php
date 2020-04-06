<?php
/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
  return;
}
?>

<div id="comments" class="comments<?php echo have_comments() ? ' comments--has-comments' : ''; ?>">

  <?php if ( have_comments() ) : ?>
    <div class="comments__section">
      <details>
        <summary>
          <?php
          printf( _x( 'View discussion <span class="comments__count" aria-hidden="true">%s</span>', 'comments title', 'notebook-ph' ), get_comments_number() );
          ?>
        </summary>
      
        <ol class="comments__comment-list comment-list">
          <?php
            wp_list_comments( array(
              'avatar_size' => 100,
              'style'       => 'ol',
              'short_ping'  => true,
              'callback'    => 'nph_comment',
              'reply_text'  => __( 'Reply', 'notebook-ph' ),
            ) );
          ?>
        </ol>
        <?php the_comments_pagination( array(
          'prev_text' => '<span class="screen-reader-text">' . __( 'Previous', 'notebook-ph' ) . '</span>',
          'next_text' => '<span class="screen-reader-text">' . __( 'Next', 'notebook-ph' ) . '</span>',
        ) ); ?>
        <?php // If comments are closed and there are comments, let's leave a little note, shall we?
        if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
          <p class="no-comments"><?php _e( 'Discussion is closed.', 'notebook-ph' ); ?></p>
        <?php endif; ?>
      </details>
    </div>
  <?php endif; // Check for have_comments(). ?>

  <?php if (comments_open()) : ?>
    <div class="comments__section">
      <details id="reply">
        <?php $discussion = 'Leave a reply'; ?>
        <?php $discussion = have_comments() ? $discussion . ' / view discussion <span class="comments__count">%s</span>' : $discussion; ?>
        <summary>
          <?php _e( 'Leave a reply', 'notebook-ph' ); ?>
        </summary>
        <?php comment_form(); ?>
      </details>
    </div>
  <?php endif; ?>

</div>
