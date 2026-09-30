<?php defined('ABSPATH') || exit; ?>
<footer class="site-footer wrap">
    <a class="footer-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="تکاو، صفحه اصلی"><bdi>TAKAV<span aria-hidden="true">.</span></bdi></a>
    <span>تکاو — همهٔ حقوق محفوظ است.</span>
    <nav class="footer-links" aria-label="پیوندهای پایانی">
        <a href="<?php echo esc_url(takav_view_url('track')); ?>">پیگیری سفارش</a>
        <a href="<?php echo esc_url(takav_info_url('about')); ?>">درباره ما</a>
        <a href="<?php echo esc_url(takav_info_url('contact')); ?>">تماس با ما</a>
        <a href="<?php echo esc_url(takav_info_url('terms')); ?>">قوانین و مقررات</a>
    </nav>
    <div class="enamad-seal"><?php takav_enamad_seal(); ?></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
