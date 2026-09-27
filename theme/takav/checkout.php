<?php
/**
 * Short guest checkout in steps: details → address → delivery and payment.
 * Posts WooCommerce's normal checkout fields, so WooCommerce creates the order and runs the payment plugin.
 * Without JavaScript all steps show at once and the form still works.
 */
defined('ABSPATH') || exit;
$cart = WC()->cart;
if (WC()->customer && !WC()->customer->get_billing_country()) {
    WC()->customer->set_billing_country('IR');
    WC()->customer->set_shipping_country('IR');
}
$gateways = $cart->needs_payment() ? WC()->payment_gateways()->get_available_payment_gateways() : array();
$chosen_gateway = WC()->session ? WC()->session->get('chosen_payment_method') : '';
if (!isset($gateways[$chosen_gateway])) $chosen_gateway = $gateways ? current(array_keys($gateways)) : '';
$terms_page = wc_terms_and_conditions_page_id();
$field = function ($key) {
    return esc_attr(takav_checkout_value($key));
};
get_header(); ?>
<main id="main" class="checkout-page wrap">
    <div class="cart-heading"><p><bdi>TAKAV / CHECKOUT</bdi></p><h1>تکمیل سفارش</h1><span>بدون عضویت. آخر کار یک کد پیگیری می‌گیری.</span></div>
    <ol class="checkout-progress" aria-label="مراحل سفارش">
        <li data-step-marker="0">مشخصات</li>
        <li data-step-marker="1">نشانی</li>
        <li data-step-marker="2">پرداخت</li>
        <li>کد پیگیری</li>
    </ol>
    <?php takav_notices(); ?>
    <div class="checkout-layout">
        <form class="takav-checkout" method="post" action="<?php echo esc_url(wc_get_checkout_url()); ?>" data-review-url="<?php echo esc_url(WC_AJAX::get_endpoint('takav_review')); ?>" data-review-nonce="<?php echo esc_attr(wp_create_nonce('takav-review')); ?>">
            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
            <input type="hidden" name="woocommerce_checkout_place_order" value="1">
            <input type="hidden" name="billing_country" value="IR">

            <fieldset class="checkout-step" data-step="0">
                <legend><span class="step-number">۰۱</span><span tabindex="-1" data-step-title>مشخصات تو</span></legend>
                <p class="field"><label for="billing_first_name">نام و نام خانوادگی</label><input id="billing_first_name" name="billing_first_name" type="text" autocomplete="name" required value="<?php echo $field('billing_first_name'); ?>"></p>
                <p class="field"><label for="billing_phone">شماره موبایل</label><input id="billing_phone" name="billing_phone" type="tel" inputmode="numeric" autocomplete="tel" dir="ltr" placeholder="09123456789" required value="<?php echo $field('billing_phone'); ?>" data-phone><small>کد پیگیری و هماهنگی ارسال با همین شماره است.</small></p>
                <p class="field"><label for="billing_email">ایمیل <span>(اختیاری)</span></label><input id="billing_email" name="billing_email" type="email" autocomplete="email" dir="ltr" value="<?php echo $field('billing_email'); ?>"></p>
                <button class="step-next" type="button" data-next>ادامه</button>
            </fieldset>

            <fieldset class="checkout-step" data-step="1">
                <legend><span class="step-number">۰۲</span><span tabindex="-1" data-step-title>نشانی ارسال</span></legend>
                <div class="field-row">
                    <p class="field"><label for="billing_state">استان</label><select id="billing_state" name="billing_state" autocomplete="address-level1" required data-review-field>
                        <option value="">انتخاب استان</option>
                        <?php $state = takav_checkout_value('billing_state'); foreach (takav_provinces() as $code => $name) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($state, $code); ?>><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select></p>
                    <p class="field"><label for="billing_city">شهر</label><input id="billing_city" name="billing_city" type="text" autocomplete="address-level2" required value="<?php echo $field('billing_city'); ?>" data-review-field></p>
                </div>
                <p class="field"><label for="billing_address_1">نشانی</label><textarea id="billing_address_1" name="billing_address_1" rows="3" autocomplete="street-address" required placeholder="خیابان، کوچه، پلاک، واحد"><?php echo esc_textarea(takav_checkout_value('billing_address_1')); ?></textarea></p>
                <p class="field"><label for="billing_postcode">کد پستی <span>(اختیاری)</span></label><input id="billing_postcode" name="billing_postcode" type="text" inputmode="numeric" autocomplete="postal-code" dir="ltr" value="<?php echo $field('billing_postcode'); ?>" data-postcode data-review-field></p>
                <div class="step-buttons"><button class="step-back" type="button" data-back>بازگشت</button><button class="step-next" type="button" data-next>ادامه</button></div>
            </fieldset>

            <fieldset class="checkout-step" data-step="2">
                <legend><span class="step-number">۰۳</span><span tabindex="-1" data-step-title>ارسال و پرداخت</span></legend>
                <div class="checkout-review" aria-live="polite"><?php echo takav_checkout_review(); // Escaped inside. ?></div>
                <?php if ($cart->needs_payment()) : ?>
                <fieldset class="choice-group"><legend>روش پرداخت</legend>
                    <?php if (!$gateways) : ?><p class="choice-empty">هنوز روش پرداختی فعال نشده است.</p><?php endif; ?>
                    <?php foreach ($gateways as $gateway_id => $gateway) : ?>
                    <div class="choice">
                        <label><input type="radio" name="payment_method" value="<?php echo esc_attr($gateway_id); ?>" <?php checked($chosen_gateway, $gateway_id); ?> required>
                        <span class="choice-body"><span class="choice-title"><?php echo wp_kses_post($gateway->get_title()); ?></span></span></label>
                        <?php if ($gateway->has_fields() || $gateway->get_description()) : ?><div class="choice-detail"><?php $gateway->payment_fields(); ?></div><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </fieldset>
                <?php endif; ?>
                <?php if ($terms_page) : ?>
                <p class="terms"><input type="hidden" name="terms-field" value="1"><label><input type="checkbox" name="terms" value="1" required> <a href="<?php echo esc_url(get_permalink($terms_page)); ?>" target="_blank" rel="noopener">قوانین فروشگاه</a> را خواندم و می‌پذیرم.</label></p>
                <?php endif; ?>
                <div class="step-buttons"><button class="step-back" type="button" data-back>بازگشت</button><button class="step-submit" type="submit">ثبت سفارش</button></div>
            </fieldset>
        </form>

        <aside class="checkout-summary" aria-labelledby="summary-title">
            <h2 id="summary-title">سفارش تو</h2>
            <ul>
            <?php foreach ($cart->get_cart() as $item) :
                $product = $item['data'];
                $catalog_id = takav_catalog_id($product);
                $variation = takav_cart_item_options($item); ?>
                <li>
                    <?php if ($catalog_id) : ?><img src="<?php echo esc_url(takav_asset('images/' . takav_catalog()[$catalog_id]['image'] . '-640.webp')); ?>" alt="" width="56" height="70"><?php endif; ?>
                    <span><?php echo esc_html($catalog_id ? takav_catalog()[$catalog_id]['name'] : $product->get_name()); ?><small><?php echo esc_html(takav_fa_digits($item['quantity'])); ?> عدد<?php if ($variation) echo ' · ' . esc_html($variation); ?></small></span>
                    <strong><?php echo wp_kses_post($cart->get_product_subtotal($product, $item['quantity'])); ?></strong>
                </li>
            <?php endforeach; ?>
            </ul>
            <a href="<?php echo esc_url(takav_cart_url()); ?>">ویرایش سبد</a>
        </aside>
    </div>
</main>
<?php get_footer(); ?>
