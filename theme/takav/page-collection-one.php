<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main" class="campaign-page">
    <section class="campaign-hero" aria-labelledby="campaign-title">
        <div class="campaign-copy"><p class="campaign-kicker"><bdi>TAKAV / COLLECTION 01</bdi></p><h1 id="campaign-title">خطی که<br><em>ادامه دارد.</em></h1><p>هودی و شلوار تکاو</p><a href="#campaign-pieces">دیدن مجموعه <span aria-hidden="true">↓</span></a></div>
        <div class="campaign-visual" aria-label="هودی و شلوار مشکی تکاو با جزئیات نارنجی"><img class="campaign-hoodie" src="<?php echo esc_url(takav_asset('images/takav-hoodie-1280.webp')); ?>" alt="هودی تکاو" width="1018" height="1280"><img class="campaign-pants" src="<?php echo esc_url(takav_asset('images/takav-pants-1280.webp')); ?>" alt="شلوار تکاو" width="1049" height="1280"><span class="campaign-orbit" aria-hidden="true"></span></div>
        <span class="campaign-index"><bdi>01 / 02</bdi></span>
    </section>
    <div class="campaign-ticker" aria-hidden="true"><div><span>TAKAV&nbsp; · &nbsp;COLLECTION 01&nbsp; · &nbsp;TAKAV&nbsp; · &nbsp;COLLECTION 01&nbsp; · &nbsp;</span><span>TAKAV&nbsp; · &nbsp;COLLECTION 01&nbsp; · &nbsp;TAKAV&nbsp; · &nbsp;COLLECTION 01&nbsp; · &nbsp;</span></div></div>
    <section id="campaign-pieces" class="campaign-pieces wrap" aria-label="محصولات کالکشن اول">
        <?php foreach (takav_catalog() as $id => $product) : ?>
        <a class="campaign-piece" href="<?php echo esc_url(takav_product_url($id)); ?>">
            <span class="campaign-piece-number"><bdi><?php echo esc_html($id === 'hoodie' ? '01' : '02'); ?> / 02</bdi></span>
            <span class="campaign-piece-photo"><?php takav_product_image($product); ?></span>
            <span class="campaign-piece-name"><?php echo esc_html($product['name']); ?><span aria-hidden="true">↗</span></span>
        </a>
        <?php endforeach; ?>
    </section>
    <section class="campaign-detail"><img src="<?php echo esc_url(takav_asset('images/takav-embroidery-1280.webp')); ?>" alt="گلدوزی نارنجی لوگوی تکاو روی پارچه مشکی" width="1280" height="964" loading="lazy"><div><span><bdi>THE DETAIL / 01</bdi></span><h2>مشکی.<br><em>با یک خط نارنجی.</em></h2><a href="<?php echo esc_url(home_url('/')); ?>">بازگشت به فروشگاه <span aria-hidden="true">↗</span></a></div></section>
</main>
<?php get_footer(); ?>
