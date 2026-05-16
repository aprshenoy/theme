<?php get_header(); ?>

<?php if (is_home() && !is_paged()):
  // ── HERO BLOCK: Big story left + 4 mini boxes right ─────────────────────
  $hero_q = new WP_Query(['posts_per_page'=>1,'post_status'=>'publish']);
  $side_q = new WP_Query(['posts_per_page'=>4,'post_status'=>'publish','offset'=>1]);
  if ($hero_q->have_posts()):
?>
<section class="hero-block" aria-label="Top stories">

  <!-- LEFT: featured story -->
  <?php $hero_q->the_post(); $bg = get_the_post_thumbnail_url(null,'aiv-hero'); ?>
  <div class="hero-main">
    <?php if ($bg): ?>
      <div class="hero-bg" style="background-image:url('<?php echo esc_url($bg); ?>')" role="img" aria-label="<?php the_title_attribute(); ?>"></div>
    <?php else: ?>
      <div class="hero-bg" style="background:linear-gradient(135deg,#1B2E45 0%,#2E4D6B 100%)"></div>
    <?php endif; ?>
    <div class="hero-grad"></div>
    <div class="hero-body">
      <?php aiv_cat(); ?>
      <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <div class="meta">
        <span class="meta-i"><?php echo aiv_icon('calendar'); ?> <?php echo get_the_date(); ?></span>
        <span class="meta-i"><?php echo aiv_icon('clock'); ?> <?php echo aiv_read_time(); ?></span>
        <span class="meta-i"><?php echo aiv_icon('user'); ?> <?php the_author(); ?></span>
      </div>
      <p class="hero-excerpt"><?php echo get_the_excerpt(); ?></p>
      <a href="<?php the_permalink(); ?>" class="hero-cta">
        Read Article <?php echo aiv_icon('i-arrow-r'); ?>
      </a>
    </div>
  </div>

  <!-- RIGHT: 4 mini story boxes -->
  <div class="hero-side">
    <?php
    while ($side_q->have_posts()): $side_q->the_post();
    ?>
    <article class="mini-box">
      <!-- cover link — valid: no nested <a> inside <a> -->
      <a href="<?php the_permalink(); ?>" class="mini-cover-link" aria-label="<?php the_title_attribute(); ?>"></a>
      <?php aiv_cat_span(); ?>
      <div class="mini-box-title"><?php the_title(); ?></div>
      <div class="meta">
        <span class="meta-i"><?php echo aiv_icon('calendar'); ?> <?php echo get_the_date(); ?></span>
        <span class="meta-i"><?php echo aiv_icon('clock'); ?> <?php echo aiv_read_time(); ?></span>
      </div>
    </article>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>

</section>

<?php wp_reset_postdata();
  endif;

  // ── 4-COLUMN CARD STRIP ──────────────────────────────────────────────────
  $strip_q = new WP_Query(['posts_per_page'=>4,'post_status'=>'publish','offset'=>5,'ignore_sticky_posts'=>1]);
  if ($strip_q->have_posts()):
?>
<div class="sec-hd" style="margin-bottom:14px">
  <span class="sec-title">Latest Stories</span>
  <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="sec-more">View all <?php echo aiv_icon('i-arrow-r'); ?></a>
</div>
<div class="card-strip">
  <?php while ($strip_q->have_posts()): $strip_q->the_post(); ?>
  <article <?php post_class('post-card'); ?>>
    <a href="<?php the_permalink(); ?>" class="card-img" tabindex="-1" aria-hidden="true">
      <?php if (has_post_thumbnail()):
        the_post_thumbnail('aiv-card',['alt'=>get_the_title(),'loading'=>'lazy']);
      else: echo aiv_placeholder(); endif; ?>
    </a>
    <div class="card-body">
      <?php aiv_cat(); ?>
      <h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <div class="card-foot">
        <div class="meta">
          <span class="meta-i"><?php echo aiv_icon('calendar'); ?> <?php echo get_the_date(); ?></span>
          <span class="meta-i"><?php echo aiv_icon('clock'); ?> <?php echo aiv_read_time(); ?></span>
        </div>
      </div>
    </div>
  </article>
  <?php endwhile; wp_reset_postdata(); ?>
</div>
<?php endif; ?>

<?php endif; // end is_home first page ?>

<!-- ── MAIN TWO-COL: News rows + Sidebar ────────────────────────────────── -->
<div class="home-grid">
  <div>

    <?php if (is_home()): ?>
    <div class="sec-hd">
      <span class="sec-title">More News</span>
    </div>
    <?php elseif (is_category()): ?>
    <div class="sec-hd">
      <span class="sec-title"><?php single_cat_title(); ?></span>
    </div>
    <?php elseif (is_search()): ?>
    <div class="sec-hd">
      <span class="sec-title">Results for &ldquo;<?php echo get_search_query(); ?>&rdquo;</span>
    </div>
    <?php else: ?>
    <div class="sec-hd">
      <span class="sec-title"><?php the_archive_title(); ?></span>
    </div>
    <?php endif; ?>

    <?php
    if (is_home()) {
      $offset = !is_paged() ? 5 : 0;   // skip hero(1) + mini(4) = 5
      $paged  = max(1, get_query_var('paged'));
      $main_q = new WP_Query(['posts_per_page'=>10,'offset'=>$offset,'paged'=>$paged,'post_status'=>'publish','ignore_sticky_posts'=>1]);
    } else {
      $main_q = $GLOBALS['wp_query'];
      $paged  = max(1, get_query_var('paged'));
    }
    ?>

    <?php if ($main_q->have_posts()): ?>
    <div class="news-rows">
      <?php while ($main_q->have_posts()): $main_q->the_post(); ?>
      <article <?php post_class('news-row'); ?>>
        <a href="<?php the_permalink(); ?>" class="news-row-img" tabindex="-1" aria-hidden="true">
          <?php if (has_post_thumbnail()):
            the_post_thumbnail('aiv-thumb',['alt'=>'','loading'=>'lazy']);
          endif; ?>
        </a>
        <div class="news-row-body">
          <?php aiv_cat(); ?>
          <h3 class="news-row-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <div class="meta">
            <span class="meta-i"><?php echo aiv_icon('calendar'); ?> <?php echo get_the_date(); ?></span>
            <span class="meta-i"><?php echo aiv_icon('clock'); ?> <?php echo aiv_read_time(); ?></span>
            <span class="meta-i"><?php echo aiv_icon('user'); ?> <?php the_author(); ?></span>
          </div>
        </div>
      </article>
      <?php endwhile; if (is_home()) wp_reset_postdata(); ?>
    </div>

    <div class="archive-pagination" style="margin-top:24px">
      <?php echo paginate_links(['total'=>$main_q->max_num_pages,'current'=>$paged,'mid_size'=>2,'prev_text'=>'&larr;','next_text'=>'&rarr;','type'=>'list']); ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <?php echo aiv_icon('newspaper'); ?>
      <p>No articles found. Check back soon.</p>
    </div>
    <?php endif; ?>

  </div>

  <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
