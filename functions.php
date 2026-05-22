<?php
defined('ABSPATH') || exit;

/**
 * AI Vartha Editorial — functions.php
 * v3.0.0 — broadsheet editorial direction
 */

/* ════════════════════════════════════════════════════════════════════════
   SETUP
   ════════════════════════════════════════════════════════════════════════ */
function aivartha_setup() {
    load_theme_textdomain('aivartha', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', ['height'=>60,'width'=>200,'flex-height'=>true,'flex-width'=>true]);

    set_post_thumbnail_size(800, 450, true);
    add_image_size('aiv-hero',    1200, 630, true);
    add_image_size('aiv-mid',     600,  450, true);
    add_image_size('aiv-card',    600,  338, true);
    add_image_size('aiv-banking', 800,  500, true);
    add_image_size('aiv-thumb',   180,  135, true);

    register_nav_menus([
        'primary' => __('Primary Navigation', 'aivartha'),
        'footer'  => __('Footer Navigation', 'aivartha'),
    ]);
}
add_action('after_setup_theme', 'aivartha_setup');

/* ════════════════════════════════════════════════════════════════════════
   FONT LOADING (Indic-aware)
   ════════════════════════════════════════════════════════════════════════ */
function aivartha_font_url(): string {
    $script = get_option('aivartha_script', '');
    $locale = get_locale();
    $g = 'Inter:wght@400;500;600;700;800;900&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600;8..60,700&family=JetBrains+Mono:wght@400;500;600';

    if ($script === 'malayalam' || str_starts_with($locale, 'ml')) {
        $g .= '&family=Noto+Sans+Malayalam:wght@400;500;600;700;800';
        $v = "'Noto Sans Malayalam'";
    } elseif ($script === 'hindi' || str_starts_with($locale, 'hi')) {
        $g .= '&family=Noto+Sans+Devanagari:wght@400;500;600;700;800';
        $v = "'Noto Sans Devanagari'";
    } elseif ($script === 'telugu' || str_starts_with($locale, 'te')) {
        $g .= '&family=Noto+Sans+Telugu:wght@400;500;600;700;800';
        $v = "'Noto Sans Telugu'";
    } else {
        $v = "'Inter'";
    }
    wp_add_inline_style('aivartha-style', ":root{--regional-font:{$v};}");
    return "https://fonts.googleapis.com/css2?family={$g}&display=swap";
}

function aivartha_enqueue() {
    wp_enqueue_style('aivartha-style', get_stylesheet_uri(), [], '3.0.0');
    wp_enqueue_script('aivartha-js', get_template_directory_uri() . '/assets/js/main.js', [], '3.0.0', true);
    if (is_singular()) wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'aivartha_enqueue');

/* Non-blocking font load — fonts never block render */
add_action('wp_head', function() {
    $url = esc_url(aivartha_font_url());
    echo <<<HTML
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="{$url}">
<link rel="stylesheet" href="{$url}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{$url}"></noscript>
HTML;
}, 1);

/* ════════════════════════════════════════════════════════════════════════
   WIDGET AREAS
   - Main sidebar
   - 5 ad zones (paste any HTML/JS ad code, GAM, AdSense, etc.)
   ════════════════════════════════════════════════════════════════════════ */
function aivartha_widgets() {
    $args = [
        'before_widget' => '<section class="widget %2$s">',
        'after_widget'  => '</div></section>',
        'before_title'  => '<div class="widget-hd">',
        'after_title'   => '</div><div class="widget-body">',
    ];
    register_sidebar(array_merge($args, ['name' => __('Article Sidebar', 'aivartha'), 'id' => 'sidebar-1']));
    register_sidebar(array_merge($args, ['name' => __('Footer Column 4', 'aivartha'), 'id' => 'footer-4']));

    // Ad zones — empty wrappers; paste your GAM/AdSense code via Appearance > Widgets
    $ads = [
        ['ad-leaderboard', __('Ad — Leaderboard (homepage, below hero)',  'aivartha')],
        ['ad-mpu',         __('Ad — MPU 300×250 (in briefing sidebar)',   'aivartha')],
        ['ad-native',      __('Ad — Native / Sponsored card',             'aivartha')],
        ['ad-skyscraper',  __('Ad — Skyscraper 160×600 (article sidebar)','aivartha')],
        ['ad-sticky',      __('Ad — Sticky footer bar',                   'aivartha')],
        ['ad-in-article',  __('Ad — In-article (after 3rd paragraph)',    'aivartha')],
    ];
    foreach ($ads as [$id, $name]) {
        register_sidebar([
            'name'          => $name,
            'id'            => $id,
            'description'   => __('Paste any HTML/JS ad code as a Custom HTML widget.', 'aivartha'),
            'before_widget' => '<div class="ad-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '',
            'after_title'   => '',
        ]);
    }
}
add_action('widgets_init', 'aivartha_widgets');

/* ════════════════════════════════════════════════════════════════════════
   ICON SPRITE HELPER (unchanged from v2)
   ════════════════════════════════════════════════════════════════════════ */
function aiv_icon(string $id, string $cls = ''): string {
    $a    = $cls ? " class=\"{$cls}\"" : '';
    $href = str_starts_with($id, 'i-') ? "#{$id}" : "#i-{$id}";
    return "<svg{$a} aria-hidden=\"true\"><use href=\"{$href}\"/></svg>";
}

/* Category badge — anchor (don't nest inside other <a>) */
function aiv_cat(int $id = 0, string $extra = ''): void {
    $cats = get_the_category($id ?: get_the_ID());
    if (!$cats) return;
    $c   = $cats[0];
    $cls = trim('cat-tag cat-' . sanitize_html_class($c->slug) . ' ' . $extra);
    echo '<a href="' . esc_url(get_category_link($c->term_id)) . '" class="' . esc_attr($cls) . '" rel="category">' . esc_html($c->name) . '</a>';
}

/* Category badge — span (safe inside <a>) */
function aiv_cat_span(int $id = 0, string $extra = ''): void {
    $cats = get_the_category($id ?: get_the_ID());
    if (!$cats) return;
    $c   = $cats[0];
    $cls = trim('cat-tag cat-' . sanitize_html_class($c->slug) . ' ' . $extra);
    echo '<span class="' . esc_attr($cls) . '">' . esc_html($c->name) . '</span>';
}

function aiv_read_time(int $id = 0): string {
    $text = wp_strip_all_tags(get_post_field('post_content', $id ?: get_the_ID()));
    $wpm  = in_array(get_option('aivartha_script', ''), ['malayalam','hindi','telugu']) ? 170 : 220;
    $wc   = str_word_count($text) ?: (mb_strlen($text) / 5);
    return max(1, (int) ceil($wc / $wpm)) . ' min';
}

function aiv_placeholder(): string {
    return '<div class="card-img-placeholder">' . aiv_icon('newspaper') . '</div>';
}

function aiv_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last  = mb_substr(end($parts) ?? '', 0, 1);
    return strtoupper($first . $last);
}

