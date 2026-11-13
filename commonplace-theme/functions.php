<?php

require_once 'func/cleanup.php';
require_once 'func/customizer.php';
require_once 'func/enqueue.php';
// Gutenberg block experiments in func/gutenberg.php are not loaded.
require_once 'func/navigation.php';
require_once 'func/theme-support.php';
require_once 'func/widgets.php';
require_once 'func/related-posts-by-taxonomy/related-posts-by-taxonomy.php';

add_shortcode( 'notebooksearch', 'get_search_form' );

function cp_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
}

function cp_excerpt_as_title() {
	return 10;
}

add_filter( 'excerpt_more', 'cp_excerpt_more' );
function cp_excerpt_more() {
	return '&hellip;';
}

add_filter( 'document_title_parts', 'filter_title_part' );
function filter_title_part( $title ) {
	if ( ! is_feed() && '' === $title['title'] ) {
		global $post;
		add_filter( 'excerpt_length', 'cp_excerpt_as_title' );
		$excerpt = get_the_excerpt();
		remove_filter( 'excerpt_length', 'cp_excerpt_as_title' );
		$title['title'] = $excerpt;
	}
	return $title;
}

add_shortcode( 'notebookindex', 'notebook_index' );
function notebook_index( $attr ) {
	$a          = shortcode_atts(
		array(
			'taxonomy' => 'post_tag, category, post_format',
			'years'    => 'true',
			'count'    => '1',
		),
		$attr
	);
	$taxonomy   = explode( ',', str_replace( ', ', ',', $a['taxonomy'] ) );
	$show_years = ( 'true' === $a['years'] );
	$count      = intval( $a['count'] );
	return get_notebook_index( $taxonomy, $show_years, $count );
}

add_shortcode( 'notebooklist', 'notebook_list' );
function notebook_list() {
	return cp_get_list();
}

function get_notebook_index( $taxonomy, $show_years, $count ) {
	// Set up the index groups
	$groups = array();

	// Add the year archives to the index
	if ( $show_years ) {
		$years = cp_get_years_array();
		if ( $years ) {
			$args = array(
				'posts_per_page' => -1,
				'post_type'      => 'post',
				'fields'         => 'ids',
			);
			foreach ( $years as $year ) {
				$args['date_query'] = array(
					array( 'year' => $year ),
				);
				$posts              = get_posts( $args );
				$year_count         = count( $posts );
				if ( $year_count >= $count ) {
					$groups = cp_add_to_index(
						$groups,
						array(
							'name'  => $year,
							'url'   => get_year_link( $year ),
							'count' => $year_count,
							'slug'  => $year,
							'type'  => 'year',
							/* translators: 1: Archive year, 2: Number of posts. */
							'aria'  => sprintf( esc_html__( 'Year: %1$s, %2$s posts', 'notebook-ph' ), $year, $year_count ),
						)
					);
				}
			}
		}
	}

	// Add the terms to the index
	$terms = get_terms(
		array(
			'taxonomy' => $taxonomy,
		)
	);
	if ( $terms && is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( $term->count >= $count ) {
				// NOTE strip "post-format-" from slug, for some reason this gets included
				$slug   = str_replace( 'post-format-', '', $term->slug );
				$tax    = get_taxonomy( $term->taxonomy );
				$groups = cp_add_to_index(
					$groups,
					array(
						'name'  => $term->name,
						'url'   => get_tag_link( $term->term_id ),
						'count' => $term->count,
						'slug'  => $slug,
						'type'  => $term->taxonomy,
						/* translators: 1: Taxonomy label, 2: Term name, 3: Number of posts. */
						'aria'  => sprintf( esc_html__( '%1$s: %2$s, %3$s posts', 'notebook-ph' ), $tax->labels->singular_name, $term->name, $term->count ),
					)
				);
			}
		}
	}

	// Move # and ? items to end of index
	if ( ! empty( $groups ) ) {
		ksort( $groups );
		$end_keys = array( '#', '?' );
		foreach ( $end_keys as $key ) {
			if ( array_key_exists( $key, $groups ) ) {
				$unset_group = $groups[ $key ];
				unset( $groups[ $key ] );
				$groups[ $key ] = $unset_group;
			}
		}
	}

	ob_start(); ?>
	<?php if ( ! empty( $groups ) ) : ?>
		<div class="termindex">
		<?php foreach ( $groups as $char => $terms ) : ?>
			<?php ksort( $terms ); ?>
			<?php $label = '#' === $char ? esc_attr__( 'a number', 'notebook-ph' ) : $char; ?>
			<?php
			/* translators: %s: First character of the grouped terms. */
			$label = sprintf( esc_attr__( 'Terms beginning with %s', 'notebook-ph' ), $label );
			?>
			<div class="termgroup">
			<h2 aria-label="<?php echo esc_attr( $label ); ?>"><?php echo wp_kses_post( apply_filters( 'the_title', $char ) ); ?></h2>
			<ol>
			<?php foreach ( $terms as $slug => $term ) : ?>
				<li>
				<a aria-label="<?php echo esc_attr( $term['aria'] ); ?>" href="<?php echo esc_url( $term['url'] ); ?>"><span class="term term--<?php echo esc_attr( $term['type'] ); ?>"><?php echo esc_html( $term['name'] ); ?></span>&nbsp;<span class="term__count"><?php echo esc_html( $term['count'] ); ?></span></a>
				</li>
			<?php endforeach; ?>
			</ol>
			</div>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}


