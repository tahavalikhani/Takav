<?php
/** Order tracking: tracking number + the mobile number used on the order. No account needed. */
defined('ABSPATH') || exit;
// phpcs:disable WordPress.Security.NonceVerification -- read-only lookup that needs both the number and the phone; rate-limited instead.
$number = isset($_REQUEST['order']) ? sanitize_text_field(wp_unslash($_REQUEST['order'])) : '';
$phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
$order = null;
$error = '';
if (isset($_POST['takav_track'])) {
    if (!takav_shop_ready()) {
        $error = 'پیگیری سفارش هنوز فعال نیست.';
    } elseif (!takav_track_allowed()) {
        $error = 'چند بار پشت سر هم جست‌وجو شد. چند دقیقهٔ دیگر دوباره امتحان کن.';
    } else {
        $order = takav_find_order($number, $phone);
        if (!$order) $error = 'سفارشی با این کد پیگیری و شماره موبایل پیدا نشد.';
    }
}
// phpcs:enable
$steps = array('ثبت سفارش', 'آماده‌سازی (حدود ' . takav_fa_digits(takav_preorder_days()) . ' روز)', 'ارسال با تیپاکس');
get_header(); ?>
<main id="main" class="track-page wrap">
    <div class="cart-heading"><p><bdi>TAKAV / TRACK</bdi></p><h1>پیگیری سفارش</h1><span>وضعیت سفارشت را بدون عضویت، با کد پیگیری و شماره موبایل ببین.</span></div>
    <section class="track-howto" aria-labelledby="track-howto-title">
        <h2 id="track-howto-title">چطور کار می‌کند؟</h2>
        <ol>
            <li><strong>کد پیگیری</strong> را وارد کن؛ همان عددی که بعد از پرداخت در صفحهٔ «سفارشت ثبت شد» دیدی (در ایمیل سفارش هم هست).</li>
            <li><strong>شماره موبایلی</strong> را که با آن سفارش دادی وارد کن و «نمایش وضعیت» را بزن.</li>
            <li>مرحلهٔ سفارشت را می‌بینی: <strong>ثبت سفارش</strong> ← <strong>آماده‌سازی</strong> (حدود <?php echo esc_html(takav_fa_digits(takav_preorder_days())); ?> روز) ← <strong>ارسال با تیپاکس</strong>. هر تغییری در سفارشت همین‌جا نشان داده می‌شود و تا وقتی این صفحه باز است، هر دقیقه خودش به‌روز می‌شود.</li>
        </ol>
        <?php if (takav_telegram_url()) : ?><p>جزئیات مراحل آماده‌سازی را در <a href="<?php echo esc_url(takav_telegram_url()); ?>" target="_blank" rel="noopener">تلگرام تکاو</a> هم می‌گذاریم.</p><?php endif; ?>
    </section>
    <form class="track-form" method="post" action="<?php echo esc_url(takav_view_url('track')); ?>">
        <input type="hidden" name="takav_track" value="1">
        <p class="field"><label for="track-order">کد پیگیری</label><input id="track-order" name="order" type="text" inputmode="numeric" dir="ltr" required value="<?php echo esc_attr($number); ?>" data-digits></p>
        <p class="field"><label for="track-phone">شماره موبایل</label><input id="track-phone" name="phone" type="tel" inputmode="numeric" dir="ltr" autocomplete="tel" placeholder="09123456789" required value="<?php echo esc_attr($phone); ?>" data-phone></p>
        <button type="submit">نمایش وضعیت</button>
    </form>
    <?php if ($error) : ?><ul class="shop-notices" role="alert"><li class="shop-notice is-error"><?php echo esc_html($error); ?></li></ul><?php endif; ?>
    <?php if ($order) : $progress = takav_order_progress($order); ?>
    <section class="track-result" aria-labelledby="track-title" tabindex="-1" data-live>
        <p class="track-live" aria-live="polite"><span class="track-live-dot" aria-hidden="true"></span>به‌روزرسانی خودکار هر دقیقه · آخرین بررسی: <time data-live-time><?php echo esc_html(takav_fa_digits(wp_date('H:i'))); ?></time></p>
        <div class="track-head">
            <h2 id="track-title">سفارش <bdi><?php echo esc_html($order->get_order_number()); ?></bdi></h2>
            <span class="track-status"><?php echo esc_html(takav_status_label($order)); ?></span>
        </div>
        <?php if ($progress >= 0) : ?>
        <ol class="track-steps">
            <?php foreach ($steps as $index => $label) : ?>
            <li class="<?php echo $index <= $progress ? 'is-done' : ''; ?>"<?php if ($index === $progress) echo ' aria-current="step"'; ?>><?php echo esc_html($label); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>
        <dl class="track-facts">
            <?php if ($order->get_date_created()) : ?><div><dt>تاریخ ثبت</dt><dd><?php echo esc_html(takav_fa_digits(wc_format_datetime($order->get_date_created()))); ?></dd></div><?php endif; ?>
            <div><dt>مبلغ</dt><dd><?php echo wp_kses_post($order->get_formatted_order_total()); ?></dd></div>
            <div><dt>کالاها</dt><dd><?php echo esc_html(implode('، ', array_map(function ($item) { return $item->get_name() . ' × ' . takav_fa_digits($item->get_quantity()); }, $order->get_items()))); ?></dd></div>
        </dl>
        <?php $notes = $order->get_customer_order_notes(); if ($notes) : ?>
        <div class="track-notes">
            <h3>پیام‌های فروشگاه</h3>
            <ul>
            <?php foreach ($notes as $note) : ?>
                <li><time><?php echo esc_html(takav_fa_digits(date_i18n(get_option('date_format'), strtotime($note->comment_date)))); ?></time><?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (!$order->needs_payment()) takav_telegram_button('done-telegram'); ?>
        <?php if ($order->needs_payment()) : ?><a class="done-primary" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>">پرداخت سفارش</a><?php endif; ?>
    </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
