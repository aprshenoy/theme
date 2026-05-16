  </div><!-- /.wrap -->
</div><!-- /.site-body -->

<footer class="site-footer">
  <div class="footer-top">
    <div class="wrap">
      <div class="footer-cols">

        <div>
          <span class="footer-logo-name">AI&nbsp;<em>Vartha</em></span>
          <p class="footer-desc"><?php echo esc_html(get_bloginfo('description') ?: 'Trusted economic and financial news in your language, powered by AI.'); ?></p>
          <div class="footer-socials">
            <a href="#" aria-label="Facebook"><?php echo aiv_icon('i-fb'); ?></a>
            <a href="#" aria-label="Twitter"><?php echo aiv_icon('i-tw'); ?></a>
            <a href="#" aria-label="WhatsApp"><?php echo aiv_icon('i-wa'); ?></a>
            <a href="<?php bloginfo('rss2_url'); ?>" aria-label="RSS Feed"><?php echo aiv_icon('i-rss'); ?></a>
          </div>
        </div>

        <div>
          <p class="footer-col-title">Sections</p>
          <nav class="footer-links">
            <?php foreach (get_categories(['number'=>8,'hide_empty'=>true]) as $c): ?>
            <a href="<?php echo esc_url(get_category_link($c->term_id)); ?>">
              <?php echo aiv_icon('i-arrow-r'); ?> <?php echo esc_html($c->name); ?>
            </a>
            <?php endforeach; ?>
          </nav>
        </div>

        <div>
          <p class="footer-col-title">Latest</p>
          <nav class="footer-links">
            <?php foreach (get_posts(['numberposts'=>6,'post_status'=>'publish']) as $p): ?>
            <a href="<?php echo esc_url(get_permalink($p->ID)); ?>">
              <?php echo aiv_icon('i-arrow-r'); ?> <?php echo esc_html(wp_trim_words(get_the_title($p->ID),7,'…')); ?>
            </a>
            <?php endforeach; ?>
          </nav>
        </div>

        <div>
          <p class="footer-col-title">About</p>
          <nav class="footer-links">
            <?php if (is_active_sidebar('footer-4')): dynamic_sidebar('footer-4');
            else: ?>
            <a href="<?php echo esc_url(home_url('/about')); ?>"><?php echo aiv_icon('i-arrow-r'); ?> About Us</a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>"><?php echo aiv_icon('i-arrow-r'); ?> Contact</a>
            <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>"><?php echo aiv_icon('i-arrow-r'); ?> Privacy Policy</a>
            <a href="<?php echo esc_url(home_url('/disclaimer')); ?>"><?php echo aiv_icon('i-arrow-r'); ?> Disclaimer</a>
            <?php endif; ?>
          </nav>
        </div>

      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="wrap">
      <span>&copy; <?php echo date('Y'); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>. All rights reserved.</span>
      <span>Powered by AI Vartha &amp; IndicTrans2</span>
    </div>
  </div>
</footer>

<button class="back-top" id="back-top" aria-label="Back to top"><?php echo aiv_icon('i-arrow-up'); ?></button>

<?php wp_footer(); ?>
</body>
</html>
