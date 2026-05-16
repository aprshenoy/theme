<?php
defined('ABSPATH') || exit;

/* ── SETUP ─────────────────────────────────────────────────────────────── */
function aivartha_setup() {
    load_theme_textdomain('aivartha', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', ['height'=>60,'width'=>200,'flex-height'=>true,'flex-width'=>true]);
    set_post_thumbnail_size(800, 450, true);
    add_image_size('aiv-hero',  1200, 630, true);
    add_image_size('aiv-card',  600,  338, true);
    add_image_size('aiv-mini',  400,  300, true);
    add_image_size('aiv-thumb', 180,  135, true);
    register_nav_menus(['primary' => 'Primary Navigation', 'footer' => 'Footer Navigation']);
}
add_action('after_setup_theme', 'aivartha_setup');

/* ── FONT DETECTION ─────────────────────────────────────────────────────── */
function aivartha_font_url(): string {
    $script = get_option('aivartha_script', '');
    $locale = get_locale();
    if ($script === 'malayalam' || str_starts_with($locale, 'ml')) {
        $g = 'Noto+Sans+Malayalam:wght@400;600;700;800';
        $v = "'Noto Sans Malayalam'";
    } elseif ($script === 'hindi' || str_starts_with($locale, 'hi')) {
        $g = 'Noto+Sans+Devanagari:wght@400;600;700;800';
        $v = "'Noto Sans Devanagari'";
    } elseif ($script === 'telugu' || str_starts_with($locale, 'te')) {
        $g = 'Noto+Sans+Telugu:wght@400;600;700;800';
        $v = "'Noto Sans Telugu'";
    } else {
        $g = 'Inter:wght@400;500;600;700;800;900';
        $v = "'Inter'";
    }
    wp_add_inline_style('aivartha-style', ":root{--regional-font:{$v};}");
    return "https://fonts.googleapis.com/css2?family={$g}&family=DM+Mono:wght@400;500&display=swap";
}

/* ── ENQUEUE ────────────────────────────────────────────────────────────── */
function aivartha_enqueue() {
    wp_enqueue_style('aivartha-fonts', aivartha_font_url(), [], null);
    wp_enqueue_style('aivartha-style', get_stylesheet_uri(), ['aivartha-fonts'], '2.0.0');
    wp_enqueue_script('aivartha-js', get_template_directory_uri() . '/assets/js/main.js', [], '2.0.0', true);
    if (is_singular()) wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'aivartha_enqueue');

/* ── WIDGETS ────────────────────────────────────────────────────────────── */
function aivartha_widgets() {
    $args = [
        'before_widget' => '<section class="widget %2$s">',
        'after_widget'  => '</div></section>',
        'before_title'  => '<div class="widget-hd">',
        'after_title'   => '</div><div class="widget-body">',
    ];
    register_sidebar(array_merge($args, ['name' => 'Main Sidebar', 'id' => 'sidebar-1']));
    register_sidebar(array_merge($args, ['name' => 'Footer Col 4', 'id' => 'footer-4']));
}
add_action('widgets_init', 'aivartha_widgets');

/* ── BODY CLASS ─────────────────────────────────────────────────────────── */
add_filter('body_class', function($c) {
    $c[] = 'script-' . sanitize_html_class(get_option('aivartha_script', 'latin'));
    return $c;
});

/* ── LANG ATTR ──────────────────────────────────────────────────────────── */
add_filter('language_attributes', function($out) {
    $map = ['malayalam' => 'ml', 'hindi' => 'hi', 'telugu' => 'te'];
    $s   = get_option('aivartha_script', '');
    if (isset($map[$s])) $out = preg_replace('/lang="[^"]*"/', 'lang="'.$map[$s].'"', $out);
    return $out;
});

/* ── EXCERPT ────────────────────────────────────────────────────────────── */
add_filter('excerpt_length', fn() => 22, 999);
add_filter('excerpt_more',   fn() => '…');

/* ─── HELPERS ───────────────────────────────────────────────────────────── */

/** Inline SVG icon via sprite — accepts 'search' or 'i-search' both work */
function aiv_icon(string $id, string $cls = ''): string {
    $a    = $cls ? " class=\"{$cls}\"" : '';
    $href = str_starts_with($id, 'i-') ? "#{$id}" : "#i-{$id}";
    return "<svg{$a} aria-hidden=\"true\"><use href=\"{$href}\"/></svg>";
}

/** Category badge (anchor — do NOT use inside another <a>) */
function aiv_cat(int $id = 0, string $extra = ''): void {
    $cats = get_the_category($id ?: get_the_ID());
    if (!$cats) return;
    $c = $cats[0];
    $cls = trim('cat-tag ' . sanitize_html_class($c->slug) . ' ' . $extra);
    echo '<a href="'.esc_url(get_category_link($c->term_id)).'" class="'.esc_attr($cls).'" rel="category">'.esc_html($c->name).'</a>';
}

/** Category badge as <span> — safe to use inside <a> elements (mini-box, etc.) */
function aiv_cat_span(int $id = 0, string $extra = ''): void {
    $cats = get_the_category($id ?: get_the_ID());
    if (!$cats) return;
    $c = $cats[0];
    $cls = trim('cat-tag ' . sanitize_html_class($c->slug) . ' ' . $extra);
    echo '<span class="'.esc_attr($cls).'">'.esc_html($c->name).'</span>';
}

/** Reading time */
function aiv_read_time(int $id = 0): string {
    $text = wp_strip_all_tags(get_post_field('post_content', $id ?: get_the_ID()));
    $wpm  = in_array(get_option('aivartha_script',''), ['malayalam','hindi','telugu']) ? 170 : 220;
    $wc   = str_word_count($text) ?: (mb_strlen($text) / 5);
    $m    = max(1, (int) ceil($wc / $wpm));
    return $m . ' min';
}

/** Placeholder div */
function aiv_placeholder(): string {
    return '<div class="card-img-placeholder">'.aiv_icon('newspaper').'</div>';
}

/** Market ticker data */
function aiv_market_items(): array {
    return [
        ['n'=>'NIFTY 50',   'p'=>'22,450',  'c'=>'+1.2%',  'up'=>true],
        ['n'=>'SENSEX',     'p'=>'73,888',  'c'=>'+0.95%', 'up'=>true],
        ['n'=>'BANK NIFTY', 'p'=>'48,120',  'c'=>'-0.31%', 'up'=>false],
        ['n'=>'USD/INR',    'p'=>'₹83.45',  'c'=>'-0.12%', 'up'=>false],
        ['n'=>'GOLD',       'p'=>'₹71,450', 'c'=>'+0.5%',  'up'=>true],
        ['n'=>'CRUDE OIL',  'p'=>'$85.6',   'c'=>'+1.1%',  'up'=>true],
        ['n'=>'IT INDEX',   'p'=>'38,240',  'c'=>'+2.3%',  'up'=>true],
        ['n'=>'MID CAP',    'p'=>'41,200',  'c'=>'+0.8%',  'up'=>true],
        ['n'=>'NIFTY 50',   'p'=>'22,450',  'c'=>'+1.2%',  'up'=>true],
        ['n'=>'SENSEX',     'p'=>'73,888',  'c'=>'+0.95%', 'up'=>true],
        ['n'=>'BANK NIFTY', 'p'=>'48,120',  'c'=>'-0.31%', 'up'=>false],
        ['n'=>'USD/INR',    'p'=>'₹83.45',  'c'=>'-0.12%', 'up'=>false],
    ];
}

/** Breaking / latest posts for ticker */
function aiv_breaking(): array {
    $ps = get_posts(['numberposts'=>10,'category_name'=>'breaking','post_status'=>'publish']);
    return $ps ?: get_posts(['numberposts'=>10,'post_status'=>'publish']);
}
