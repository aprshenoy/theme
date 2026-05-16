<?php get_header(); ?>

<div class="home-grid">
  <div>

    <!-- Archive header -->
    <header class="archive-head">
      <?php if (is_category()): ?>
        <?php $cat = get_queried_object(); ?>
        <div class="arc-kicker">Section</div>
        <h1 class="arc-title"><?php single_cat_title(); ?></h1>
        <?php if ($cat->description): ?>
        <p class="arc-desc"><?php echo esc_html($cat->description); ?></p>
        <?php endif; ?>

      <?php elseif (is_tag()): ?>
        <div class="arc-kicker">Tagged</div>
        <h1 class="arc-title"><?php single_tag_title(); ?></h1>

      <?php elseif (is_author()): ?>
        <div class="arc-kicker">Author</div>
        <h1 class="arc-title"><?php the_author(); ?></h1>
        <?php $bio = get_the_author_meta('description'); if ($bio): ?>
        <p class="arc-desc"><?php echo esc_html($bio); ?></p>
        <?php endif; ?>

      <?php elseif (is_date()): ?>
        <div class="arc-kicker">Archive</div>
        <h1 class="arc-title"><?php the_archive_title(); ?></h1>

      <?php elseif (is_search()): ?>
        <div class="arc-kicker">Search Results</div>
        <h1 class="arc-title">&ldquo;<?php echo esc_html(get_search_query()); ?>&rdquo;</h1>
        <p class="arc-desc"><?php printf('%d articles found', $wp_query->found_posts); ?></p>

      <?php else: ?>
        <h1 class="arc-title"><?php the_archive_title(); ?></h1>
      <?php endif; ?>
    </header>

    <?php if (have_posts()): ?>

    <div class="news-rows">
      <?php while (have_posts()): the_post(); ?>
      <article <?php post_class('news-row'); ?>>
        <a href="<?php the_permalink(); ?>" class="news-row-img" tabindex="-1" aria-hidden="true">
          <?php if (has_post_thumbnail()):
            the_post_thumbnail('aiv-thumb', ['alt' => '', 'loading' => 'lazy']);
          else: ?>
            <div class="news-row-img-ph"><?php echo aiv_icon('newspaper'); ?></div>
          <?php endif; ?>
        </a>
        <div class="news-row-body">
          <?php aiv_cat(); ?>
          <h2 class="news-row-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p class="news-row-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 18, '…'); ?></p>
          <div class="meta">
            <span class="meta-i"><?php echo aiv_icon('calendar'); ?> <?php echo get_the_date(); ?></span>
            <span class="meta-i"><?php echo aiv_icon('clock'); ?> <?php echo aiv_read_time(); ?></span>
            <span class="meta-i"><?php echo aiv_icon('user'); ?> <?php the_author(); ?></span>
          </div>
        </div>
      </article>
      <?php endwhile; ?>
    </div>

    <div class="archive-pagination">
      <?php
      echo paginate_links([
        'total'     => $wp_query->max_num_pages,
        'current'   => max(1, get_query_var('paged')),
        'mid_size'  => 2,
        'prev_text' => '&larr; Prev',
        'next_text' => 'Next &rarr;',
        'type'      => 'list',
      ]);
      ?>
    </div>

    <?php else: ?>

    <div class="empty-state">
      <?php echo aiv_icon('newspaper'); ?>
      <h2>Nothing found</h2>
      <p>Try a different search term or browse our sections.</p>
      <?php get_search_form(); ?>
    </div>

    <?php endif; ?>

  </div>

  <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