function cp_pagetype( $display = true ) {
	$class = is_singular() ? 'permalink' : '';
	if ( true === $display ) {
		echo esc_attr( $class );
	} else {
		return $class;
	}
}

function cp_permalink( $display = true, $prefix = '', $link_text = '', $suffix = '' ) {
	$return = '';

	if ( ! is_singular() ) {
		$return = $prefix . '<a href="' . esc_url( get_the_permalink() ) . '">' . $link_text . '</a>' . $suffix;
	}

	if ( true === $display ) {
		echo wp_kses_post( $return );
	} else {
		return $return;
	}
}

function cp_editlink( $display = true, $prefix = '', $link_text = '', $suffix = '' ) {
	$return = '';

	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		$return = $prefix . '<a href="' . esc_url( get_edit_post_link() ) . '">' . $link_text . '</a>' . $suffix;
	}

	if ( true === $display ) {
		echo wp_kses_post( $return );
	} else {
		return $return;
	}
}

function cp_date( $format = false, $display = true ) {
	if ( ! $format ) {
		$date = get_the_date();
	} else {
		$str  = 'Y-m-d H:i:s';
		$date = get_the_date( $str );
		$date = DateTime::createFromFormat( $str, $date );
		$date = $date->format( DateTime::RFC3339 );
	}

	if ( true === $display ) {
		echo esc_html( $date );
	} else {
		return $date;
	}
}

function cp_title( $display = true, $is_hidden = false ) {
	$title = get_the_title();
	if ( empty( $title ) ) {
		return;
	}

	if ( ! is_singular() && ! $is_hidden ) {
		$title = '<a href="' . esc_url( get_the_permalink() ) . '">' . $title . '</a>';
	}

	if ( true === $display ) {
		echo wp_kses_post( $title );
	} else {
		return $title;
	}
}

function cp_author( $display = true ) {
	$return = '';

	$id   = get_the_author_meta( 'ID' );
	$name = get_the_author_meta( 'display_name' );

	if ( $id && $name ) {
		$return .= '<a href="' . esc_url( get_author_posts_url( $id ) ) . '">' . esc_html( $name ) . '</a>';
	}

	if ( true === $display ) {
		echo wp_kses_post( $return );
	} else {
		return $return;
	}
}

