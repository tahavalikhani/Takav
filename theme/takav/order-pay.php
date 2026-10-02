<?php
/**
 * The step between checkout and the bank (WooCommerce's order-pay "receipt" page).
 * ZarinPal redirects to the bank from the receipt hook. Running that hook before any HTML is sent means the
 * redirect always works; WooCommerce's own page runs it mid-page, where a redirect can fail on many hosts.
 * If the gateway refuses (e.g. merchant code not active yet), the customer sees why and where to go next.
 */
defined('ABSPATH') || exit;
$order_id = absint(get_query_var('order-pay'));
$order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$order = $order_id ? wc_get_order($order_id) : false;
if ($order && !hash_equals($order->get_order_key(), $order_key)) $order = false;
if ($order && !$order->needs_payment()) {
    wp_safe_redirect($order->get_checkout_order_received_url());
    exit;
}
$gateway_output = '';
if ($order) {
    ob_start();
    do_action('woocommerce_receipt_' . $order->get_payment_method(), $order->get_id());
    $gateway_output = trim(ob_get_clean());
}
$has_error = function_exists('wc_notice_count') && wc_notice_count('error') > 0;
$contact = takav_contact();
get_header(); ?>
<main id="main" class="done-page wrap">
<?php if ($order && $gateway_output !== '' && !$has_error) : ?>
    <div class="done-card">
        <h1>در حال انتقال به درگاه پرداخت…</h1>
        <div class="done-gateway"><?php echo $gateway_output; // The payment gateway's own form or script. ?></div>
    </div>
<?php else : ?>
    <div class="done-card is-failed">
        <span class="done-mark" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M26 14v16M26 37v1"/></svg></span>
        <h1><?php echo $order ? 'اتصال به درگاه پرداخت انجام نشد' : 'سفارش پیدا نشد'; ?></h1>
        <?php if ($order) : ?>
        <p>سفارشت ثبت شده ولی هنوز پرداخت نشده و پولی از حسابت کم نشده است. چند لحظه بعد دوباره امتحان کن؛ اگر باز هم نشد، به ما خبر بده.</p>
        <?php takav_notices(); ?>
        <p class="done-code-line">شمارهٔ سفارش: <bdi><?php echo esc_html($order->get_order_number()); ?></bdi></p>
        <?php endif; ?>
        <div class="done-actions">
            <?php if ($order) : ?><a class="done-action is-primary" href="<?php echo esc_url($order->get_checkout_payment_url(true)); ?>">تلاش دوباره برای پرداخت</a><?php endif; ?>
            <a class="done-action" href="<?php echo esc_url($order ? add_query_arg('order', $order->get_order_number(), takav_view_url('track')) : takav_view_url('track')); ?>">پیگیری سفارش</a>
            <?php if ($contact['instagram']) : ?><a class="done-action" href="<?php echo esc_url($contact['instagram']); ?>" target="_blank" rel="noopener">پیام در اینستاگرام</a><?php endif; ?>
        </div>
    </div>
<?php endif; ?>
</main>
<?php get_footer(); ?>
