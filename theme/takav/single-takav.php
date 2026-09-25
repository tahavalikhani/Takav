<?php
/** Dedicated collection product page. No commerce integration. */
defined('ABSPATH') || exit;
$id = takav_current_product_id();
$catalog = takav_catalog();
if (!$id || !isset($catalog[$id])) { get_template_part('404'); return; }
$product = $catalog[$id];
$other_id = $id === 'hoodie' ? 'pants' : 'hoodie';
$other = $catalog[$other_id];
get_header();
$id = takav_current_product_id();
?>
<main id="main" class="product-page wrap">
    <nav class="breadcrumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url(home_url('/')); ?>">کالکشن اول</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html($product['name']); ?></span></nav>
    <div class="product-layout">
        <section class="product-gallery" aria-label="عکس‌های <?php echo esc_attr($product['name']); ?>">
            <div class="gallery-stage"><?php takav_product_image($product, 'gallery-main-image', true); ?></div>
            <div class="gallery-bottom">
                <div class="gallery-thumbnails" aria-label="انتخاب عکس">
                    <?php foreach ($product['gallery'] as $image => $label) : ?>
                    <a href="<?php echo esc_url(takav_asset('images/' . $image . '.jpg')); ?>" data-gallery-image="<?php echo esc_url(takav_asset('images/' . $image . '-1280.webp')); ?>" data-gallery-alt="<?php echo esc_attr($label); ?>" <?php if ($image === $product['image']) echo 'aria-current="true"'; ?>><img src="<?php echo esc_url(takav_asset('images/' . $image . '-640.webp')); ?>" alt="<?php echo esc_attr($label); ?>" width="64" height="64"></a>
                    <?php endforeach; ?>
                </div>
                <span class="gallery-count" aria-hidden="true">۰۱ / <?php echo $id === 'hoodie' ? '۰۳' : '۰۴'; ?></span>
            </div>
            <p class="gallery-status sr-only" role="status" aria-live="polite">نمای کامل محصول</p>
        </section>
        <section class="product-summary" aria-labelledby="product-title">
            <p class="product-edition"><bdi>COLLECTION 01</bdi></p>
            <h1 id="product-title"><?php echo esc_html($product['name']); ?></h1>
            <div class="product-color"><span class="color-swatch" aria-hidden="true"></span>مشکی / نارنجی</div>
            <div class="purchase-state"><button type="button" data-add-cart="<?php echo esc_attr($id); ?>">افزودن به سبد نمایشی</button><p>پیش‌نمایش سبد خرید · ثبت سفارش و پرداخت فعال نیست.</p><p class="cart-feedback" role="status" aria-live="polite"></p></div>
            <details class="product-specs" open><summary>جزئیات محصول<span aria-hidden="true">+</span></summary><ul><?php foreach ($product['features'] as $feature) : ?><li><?php echo esc_html($feature); ?></li><?php endforeach; ?></ul></details>
            <aside class="matching-product" aria-labelledby="matching-title"><h2 id="matching-title">همراه این محصول</h2><a href="<?php echo esc_url(takav_product_url($other_id)); ?>"><img src="<?php echo esc_url(takav_asset('images/' . $other['image'] . '-640.webp')); ?>" width="80" height="100" alt="" loading="lazy"><span><?php echo esc_html($other['name']); ?><small>مشکی / نارنجی</small></span><?php takav_icon('arrow'); ?></a></aside>
        </section>
    </div>
</main>
<?php get_footer(); ?>
