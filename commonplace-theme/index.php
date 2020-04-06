<?php get_header(); ?>

<main id="main" class="main" role="main">
<section class="articles"<?php if (!is_singular()) : ?> role="feed"<?php endif; ?>>
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article <?php post_class(array('post', 'article', 'js-article')); ?>>
        <div class="article__inner">
          <header class="post__header">
            <h1 class="p-name post__title">
              <?php echo cp_title(false); ?>
            </h1>
            <?php if (get_post_type() == 'post') : ?>
              <time class="dt-published post__time" datetime="<?php echo cp_date(true, false); ?>">
                <a class="u-url" href="<?php the_permalink(); ?>" aria-label="<?php printf(esc_html__('View post published %s', 'commonplace'), get_the_date('l, j F Y')); ?>">
                  <?php echo get_the_date('l, j F Y'); ?>
                </a>
              </time>
              <?php if (!is_single() && is_sticky()) : ?><span aria-label="<?php _e('Pinned', 'commonplace'); ?>">📌</span><?php endif; ?>
              <span class="post__author"><?php printf(_x('by %s', 'authorship', 'commonplace'), sprintf(
                '<a class="p-author h-card" href="%1$s" title="%2$s" rel="author">%3$s</a>',
                esc_url( get_author_posts_url( get_the_author_meta('ID'), get_the_author_meta('user_nicename') ) ),
                esc_attr( sprintf( __( 'Posts by %s', 'commonplace' ), get_the_author() ) ),
                get_the_author()
              )); ?></span>
            <?php endif; ?>
          </header>
          
          <section class="prose e-content">
            <?php the_content(esc_html__('Read more', 'commonplace')); ?>
          </section>

          <footer class="post__footer">
            <?php wp_link_pages(); ?>
            <?php if (!is_page()) : ?>
              <div class="post__meta">
                <ul class="post__tags">
                  <?php $format = get_post_format(); ?>
                  <?php if ($format != false) : ?>
                    <li class="post__tag">
                      <a aria-label="<?php printf(esc_html__('Format: %s', 'commonplace'), $format); ?>" href="<?php echo get_post_format_link($format); ?>"><span class="term term--post_format"><?php echo $format; ?></span></a><span class="separator">, </span>
                    </li>
                  <?php endif; ?>
                  <?php $cats = wp_get_post_categories($post->ID, array(
                    'fields' => 'all',
                    'orderby' => 'count',
                    'order' => 'DESC'
                  )); ?>
                  <?php $tags = wp_get_post_tags($post->ID, array(
                    'orderby' => 'count',
                    'order' => 'DESC'
                  )); ?>
                  <?php $terms = array_merge($cats, $tags); ?>
                  <?php if (!empty($terms)) : ?>
                    <?php foreach($terms as $term) : ?>
                      <?php $tax = get_taxonomy($term->taxonomy); ?>
                      <li class="post__tag">
                        <a aria-label="<?php printf(esc_html__('%s: %s', 'commonplace'), $tax->labels->singular_name, $term->name); ?>" href="<?php echo get_tag_link($term->term_id); ?>"><span class="term term--<?php echo $term->taxonomy; ?>"><?php echo $term->name; ?></span></a><span class="separator" aria-hidden="true">, </span>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>
              </div>
            <?php endif; ?>
            <?php if (is_singular()) : ?>
              <?php if (comments_open() || get_comments_number()) : ?>
                <?php comments_template(); ?>
              <?php endif; ?>
            <?php endif; ?>
            <a href="#top" class="visuallyhidden button"><?php _e('Back to top', 'commonplace'); ?></a>

          </footer>
        </div>
      </article>
    <?php endwhile; ?>

  <?php else : ?>
    <article class="post article type-<?php echo get_post_type(); ?>" style="--color:<?php echo cp_get_hsl(); ?>;">
      <div class="article__inner">

        <div class="prose e-content">
          <p><?php _e('Nothing found!', 'commonplace'); ?></p>
          <?php get_search_form(); ?>
          <?php echo do_shortcode('[notebookindex count="2"]'); ?>
        </div>

      </div>
      <footer class="post__footer">
      </footer>
    </article>

  <?php endif; ?>
  </section>
</main>

<?php get_footer(); ?>