function cp_archivedesc( $display = true, $prefix = '', $suffix = '' ) {
	global $wp_query;
	$total  = $wp_query->found_posts;
	$return = '';
	if ( is_tag() ) {
		$return .= tag_description();
	} elseif ( is_category() ) {
		$return .= category_description();
	} elseif ( is_author() ) {
		$return .= '<p>' . get_the_author_meta( 'description' ) . '</p>';
	} elseif ( is_home() && ! is_paged() ) {
		$intro = get_theme_mod( 'cp_blog_intro' );
		if ( $intro ) {
			$return .= '<p>' . strip_tags( $intro, '<em><a><img><br>' ) . '</p>';
		}
	}

	$return = ! empty( $return ) ? $prefix . $return . $suffix : '';

	if ( true === $display ) {
		echo wp_kses_post( $return );
	} else {
		return $return;
	}
}

function cp_get_theme_version() {
	$version = '1';
	$theme   = wp_get_theme();
	if ( ! $theme->exists() ) {
		return;
	}
	$version = $theme->get( 'Version' );
	return $version;
}

function cp_archive_str() {
	global $wp_query;
	$paged = get_query_var( 'paged' );
	if ( is_page() || ( is_front_page() && 0 === (int) $paged ) ) {
		return false;
	}
	if ( is_singular() ) {
		return false;
	}
	if ( is_404() ) {
		return __( 'Page not found [404 error]', 'commonplace' );
	}
	$paged = get_query_var( 'paged' );
	$page  = $paged > 0 ? _x( 'page ', 'paged', 'commonplace' ) . $paged : false;
	$page  = $page && ! is_front_page() ? ', ' . $page : $page;
	$text  = '';
	if ( is_tax( 'post_format' ) ) {
		$var  = get_post_format();
		$text = __( 'format', 'commonplace' ) . ' / ' . $var;
	} elseif ( is_tag() ) {
		$var  = single_tag_title( '', false );
		$text = __( 'tag', 'commonplace' ) . ' / ' . $var;
	} elseif ( is_category() ) {
		$var  = single_cat_title( '', false );
		$text = __( 'category', 'commonplace' ) . ' / ' . $var;
	} elseif ( is_search() ) {
		$query = get_query_var( 's' );
		/* translators: %s: Search query. */
		$text = sprintf( __( 'results / &ldquo;%s&rdquo;', 'commonplace' ), $query );
	} elseif ( is_author() ) {
		$var  = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
		$text = __( 'author', 'commonplace' ) . ' / ' . $var;
	} elseif ( is_month() ) {
		$var  = get_the_date( 'F Y' );
		$text = $var;
	} elseif ( is_year() ) {
		$var  = get_the_date( 'Y' );
		$text = $var;
	}

	$title = $text . $page;

	return $title;
}

function cp_archive_label() {
	global $wp_query;
	if ( is_singular() ) {
		return get_the_title();
	}
	if ( is_404() ) {
		return __( 'Page not found [404 error]', 'commonplace' );
	}
	$paged = get_query_var( 'paged' );
	/* translators: %s: Page number. */
	$page  = $paged > 0 ? ', ' . sprintf( __( 'page %s', 'commonplace' ), $paged ) : '';
	$label = false;
	if ( is_front_page() ) {
		$label = get_bloginfo( 'name' );
	} elseif ( is_tax( 'post_format' ) ) {
		$var = get_post_format();
		/* translators: %s: Post format. */
		$label = sprintf( __( 'Format: %s', 'commonplace' ), $var );
	} elseif ( is_tag() ) {
		$var = single_tag_title( '', false );
		/* translators: %s: Tag name. */
		$label = sprintf( __( 'Tag: %s', 'commonplace' ), $var );
	} elseif ( is_category() ) {
		$var = single_cat_title( '', false );
		/* translators: %s: Category name. */
		$label = sprintf( __( 'Category: %s', 'commonplace' ), $var );
	} elseif ( is_search() ) {
		$query = get_query_var( 's' );
		/* translators: %s: Search query. */
		$label = sprintf( __( 'You searched for &ldquo;%s&rdquo;', 'commonplace' ), $query );
	} elseif ( is_author() ) {
		$var = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
		/* translators: %s: Author name. */
		$label = sprintf( __( 'Author: %s', 'commonplace' ), $var );
	} elseif ( is_month() ) {
		$var = get_the_date( 'F Y' );
		/* translators: %s: Archive month and year. */
		$label = sprintf( __( 'Month: %s', 'commonplace' ), $var );
	} elseif ( is_year() ) {
		$var = get_the_date( 'Y' );
		/* translators: %s: Archive year. */
		$label = sprintf( __( 'Year: %s', 'commonplace' ), $var );
	}

	/* translators: %s: Number of posts. */
	$label = $label . $page . ' &mdash; ' . sprintf( _n( '%s post', '%s posts', $wp_query->found_posts, 'commonplace' ), $wp_query->found_posts );

	return $label;
}

