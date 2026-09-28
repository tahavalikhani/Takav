<?php defined('ABSPATH') || exit;
$shop = takav_shop_ready() && WC()->cart;
if ($shop) WC()->cart->calculate_totals();
$items = $shop ? WC()->cart->get_cart() : array();
get_header(); ?>
<main id="main" class="cart-page wrap">
    <div class="cart-heading"><p><bdi>TAKAV / CART</bdi></p><h1>سبد خرید</h1><span>بدون عضویت؛ فقط نام، شماره و نشانی.</span></div>
    <?php takav_notices(); ?>
    <div id="cart-items">
    <?php if (!$items) : ?>
        <p class="cart-empty">سبد هنوز خالی است.</p>
    <?php else :
        foreach ($items as $key => $item) :
            $product = $item['data'];
            $catalog_id = takav_catalog_id($product);
            $name = $catalog_id ? takav_catalog()[$catalog_id]['name'] : $product->get_name();
            $link = $catalog_id ? takav_product_url($catalog_id) : get_permalink($item['product_id']);
            $variation = takav_cart_item_options($item);
            $quantity = (int) $item['quantity']; ?>
        <div class="cart-row">
            <a href="<?php echo esc_url($link); ?>" tabindex="-1" aria-hidden="true">
                <?php if ($catalog_id) : ?><img src="<?php echo esc_url(takav_asset('images/' . takav_catalog()[$catalog_id]['image'] . '-640.webp')); ?>" alt="" width="110" height="136"><?php else : echo wp_kses_post($product->get_image('thumbnail')); endif; ?>
            </a>
            <div>
                <h2><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($name); ?></a></h2>
                <p>مشکی / نارنجی<?php if ($variation) echo ' · ' . esc_html($variation); ?></p>
                <p class="cart-price"><?php echo wp_kses_post(WC()->cart->get_product_subtotal($product, $quantity)); ?></p>
                <form class="cart-actions" method="post">
                    <?php wp_nonce_field('takav-cart', '_takav_cart'); ?>
                    <input type="hidden" name="takav_cart_key" value="<?php echo esc_attr($key); ?>">
                    <button type="submit" name="takav_qty" value="<?php echo esc_attr($quantity - 1); ?>" aria-label="کم کردن <?php echo esc_attr($name); ?>">−</button>
                    <span aria-label="تعداد"><?php echo esc_html(takav_fa_digits($quantity)); ?></span>
                    <button type="submit" name="takav_qty" value="<?php echo esc_attr($quantity + 1); ?>" aria-label="اضافه کردن <?php echo esc_attr($name); ?>">+</button>
                </form>
            </div>
            <form method="post">
                <?php wp_nonce_field('takav-cart', '_takav_cart'); ?>
                <input type="hidden" name="takav_cart_key" value="<?php echo esc_attr($key); ?>">
                <button type="submit" name="takav_qty" value="0">حذف<span class="sr-only"> <?php echo esc_html($name); ?></span></button>
            </form>
        </div>
        <?php endforeach; ?>
        <div class="cart-summary">
            <p><span>جمع سبد</span><strong><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong></p>
            <?php if (WC()->cart->needs_shipping()) : ?><p class="cart-note">ارسال با تیپاکس؛ هزینهٔ ارسال را هنگام تحویل می‌پردازی (پس‌کرایه).</p><?php endif; ?>
            <p class="cart-note">پیش‌فروش: حدود <?php echo esc_html(takav_fa_digits(takav_preorder_days())); ?> روز بعد از سفارش آماده و ارسال می‌شود.</p>
            <a class="cart-checkout" href="<?php echo esc_url(wc_get_checkout_url()); ?>">ادامه و ثبت سفارش <span aria-hidden="true">←</span></a>
        </div>
    <?php endif; ?>
    </div>
    <a class="cart-back" href="<?php echo esc_url(home_url('/')); ?>">دیدن محصولات <span aria-hidden="true">↗</span></a>
</main>
<?php get_footer(); ?>
