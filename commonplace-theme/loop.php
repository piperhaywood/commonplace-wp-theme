<main id="main" class="main" role="main">
	<section class="articles"<?php if ( ! is_singular() ) :
		?> role="feed"<?php endif; ?>>
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
		<article <?php post_class( array( 'post', 'article' ) ); ?>>
			<header class="post-header">
			<?php $hide_title = false !== get_post_format() || strpos( get_page_template_slug(), 'no-title' ) ? true : false; ?>
			<?php if ( 'post' === get_post_type() ) : ?>
				<p class="post-time">
				<span class="visuallyhidden">Published </span>
				<time class="dt-published" datetime="<?php echo esc_attr( cp_date( true, false ) ); ?>"><a class="u-url" href="<?php the_permalink(); ?>" aria-label="
				<?php
				/* translators: %s: Post publication date. */
				echo esc_attr( sprintf( __( 'View post published %s', 'commonplace' ), get_the_date() ) );
				?>
				"><?php echo esc_html( get_the_date() ); ?></a></time> <span class="post-author">
				<?php
				printf(
					/* translators: %s: Link to the post author. */
					wp_kses_post( _x( 'by %s', 'authorship', 'commonplace' ) ),
					sprintf(
						'<a class="p-author h-card" href="%1$s" title="%2$s" rel="author">%3$s</a>',
						esc_url( get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) ) ),
						/* translators: %s: Author name. */
						esc_attr( sprintf( __( 'Posts by %s', 'commonplace' ), get_the_author() ) ),
						esc_html( get_the_author() )
					)
				);
				?>
				</span>
				<?php
				if ( ! is_single() && is_sticky() ) :
					?>
										<span aria-label="<?php esc_attr_e( 'Pinned', 'commonplace' ); ?>"> ◆</span><?php endif; ?>
				<?php if ( is_singular() && $hide_title && '' !== get_the_title() ) : ?>
					<span aria-hidden="true">&mdash; <?php the_title(); ?></span>
				<?php endif; ?>
				</p>
			<?php endif; ?>
			<?php $article_title = cp_title( false, $hide_title ); ?>
			<?php if ( $article_title && '' !== $article_title ) : ?>
				<<?php echo is_singular() ? 'h1' : 'h2'; ?> class="p-name post-title <?php echo $hide_title ? 'visuallyhidden' : ''; ?>">
				<?php echo wp_kses_post( $article_title ); ?>
				</<?php echo is_singular() ? 'h1' : 'h2'; ?>>
			<?php endif; ?>
			</header>

			<section class="prose e-content">
			<?php the_content( esc_html__( 'Read more', 'commonplace' ) ); ?>
			</section>
			<footer class="post-footer">
			<?php wp_link_pages(); ?>

			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="post-meta meta">
				<p>
					<?php
					$post_terms = get_terms(
						array(
							'taxonomy'   => array( 'post_tag', 'category', 'post_format' ),
							'object_ids' => get_the_id(),
						)
					);
					?>
					<?php if ( $post_terms ) : ?>
						<?php foreach ( $post_terms as $slug => $post_term ) : ?>
						<span class="term<?php echo (int) get_option( 'default_category' ) === (int) $post_term->term_id ? ' term--default' : ''; ?> term--<?php echo esc_attr( $post_term->taxonomy ); ?>">
							<?php
							/* translators: 1: Term name, 2: Number of posts. */
							$term_label = sprintf( _n( '%1$s, %2$s post', '%1$s, %2$s posts', $post_term->count, 'commonplace' ), $post_term->name, $post_term->count );
							?>
						<a class="term-link" aria-label="<?php echo esc_attr( $term_label ); ?>" href="<?php echo esc_url( get_tag_link( $post_term->term_id ) ); ?>"><?php echo esc_html( $post_term->name ); ?></a><span class="separator" aria-hidden="true">,</span>
						</span>
					<?php endforeach; ?>
					<?php endif; ?>
				</p>
				</div>
			<?php endif; ?>
			<?php if ( is_singular() ) : ?>
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			<?php endif; ?>
			<div class="container">
				<a href="#top" class="visuallyhidden button"><?php esc_html_e( 'Back to top', 'commonplace' ); ?></a>
			</div>

			</footer>
		</article>
		<?php endwhile; ?>

	<?php else : ?>
		<article class="post article type-<?php echo esc_attr( get_post_type() ); ?>" style="--color:<?php echo esc_attr( cp_get_hsl() ); ?>;">

			<section class="prose e-content">
			<p><?php esc_html_e( 'Nothing found!', 'commonplace' ); ?></p>
			<?php get_search_form(); ?>
			<?php echo do_shortcode( '[notebookindex count="2"]' ); ?>
			</section>

		</article>

	<?php endif; ?>
	</section>
</main>