function cp_get_copyright() {
	$copy = '';
	if ( is_single() ) {
		$copy .= get_the_date( 'Y' );
	} else {
		$args        = array(
			'order'          => 'ASC',
			'posts_per_page' => 1,
			'post_type'      => 'any',
			'post_status'    => 'publish,private,draft',
		);
		$posts_array = get_posts( $args );
		if ( ! empty( $posts_array ) ) {
			$copy .= get_the_date( 'Y', $posts_array[0]->ID ) . '&ndash;';
		}
		$copy .= gmdate( 'Y' );
	}
	$copy = '&copy; ' . $copy;
	return $copy;
}

function cp_tag_opacity( $tag, $max_opacity = 1, $min_opacity = .1 ) {
	$opacity = 1;
	$args    = array(
		'orderby' => 'count',
		'number'  => 1,
	);
	$tags    = get_tags( $args );
	$min_in  = $tags[0]->count;
	$tags    = array_reverse( $tags );
	$max_in  = $tags[0]->count;
	$max_in  = 8;
	$count   = $tag->count > $max_in ? $max_in : $tag->count;

	$percentage = ( $count - $min_in ) / ( $max_in - $min_in );

	$opacity = $percentage * ( $max_opacity - $min_opacity ) + $min_opacity;
	return round( $opacity, 4 );
}

function cp_map_hue( $timezone, $lat, $unix ) {
	$tz       = new DateTimeZone( $timezone );
	$datetime = DateTime::createFromFormat( 'U', $unix );
	$datetime->setTimezone( $tz );
	$dayofyear = $datetime->format( 'z' );
	$june_dt   = DateTime::createFromFormat( 'Y-m-d', $datetime->format( 'Y' ) . '-06-22' );
	$june_dt->setTimezone( $tz );
	$june_doy = $june_dt->format( 'z' );
	$last_dt  = DateTime::createFromFormat( 'Y-m-d', $datetime->format( 'Y' ) . '-12-31' );
	$last_dt->setTimezone( $tz );
	$last_doy = $last_dt->format( 'z' );
	$offset   = $lat > 0 ? 52 : 200;
	if ( $dayofyear < $june_doy ) {
		$diff = $dayofyear + ( $last_doy - $june_doy );
	} else {
		$diff = $dayofyear - $june_doy;
	}

	$return = 0 - $diff;
	$return = $return + $offset;
	$return = $return < 0 ? $return + 360 : $return;
	return $return;
}

function cp_get_hsl( $hsl_post = false ) {
	if ( ! $hsl_post ) {
		return 'hsl(1, 100%, 0%)';
	}
	$unix = get_the_date( 'U', $hsl_post->ID );
	$hue  = cp_map_hue( 'Europe/London', 51.567592, $unix );
	$hsl  = sprintf( 'hsl(%s, 70%%, 50%%)', $hue );
	return $hsl;
}

function cp_group_posts_by_year() {
	global $post;
	$years = array();
	$posts = get_posts(
		array(
			'posts_per_page' => -1,
		)
	);
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Template tags require the global post; reset below.
	foreach ( $posts as $post ) {
		setup_postdata( $post );
		$year = get_the_date( 'Y' );
		if ( ! isset( $years[ $year ] ) ) {
			$years[ $year ] = array();
		}
		$years[ $year ][] = array(
			'title'     => get_the_title(),
			'permalink' => get_the_permalink(),
			'hsl'       => cp_get_hsl( $post ),
			'id'        => get_the_id( $post ),
		);
	}
	wp_reset_postdata();
	return $years;
}

