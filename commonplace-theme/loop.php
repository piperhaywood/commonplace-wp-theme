<main id="main" class="main" role="main">
  <section class="articles"<?php if (!is_singular()) : ?> role="feed"<?php endif; ?>>
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(array('post', 'article')); ?>>
          <header class="post-header">
            <?php $hideTitle = get_post_format() != false || strpos(get_page_template_slug(), 'no-title') ? true : false; ?>
            <?php if (get_post_type() == 'post') : ?>
              <p class="post-time">
                <span class="visuallyhidden">Published </span>
                <time class="dt-published" datetime="<?php echo cp_date(true, false); ?>"><a class="u-url" href="<?php the_permalink(); ?>" aria-label="<?php printf(esc_html__('View post published %s', 'commonplace'), get_the_date('l, j F Y')); ?>"><?php echo get_the_date('l, j F Y'); ?></a></time> <span class="post-author"><?php printf(_x('by %s', 'authorship', 'commonplace'), sprintf(
                  '<a class="p-author h-card" href="%1$s" title="%2$s" rel="author">%3$s</a>',
                  esc_url( get_author_posts_url( get_the_author_meta('ID'), get_the_author_meta('user_nicename') ) ),
                  esc_attr( sprintf( __( 'Posts by %s', 'commonplace' ), get_the_author() ) ),
                  get_the_author()
                )); ?></span><?php if (!is_single() && is_sticky()) : ?><span aria-label="<?php _e('Pinned', 'commonplace'); ?>"> ◆</span><?php endif; ?>
                <?php if (is_singular() && $hideTitle && get_the_title() != '') : ?>
                  <span aria-hidden="true">&mdash; <?php the_title(); ?></span>
                <?php endif; ?>  
              </p>
            <?php endif; ?>
            <?php $title = cp_title(false); ?>
            <?php if ($title && $title != '') : ?>
              <<?php echo is_singular() ? 'h1' : 'h2'; ?> class="p-name post-title <?php echo $hideTitle ? 'visuallyhidden' : ''; ?>">
                <?php echo $title; ?>
              </<?php echo is_singular() ? 'h1' : 'h2'; ?>>
            <?php endif; ?>
          </header>
          
          <section class="prose e-content">
            <?php the_content(esc_html__('Read more', 'commonplace')); ?>
          <footer class="post-footer">
            <?php wp_link_pages(); ?>

            <?php if (get_post_type() == 'post') : ?>
              <div class="post-meta meta">
                <p>
                  <?php $terms = get_terms(array(
                    'taxonomy' => array('post_tag', 'category', 'post_format'),
                    'object_ids' => get_the_id()
                  )); ?>
                  <?php if ($terms) : ?>
                    <?php foreach ($terms as $slug => $term) : ?>
                      <span class="term<?php echo $term->term_id == get_option('default_category') ? ' term--default' : ''; ?> term--<?php echo $term->taxonomy; ?>">
                        <a class="term-link" aria-label="<?php printf(_n('%s, %s post', '%s, %s posts', $term->count, 'commonplace'), $term->name, $term->count); ?>" href="<?php echo get_tag_link($term->term_id); ?>"><?php echo $term->name; ?></a><span class="separator" aria-hidden="true">,</span>
                      </span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </p>
              </div>
            <?php endif; ?>
            <?php if (is_singular()) : ?>
              <?php if (comments_open() || get_comments_number()) : ?>
                <?php comments_template(); ?>
              <?php endif; ?>
            <?php endif; ?>
            <div class="container">
              <a href="#top" class="visuallyhidden button"><?php _e('Back to top', 'commonplace'); ?></a>
            </div>

          </footer>
        </article>
      <?php endwhile; ?>

    <?php else : ?>
      <article class="post article type-<?php echo get_post_type(); ?>" style="--color:<?php echo cp_get_hsl(); ?>;">

          <section class="prose e-content">
            <p><?php _e('Nothing found!', 'commonplace'); ?></p>
            <?php get_search_form(); ?>
            <?php echo do_shortcode('[notebookindex count="2"]'); ?>
          </section>

      </article>

    <?php endif; ?>
  </section>
</main>