<?php
$cells = aiv_index_stories();

// Map each cell's category label to a WP category slug so we can pull a real post
$cat_slug_map = [
    'Markets'     => 'markets',
    'Banking'     => 'banking',
    'Currency'    => 'banking',
    'Commodities' => 'global',
    'Energy'      => 'global',
    'Economy'     => 'economy',
    'Policy'      => 'policy',
    'Technology'  => 'technology',
    'Global'      => 'global',
];
$used_ids = [];
?>
<section class="idx-stories">
  <?php foreach ($cells as $c):
    $slug = $cat_slug_map[$c['cat']] ?? 'markets';
    $q = new WP_Query([
        'posts_per_page'      => 1,
        'category_name'       => $slug,
        'post__not_in'        => $used_ids,
        'ignore_sticky_posts' => 1,
    ]);
    $post = $q->posts[0] ?? null;
    if ($post) $used_ids[] = $post->ID;
    $url   = $post ? get_permalink($post) : get_category_link(get_cat_ID($c['cat']));
    $title = $post ? get_the_title($post) : $c['title'];
    $cat_obj = get_the_category($post->ID ?? 0)[0] ?? null;
    $cat_label = $cat_obj ? $cat_obj->name : $c['cat'];
  ?>
  <a class="idx-cell" href="<?php echo esc_url($url); ?>">
    <span class="idx-symbol"><?php echo esc_html($c['symbol']); ?></span>
    <span class="idx-value"><?php echo esc_html($c['value']); ?></span>
    <span class="idx-change <?php echo $c['up'] ? 'up' : 'down'; ?>">
      <?php echo $c['up'] ? '▲' : '▼'; ?> <?php echo esc_html($c['chg']); ?>
    </span>
    <span class="idx-story-cat"><?php echo esc_html($cat_label); ?></span>
    <span class="idx-story-title"><?php echo esc_html($title); ?></span>
  </a>
  <?php endforeach; ?>
</section>