function cp_get_years_array() {
	global $wpdb;
	$result = array();
	$years  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT YEAR(post_date) FROM {$wpdb->posts} WHERE post_status = %s GROUP BY YEAR(post_date) ORDER BY YEAR(post_date) DESC",
			array( 'publish' )
		),
		ARRAY_N
	);
	if ( is_array( $years ) && count( $years ) > 0 ) {
		foreach ( $years as $year ) {
			$result[] = $year[0];
		}
	}
	return $result;
}

function cp_add_to_index( $groups, $args ) {
	$first_char = strtoupper( $args['name'][0] );
	if ( is_numeric( $first_char ) ) {
		$first_char = '#';
	} elseif ( '&' === $first_char || 'UTF-8' === mb_detect_encoding( $first_char ) ) {
		$first_char = '?';
	}
	$groups[ $first_char ][ $args['slug'] ] = $args;
	return $groups;
}

function cp_get_list_item( $post = false ) {
	if ( ! $post ) {
		global $post;
	}
	?>
	<?php $format = get_post_format( $post ); ?>
	<?php $hsl = cp_get_hsl( $post, 'thumbnail' ); ?>
	<?php $images = get_attached_media( 'image' ); ?>
	<?php $title = get_the_title( $post ); ?>
	<li class="post-index__post-item" style="--color:<?php echo esc_attr( $hsl ); ?>;">
	<?php if ( has_post_thumbnail() ) : ?>
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates and escapes the thumbnail HTML, including theme CSS properties.
		echo cp_list_thumb( $post, 'thumbnail' );
		?>
	<?php endif; ?>
	<div class="post-item">
		<a class="post-item__link" href="<?php the_permalink(); ?>">
		<time class="post-item__time" datetime="<?php echo esc_attr( cp_date( true, false ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
		<?php
		if ( ! is_single() && is_sticky() ) :
			?>
			<span aria-label="<?php esc_attr_e( 'Pinned', 'commonplace' ); ?>"> ◆</span><?php endif; ?>
		</a>
		<a class="post-item__link" href="<?php the_permalink(); ?>">
		<span class="post-item__text"><?php echo wp_kses_post( '' === $title ? get_the_excerpt() : $title ); ?></span>
		</a>
		<?php
		$terms = get_terms(
			array(
				'taxonomy'   => array( 'post_tag' ),
				'object_ids' => get_the_id(),
			)
		);
		?>
		<?php if ( $terms ) : ?>
		<ul class="post-item__terms">
			<?php foreach ( $terms as $slug => $term ) : ?>
				<?php if ( (int) get_option( 'default_category' ) !== (int) $term->term_id ) : ?>
				<li class="term term--<?php echo esc_attr( $term->taxonomy ); ?>">
					<?php
					/* translators: 1: Term name, 2: Number of posts. */
					$term_label = sprintf( _n( '%1$s, %2$s post', '%1$s, %2$s posts', $term->count, 'commonplace' ), $term->name, $term->count );
					?>
				<a class="term-link" aria-label="<?php echo esc_attr( $term_label ); ?>" href="<?php echo esc_url( get_tag_link( $term->term_id ) ); ?>"><?php echo esc_html( $term->name ); ?></a><span class="separator" aria-hidden="true">,</span>
				</li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
	</li>
	<?php
}

function cp_get_list_arr( $posts = false ) {
	global $post;
	if ( ! $posts ) {
		$posts = get_posts( array( 'posts_per_page' => -1 ) );
	}
	if ( $posts ) {
		add_filter( 'excerpt_length', 'cp_excerpt_as_title' );
		ob_start();
		?>
	<ul class="post-index">
		<?php // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Set up each post for template tags; reset below.
		foreach ( $posts as $post ) :
			?>
			<?php setup_postdata( $post ); ?>
			<?php cp_get_list_item( $post ); ?>
			<?php wp_reset_postdata(); ?>
		<?php endforeach; ?>
	</ul>
		<?php
		remove_filter( 'excerpt_length', 'cp_excerpt_as_title' );
		return ob_get_clean();
	}
}