/* ════════════════════════════════════════════════════════════════════════
   CONTENT-CURATION HELPERS — replace mock data with real queries
   All return arrays of WP_Post (or stdClass for non-post data).
   Override these in a child theme to wire to your CMS structure.
   ════════════════════════════════════════════════════════════════════════ */

/** Top story (hero lead) — first sticky post, fallback to latest */
function aiv_top_story(): ?WP_Post {
    $sticky = get_option('sticky_posts');
    if ($sticky) {
        $p = get_post($sticky[0]);
        if ($p) return $p;
    }
    $q = new WP_Query(['posts_per_page' => 1, 'post_status' => 'publish', 'ignore_sticky_posts' => 1]);
    return $q->posts[0] ?? null;
}

/** Front-page MID story (right of lead) */
function aiv_mid_story(int $exclude = 0): ?WP_Post {
    $sticky = array_filter(get_option('sticky_posts', []), fn($id) => $id !== $exclude);
    if (count($sticky) > 1) {
        $p = get_post(array_values($sticky)[1] ?? null);
        if ($p) return $p;
    }
    $q = new WP_Query([
        'posts_per_page' => 1,
        'post__not_in'   => array_filter([$exclude]),
        'category_name'  => 'markets',
        'ignore_sticky_posts' => 1,
    ]);
    return $q->posts[0] ?? null;
}

