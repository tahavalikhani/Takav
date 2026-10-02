<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main" class="collection-page wrap">
    <header class="collection-heading">
        <h1>کالکشن اول</h1>
        <p>مشکی <span aria-hidden="true">/</span> نارنجی</p>
    </header>
    <div class="product-grid">
        <?php foreach (takav_catalog() as $id => $product) :
            $detail_image = $id === 'hoodie' ? 'takav-embroidery' : 'takav-hem-detail';
            $wc_product = takav_wc_product($id);
            $on_sale = takav_can_buy($wc_product); ?>
        <article class="product-card">
            <a class="product-link" href="<?php echo esc_url(takav_product_url($id)); ?>">
                <div class="product-image-wrap">
                    <?php takav_product_image($product, 'product-image', true); ?>
                    <img class="product-hover-image" src="<?php echo esc_url(takav_asset('images/' . $detail_image . '-640.webp')); ?>" alt="" width="640" height="640" loading="lazy" aria-hidden="true">
                    <?php if ($on_sale && takav_first_discount_active()) : ?><span class="product-status is-discount"><?php echo esc_html(takav_fa_digits(takav_first_discount_active())); ?>٪ تخفیف</span><?php endif; ?>
                    <?php if (!$on_sale) : ?><span class="product-status"><?php echo $wc_product && $wc_product->is_purchasable() ? 'ناموجود' : 'به‌زودی'; ?></span><?php endif; ?>
                </div>
                <div class="product-info">
                    <div><h2><?php echo esc_html($product['name']); ?></h2><p>مشکی / نارنجی<?php if ($on_sale) echo '<span class="card-price">' . wp_kses_post($wc_product->get_price_html()) . '</span>'; ?></p></div>
                    <?php takav_icon('arrow'); ?>
                </div>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
    <a class="collection-invite" href="<?php echo esc_url(takav_view_url('collection-one')); ?>"><span>کالکشن ۰۱ را ببین</span><span aria-hidden="true">↗</span></a>
</main>
<?php get_footer(); ?>
