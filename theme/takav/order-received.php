<?php
/**
 * Last step: a tick and the tracking number. A failed payment gets its own message and a retry button instead.
 * Payment plugins' thank-you hooks still run (bank-transfer details, payment verification).
 */
defined('ABSPATH') || exit;
$order_id = absint(get_query_var('order-received'));
$order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$order = $order_id ? wc_get_order(apply_filters('woocommerce_thankyou_order_id', $order_id)) : false;
if ($order && !hash_equals($order->get_order_key(), apply_filters('woocommerce_thankyou_order_key', $order_key))) $order = false;
$failed = $order && $order->has_status('failed');
get_header(); ?>
<main id="main" class="done-page wrap">
<?php if (!$order) : ?>
    <div class="done-card">
        <h1>سفارش پیدا نشد</h1>
        <p>اگر سفارش ثبت کرده‌ای، با کد پیگیری و شماره موبایل از صفحهٔ پیگیری وضعیتش را ببین.</p>
        <a class="done-primary" href="<?php echo esc_url(takav_view_url('track')); ?>">پیگیری سفارش</a>
    </div>
<?php elseif ($failed) : ?>
    <div class="done-card is-failed">
        <span class="done-mark" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="m18 18 16 16M34 18 18 34"/></svg></span>
        <h1>پرداخت انجام نشد</h1>
        <p>سفارش ثبت شد اما پرداخت کامل نشد. اگر مبلغی از حسابت کم شده، معمولاً تا ۷۲ ساعت به حسابت برمی‌گردد.</p>
        <a class="done-primary" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>">پرداخت دوباره</a>
        <p class="done-code-line">شمارهٔ سفارش: <bdi><?php echo esc_html($order->get_order_number()); ?></bdi></p>
    </div>
<?php else : ?>
    <ol class="checkout-progress is-complete" aria-label="مراحل سفارش"><li>مشخصات</li><li>نشانی</li><li>پرداخت</li><li aria-current="step">کد پیگیری</li></ol>
    <?php if (function_exists('wc_clear_notices') && WC()->session) wc_clear_notices(); // The tick page says it all; don't carry the gateway's message to the next page. ?>
    <div class="done-card">
        <span class="done-mark" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="m15 27 7.5 7.5L37 19"/></svg></span>
        <h1>سفارشت ثبت شد</h1>
        <p><?php echo esc_html($order->get_billing_first_name()); ?>، ممنون از خریدت. این کد را نگه دار؛ با آن و شماره موبایلت می‌توانی وضعیت سفارش را ببینی.</p>
        <div class="done-code">
            <span>کد پیگیری</span>
            <strong><bdi data-copy-source><?php echo esc_html($order->get_order_number()); ?></bdi></strong>
            <button type="button" data-copy hidden>کپی کد</button>
        </div>
        <?php if ($order->has_status(array('on-hold', 'pending'))) : ?><p class="done-status">وضعیت: <?php echo esc_html(takav_status_label($order)); ?></p><?php endif; ?>
        <?php if ($order->get_transaction_id()) : ?><p class="done-status">کد رهگیری پرداخت: <bdi><?php echo esc_html($order->get_transaction_id()); ?></bdi></p><?php endif; ?>
        <p class="done-preorder">پیش‌فروش: سفارشت حدود <?php echo esc_html(takav_fa_digits(takav_preorder_days())); ?> روز دیگر آماده می‌شود و با تیپاکس (پس‌کرایه) ارسال می‌شود.</p>
        <div class="done-gateway"><?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); do_action('woocommerce_thankyou', $order->get_id()); ?></div>
        <?php $contact = takav_contact(); ?>
        <nav class="done-actions" aria-label="قدم بعدی">
            <a class="done-action is-primary" href="<?php echo esc_url(add_query_arg('order', $order->get_order_number(), takav_view_url('track'))); ?>"><span class="done-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></span><span><strong>پیگیری سفارش</strong><small>وضعیت سفارشت را لحظه‌به‌لحظه ببین</small></span></a>
            <?php if ($contact['instagram']) : ?><a class="done-action" href="<?php echo esc_url($contact['instagram']); ?>" target="_blank" rel="noopener"><span class="done-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg></span><span><strong>اینستاگرام تکاو</strong><small>کالکشن و خبرهای تازه</small></span></a><?php endif; ?>
            <?php if (takav_telegram_url()) : ?><a class="done-action" href="<?php echo esc_url(takav_telegram_url()); ?>" target="_blank" rel="noopener"><span class="done-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m21 4-18 7 6 2 2 6 3-4 5 4z"/><path d="m9 13 8-6"/></svg></span><span><strong>تلگرام تکاو</strong><small>مراحل آماده‌سازی سفارش</small></span></a>
            <?php else : ?><a class="done-action" href="<?php echo esc_url(home_url('/')); ?>"><span class="done-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10 12 3l9 7v10H3z"/></svg></span><span><strong>بازگشت به فروشگاه</strong><small>دیدن کالکشن تکاو</small></span></a><?php endif; ?>
        </nav>
    </div>
    <section class="done-summary" aria-labelledby="done-summary-title">
        <h2 id="done-summary-title">خلاصهٔ سفارش</h2>
        <ul>
        <?php foreach ($order->get_items() as $item) : ?>
            <li><span><?php echo esc_html($item->get_name()); ?> <small>× <?php echo esc_html(takav_fa_digits($item->get_quantity())); ?></small></span><strong><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></strong></li>
        <?php endforeach; ?>
        </ul>
        <dl>
            <?php if ((float) $order->get_total_discount() > 0) : ?><div><dt>تخفیف</dt><dd>−<?php echo wp_kses_post(wc_price($order->get_total_discount(), array('currency' => $order->get_currency()))); ?></dd></div><?php endif; ?>
            <?php if ($order->get_shipping_method()) : ?><div><dt>ارسال</dt><dd><?php echo esc_html($order->get_shipping_method()); ?> · <?php echo takav_order_is_cash_on_delivery_freight($order) ? 'پس‌کرایه' : wp_kses_post(wc_price($order->get_shipping_total(), array('currency' => $order->get_currency()))); ?></dd></div><?php endif; ?>
            <?php if ($order->get_payment_method_title()) : ?><div><dt>روش پرداخت</dt><dd><?php echo esc_html($order->get_payment_method_title()); ?></dd></div><?php endif; ?>
            <div><dt>مبلغ کل</dt><dd><?php echo wp_kses_post($order->get_formatted_order_total()); ?></dd></div>
        </dl>
        <p class="done-address"><?php echo esc_html(implode('، ', array_filter(array($order->get_billing_state() ? (takav_provinces()[$order->get_billing_state()] ?? $order->get_billing_state()) : '', $order->get_billing_city(), $order->get_billing_address_1())))); ?> · <?php echo esc_html(takav_fa_digits($order->get_billing_phone())); ?></p>
    </section>
<?php endif; ?>
</main>
<?php get_footer(); ?>