/** Front-page RIGHT column — 4 stories from different categories */
function aiv_right_stories(array $exclude = [], int $n = 4): array {
    $cats = ['banking', 'global', 'opinion', 'economy', 'policy', 'foreign-policy', 'technology'];
    $out  = [];
    foreach ($cats as $slug) {
        if (count($out) >= $n) break;
        $q = new WP_Query([
            'posts_per_page'      => 1,
            'category_name'       => $slug,
            'post__not_in'        => array_merge($exclude, wp_list_pluck($out, 'ID')),
            'ignore_sticky_posts' => 1,
        ]);
        if (!empty($q->posts)) $out[] = $q->posts[0];
    }
    // Backfill from recent if categories aren't populated yet
    if (count($out) < $n) {
        $fill = new WP_Query([
            'posts_per_page'      => $n - count($out),
            'post__not_in'        => array_merge($exclude, wp_list_pluck($out, 'ID')),
            'ignore_sticky_posts' => 1,
        ]);
        $out = array_merge($out, $fill->posts);
    }
    return $out;
}

/** The Briefing — posts tagged "briefing" (fallback: top 5 recent) */
function aiv_briefing_posts(int $n = 5): array {
    $q = new WP_Query([
        'posts_per_page' => $n,
        'tag'            => 'briefing',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    if ($q->have_posts()) return $q->posts;
    return get_posts(['numberposts' => $n, 'post_status' => 'publish']);
}

/** Opinion columns ("Voices") */
function aiv_voices_posts(int $n = 4): array {
    $q = new WP_Query([
        'posts_per_page' => $n,
        'category_name'  => 'opinion',
    ]);
    return $q->posts;
}

/** Banking feature + 3 secondary rows */
function aiv_banking_posts(int $n = 4): array {
    $q = new WP_Query(['posts_per_page' => $n, 'category_name' => 'banking']);
    return $q->posts;
}

/** Global cells (4 stories, ideally one per region) */
function aiv_global_posts(int $n = 4): array {
    $q = new WP_Query(['posts_per_page' => $n, 'category_name' => 'global']);
    return $q->posts;
}

/* ════════════════════════════════════════════════════════════════════════
   MARKET-DATA HELPERS — return static defaults, filterable
   To wire to a live feed, hook into these filters from a child theme
   or a small companion plugin.
   ════════════════════════════════════════════════════════════════════════ */

/** Top-bar ticker */
function aiv_market_items(): array {
    return apply_filters('aiv_market_items', [
        ['n'=>'NIFTY 50',   'p'=>'22,450.20', 'c'=>'+1.20%',  'up'=>true],
        ['n'=>'SENSEX',     'p'=>'73,888.45', 'c'=>'+0.95%',  'up'=>true],
        ['n'=>'BANK NIFTY', 'p'=>'48,120.10', 'c'=>'-0.31%',  'up'=>false],
        ['n'=>'USD/INR',    'p'=>'₹83.45',    'c'=>'-0.12%',  'up'=>false],
        ['n'=>'GOLD',       'p'=>'₹71,450',   'c'=>'+0.50%',  'up'=>true],
        ['n'=>'BRENT',      'p'=>'$85.60',    'c'=>'+1.10%',  'up'=>true],
        ['n'=>'IT INDEX',   'p'=>'38,240.00', 'c'=>'+2.30%',  'up'=>true],
        ['n'=>'MIDCAP 100', 'p'=>'41,200.55', 'c'=>'+0.80%',  'up'=>true],
    ]);
}

/** Dateline headline indices */
function aiv_dateline_data(): array {
    return apply_filters('aiv_dateline', [
        'vol'      => 'Vol. IV · No. ' . wp_date('z'),
        'edition'  => __('National Edition', 'aivartha'),
        'weather'  => __('Mumbai 32°C · Humid', 'aivartha'),
        'sensex'   => '+0.95%',
        'nifty'    => '+1.20%',
        'rupee'    => '83.45',
    ]);
}

/** Markets Pulse — sector heatmap (1 big + 14 smalls = 18 cells) */
function aiv_heatmap_data(): array {
    return apply_filters('aiv_heatmap', [
        ['name'=>'INFOTECH',  'pct'=>'+2.31%','up'=>true, 'large'=>true, 'sub'=>'Nifty IT · 38,240'],
        ['name'=>'BANK',      'pct'=>'+0.85%','up'=>true],
        ['name'=>'AUTO',      'pct'=>'+1.42%','up'=>true],
        ['name'=>'PHARMA',    'pct'=>'+0.95%','up'=>true],
        ['name'=>'CONSUMER',  'pct'=>'-0.30%','up'=>false],
        ['name'=>'METAL',     'pct'=>'+2.05%','up'=>true],
        ['name'=>'REALTY',    'pct'=>'-0.62%','up'=>false],
        ['name'=>'FMCG',      'pct'=>'+0.18%','up'=>true],
        ['name'=>'ENERGY',    'pct'=>'+1.15%','up'=>true],
        ['name'=>'MEDIA',     'pct'=>'-1.20%','up'=>false],
        ['name'=>'PSU BANK',  'pct'=>'-0.45%','up'=>false],
        ['name'=>'INFRA',     'pct'=>'+1.78%','up'=>true],
        ['name'=>'COMMODITY', 'pct'=>'+0.92%','up'=>true],
        ['name'=>'FIN SVC',   'pct'=>'+0.40%','up'=>true],
        ['name'=>'POWER',     'pct'=>'+1.32%','up'=>true],
    ]);
}

/** Key indices in pulse-band right column */
function aiv_pulse_indices(): array {
    return apply_filters('aiv_pulse_indices', [
        ['name'=>'NIFTY 50',   'value'=>'22,450.20', 'chg'=>'+1.20%', 'up'=>true],
        ['name'=>'BANK NIFTY', 'value'=>'48,120.10', 'chg'=>'-0.31%', 'up'=>false],
        ['name'=>'NIFTY IT',   'value'=>'38,240.00', 'chg'=>'+2.30%', 'up'=>true],
        ['name'=>'INDIA VIX',  'value'=>'11.84',     'chg'=>'-3.20%', 'up'=>false],
    ]);
}

/** "By the Numbers" sidebar card */
function aiv_numbers_today(): array {
    return apply_filters('aiv_numbers', [
        ['figure'=>'₹2.10','unit'=>'lakh crore', 'label'=>__('GST collections in April — an all-time record', 'aivartha'),       'accent'=>'up'],
        ['figure'=>'6.5%','unit'=>'',            'label'=>__('RBI repo rate · unchanged for the 11th consecutive meeting', 'aivartha'), 'accent'=>''],
        ['figure'=>'₹38,000','unit'=>'crore',    'label'=>__('Net FII inflow in May — third straight month of buying', 'aivartha'),    'accent'=>'up'],
        ['figure'=>'3.61%','unit'=>'',           'label'=>__('CPI inflation in April · lowest print in 19 months', 'aivartha'),        'accent'=>'down'],
    ]);
}

/** Index-led stories strip (5 cells) */
function aiv_index_stories(): array {
    return apply_filters('aiv_index_stories', [
        ['symbol'=>'NIFTY 50',   'value'=>'22,450','chg'=>'+1.20%','up'=>true,  'cat'=>'Markets',     'title'=>'IT rally lifts Nifty past 22,400 for first time'],
        ['symbol'=>'BANK NIFTY', 'value'=>'48,120','chg'=>'-0.31%','up'=>false, 'cat'=>'Banking',     'title'=>'PSU drag erases early gains in bank index'],
        ['symbol'=>'USD/INR',    'value'=>'83.45', 'chg'=>'-0.12%','up'=>false, 'cat'=>'Currency',    'title'=>'Rupee strengthens on RBI dollar inflows'],
        ['symbol'=>'GOLD',       'value'=>'71,450','chg'=>'+0.50%','up'=>true,  'cat'=>'Commodities', 'title'=>'Gold tests fresh peak as ETF flows resume'],
        ['symbol'=>'BRENT',      'value'=>'$85.6', 'chg'=>'+1.10%','up'=>true,  'cat'=>'Energy',      'title'=>'OMC stocks slip 2% as Brent crosses $87 mark'],
    ]);
}

/** Global region cells */
function aiv_global_cells(): array {
    return apply_filters('aiv_global_cells', [
        ['region'=>'United States','flag'=>'#B22234','title'=>'Fed dot plot signals two cuts in 2026; markets had priced three','stat'=>'S&P 500','up'=>true,  'pct'=>'+0.42%'],
        ['region'=>'China',        'flag'=>'#DE2910','title'=>'Industrial output rebounds 6.7% YoY, lifting iron-ore prices',   'stat'=>'Shanghai','up'=>true, 'pct'=>'+1.18%'],
        ['region'=>'Europe',       'flag'=>'#003399','title'=>'ECB holds at 3.25%; Lagarde says June cut "discussed but not decided"','stat'=>'EuroStoxx 50','up'=>false,'pct'=>'-0.22%'],
        ['region'=>'Japan',        'flag'=>'#BC002D','title'=>'BoJ delays QT timeline; yen weakens below 156 to dollar',        'stat'=>'Nikkei 225','up'=>true,'pct'=>'+0.65%'],
    ]);
}

/** Breaking news bar */
function aiv_breaking(): array {
    $ps = get_posts(['numberposts'=>10, 'category_name'=>'breaking', 'post_status'=>'publish']);
    return $ps ?: get_posts(['numberposts'=>10, 'post_status'=>'publish']);
}

/* ════════════════════════════════════════════════════════════════════════
   BODY CLASS + LANG
   ════════════════════════════════════════════════════════════════════════ */
add_filter('body_class', function($c) {
    $c[] = 'script-' . sanitize_html_class(get_option('aivartha_script', 'latin'));
    return $c;
});

add_filter('language_attributes', function($out) {
    $map = ['malayalam'=>'ml', 'hindi'=>'hi', 'telugu'=>'te'];
    $s   = get_option('aivartha_script', '');
    if (isset($map[$s])) $out = preg_replace('/lang="[^"]*"/', 'lang="' . $map[$s] . '"', $out);
    return $out;
});

add_filter('excerpt_length', fn() => 22, 999);
add_filter('excerpt_more',   fn() => '…');

/* ════════════════════════════════════════════════════════════════════════
   IN-ARTICLE AD INJECTION
   Injects the "ad-in-article" widget area after the 3rd paragraph of every
   single post. If the widget area is empty, nothing renders.
   ════════════════════════════════════════════════════════════════════════ */
add_filter('the_content', function ($content) {
    if (!is_singular('post') || !is_main_query() || !is_active_sidebar('ad-in-article')) {
        return $content;
    }
    $needle = '</p>';
    $count  = 0; $offset = 0;
    while (($pos = strpos($content, $needle, $offset)) !== false) {
        $count++;
        if ($count === 3) {
            ob_start();
            get_template_part('template-parts/ads/in-article');
            $ad = ob_get_clean();
            return substr_replace($content, $needle . $ad, $pos, strlen($needle));
        }
        $offset = $pos + strlen($needle);
    }
    return $content;
});

/* ════════════════════════════════════════════════════════════════════════
   ADMIN — onboarding nudge
   ════════════════════════════════════════════════════════════════════════ */
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'dashboard') return;
    if (get_option('aivartha_setup_dismissed')) return;
    $url = admin_url('widgets.php');
    echo '<div class="notice notice-info is-dismissible">
        <p><strong>AI Vartha Editorial v3</strong> is active. Configure your editorial sections:</p>
        <ol style="margin-left:20px">
            <li>Create categories: <code>markets</code>, <code>policy</code>, <code>banking</code>, <code>economy</code>, <code>global</code>, <code>foreign-policy</code>, <code>technology</code>, <code>opinion</code>, <code>breaking</code></li>
            <li>Tag 5 articles with <code>briefing</code> to populate the homepage Briefing block</li>
            <li>Mark today\'s top story as <em>Sticky</em> for the Front Page lead</li>
            <li><a href="' . esc_url($url) . '">Paste ad codes</a> into the 6 ad zones (Leaderboard, MPU, Native, Skyscraper, Sticky, In-article)</li>
        </ol>
    </div>';
});