function cp_get_list( $posts = false ) {
	global $post;
	if ( ! $posts ) {
		$posts = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
			)
		);
	}
	if ( $posts ) {
		add_filter( 'excerpt_length', 'cp_excerpt_as_title' );
		ob_start();
		?>
	<ul class="post-index">
		<?php $orig = $post; ?>
		<?php if ( $posts->have_posts() ) : ?>
			<?php while ( $posts->have_posts() ) : ?>
				<?php $posts->the_post(); ?>
				<?php cp_get_list_item(); ?>
		<?php endwhile; ?>
		<?php endif; ?>
		<?php // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore the original global post after the custom loop.
		$post = $orig;
		?>
	</ul>
		<?php
		remove_filter( 'excerpt_length', 'cp_excerpt_as_title' );
		return ob_get_clean();
	}
}

function max_img_width( $img_width, $img_height, $ideal_area ) {
	$max_width = round( $img_width * sqrt( $ideal_area / ( $img_width * $img_height ) ) );
	return $max_width;
}

function cp_list_thumb( $post, $size = 'thumbnail' ) {
	add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
	$rotations = array( wp_rand( 10, 20 ), wp_rand( -20, -10 ) );
	$y_offset  = array( wp_rand( 4, 6 ), wp_rand( -6, -4 ) );
	$data      = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
	$style     = '--pos-right:' . wp_rand( 5, 95 ) . '%;--trans-y:' . $y_offset[ array_rand( $y_offset ) ] . 'rem;--rotate:' . $rotations[ array_rand( $rotations ) ] . 'deg;--max-width:' . max_img_width( $data[1], $data[2], 8000 ) / 16 . 'rem;';
	$html      = get_the_post_thumbnail(
		$post,
		$size,
		array(
			'loading'     => 'lazy',
			'style'       => $style,
			'class'       => 'post-item__image',
			'aria-hidden' => 'true',
			'role'        => 'decoration',
		)
	);
	remove_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
	return $html;
}

function cp_comment( $comment, $args, $depth ) {
	if ( 'div' === $args['style'] ) {
		$tag       = 'div';
		$add_below = 'comment';
	} else {
		$tag       = 'li';
		$add_below = 'div-comment';
	}
	?>
	<<?php echo esc_html( $tag ); ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
	<?php
	if ( 'div' !== $args['style'] ) {
		?>
	<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
		<?php
	}
	?>
	<div class="comment-avatar">
		<?php
		if ( 0 !== (int) $args['avatar_size'] ) {
			echo get_avatar( $comment, $args['avatar_size'] );
		}
		?>
	</div>

	<div class="comment-content prose">
		<?php if ( '0' === (string) $comment->comment_approved ) : ?>
		<p><em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.' ); ?></em></p>
		<?php endif; ?>
		<?php comment_text(); ?>
	</div>

	<div class="comment-author vcard">
		<cite class="fn"><?php echo wp_kses_post( get_comment_author_link() ); ?></cite>,
		<span class="comment-meta commentmetadata">
		<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
		<?php
			printf(
				/* translators: 1: Comment date, 2: Comment time. */
				esc_html__( '%1$s at %2$s' ),
				esc_html( get_comment_date() ),
				esc_html( get_comment_time() )
			);
		?>
		</a>
		</span><?php edit_comment_link( __( 'Edit' ), ' / ', '' ); ?>
	</div>

	<div class="reply">
	<?php
		comment_reply_link(
			array_merge(
				$args,
				array(
					'add_below' => $add_below,
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
				)
			)
		);
	?>
	</div>
	<?php
	if ( 'div' !== $args['style'] ) :
		?>
	</div>
		<?php
	endif;
}
