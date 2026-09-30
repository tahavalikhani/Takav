<?php
/**
 * wp-admin → «سفارشات»: every order with its details on one screen, filters for paid / unpaid,
 * quick status buttons, CSV export, and the pre-order settings (Telegram link, days).
 * Reads orders through WooCommerce's API, so it works with HPOS and the classic order storage.
 * Digits stay Latin here so numbers can be copied and sorted.
 */
defined('ABSPATH') || exit;

function takav_admin_filters() {
    return array(
        'all' => array('همه', array()),
        'paid' => array('پرداخت موفق', array('processing', 'completed')),
        'unpaid' => array('پرداخت ناموفق یا انجام‌نشده', array('pending', 'failed', 'cancelled')),
        'preparing' => array('در حال آماده‌سازی', array('processing')),
        'shipped' => array('ارسال‌شده', array('completed')),
    );
}

/** Payment result for the badge: [label, css modifier]. */
function takav_admin_payment_state($order) {
    switch ($order->get_status()) {
        case 'processing':
        case 'completed':
            return array('پرداخت موفق', 'ok');
        case 'failed':
            return array('پرداخت ناموفق', 'bad');
        case 'cancelled':
            return array('لغو شده', 'bad');
        case 'refunded':
            return array('مبلغ برگشت داده شد', 'bad');
        case 'on-hold':
            return array('در انتظار بررسی', 'wait');
        default:
            return array('پرداخت انجام نشده', 'wait');
    }
}

function takav_admin_query_args() {
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
    $filter = isset($_GET['filter']) ? sanitize_key(wp_unslash($_GET['filter'])) : 'all';
    $filters = takav_admin_filters();
    if (!isset($filters[$filter])) $filter = 'all';
    $from = isset($_GET['from']) ? sanitize_text_field(wp_unslash($_GET['from'])) : '';
    $to = isset($_GET['to']) ? sanitize_text_field(wp_unslash($_GET['to'])) : '';
    $search = isset($_GET['q']) ? trim(sanitize_text_field(wp_unslash($_GET['q']))) : '';
    // phpcs:enable
    $args = array('limit' => -1, 'orderby' => 'date', 'order' => 'DESC', 'type' => 'shop_order');
    $args['status'] = $filters[$filter][1] ? array_map(function ($status) { return 'wc-' . $status; }, $filters[$filter][1]) : array_keys(wc_get_order_statuses());
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $args['date_created'] = ($from ?: '2000-01-01') . '...' . ($to ?: gmdate('Y-m-d', time() + DAY_IN_SECONDS));
    }
    return array($args, $filter, $from, $to, $search);
}

/** Orders for the current filters; the text search matches number, name, phone, city and address. */
function takav_admin_orders() {
    list($args, $filter, $from, $to, $search) = takav_admin_query_args();
    $orders = wc_get_orders($args);
    if ($search !== '') {
        $needle = takav_en_digits($search);
        $phone = takav_normalize_phone($needle);
        $orders = array_values(array_filter($orders, function ($order) use ($needle, $phone) {
            $haystack = implode(' ', array($order->get_order_number(), $order->get_billing_first_name(), $order->get_billing_city(), $order->get_billing_address_1(), $order->get_transaction_id()));
            return stripos($haystack, $needle) !== false || ($phone !== '' && strpos($order->get_billing_phone(), $phone) !== false);
        }));
    }
    return $orders;
}

function takav_admin_address($order) {
    $states = takav_provinces();
    $state = $order->get_billing_state();
    return implode('، ', array_filter(array(isset($states[$state]) ? $states[$state] : $state, $order->get_billing_city(), $order->get_billing_address_1())));
}

function takav_admin_items($order) {
    $lines = array();
    foreach ($order->get_items() as $item) {
        $lines[] = $item->get_name() . ' × ' . $item->get_quantity();
    }
    return $lines;
}

add_action('admin_menu', function () {
    if (!takav_shop_ready()) return;
    $preparing = count(wc_get_orders(array('status' => array('wc-processing'), 'limit' => -1, 'return' => 'ids', 'type' => 'shop_order')));
    $bubble = $preparing ? ' <span class="awaiting-mod count-' . $preparing . '"><span class="pending-count">' . $preparing . '</span></span>' : '';
    add_menu_page('سفارشات', 'سفارشات' . $bubble, 'edit_shop_orders', 'takav-orders', 'takav_admin_orders_page', 'dashicons-clipboard', 3);
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_takav-orders') return;
    wp_register_style('takav-admin-orders', false, array(), '1');
    wp_enqueue_style('takav-admin-orders');
    wp_add_inline_style('takav-admin-orders', '
        .takav-orders .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:16px 0}
        .takav-orders .card{margin:0;max-width:none;padding:14px 16px}.takav-orders .card strong{display:block;font-size:22px;margin-top:6px}
        .takav-orders .filters{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:12px 0}
        .takav-orders table td{vertical-align:top}.takav-orders .muted{color:#646970}
        .takav-orders .badge{display:inline-block;padding:2px 8px;border-radius:3px;font-size:12px;white-space:nowrap}
        .takav-orders .badge.ok{background:#e7f6ec;color:#116329}.takav-orders .badge.bad{background:#fcebea;color:#8a1f11}.takav-orders .badge.wait{background:#fff4e0;color:#8a5300}
        .takav-orders .actions form{display:inline}.takav-orders .actions .button{margin:0 0 4px 4px}
        .takav-orders .ltr{direction:ltr;unicode-bidi:isolate}
        .takav-orders{max-width:100%;overflow:hidden}.takav-orders .table-scroll{overflow-x:auto;max-width:100%}.takav-orders .table-scroll table{min-width:1100px}
        .takav-orders .cards{grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
    ');
});

function takav_admin_orders_page() {
    if (!current_user_can('edit_shop_orders')) return;
    if (!takav_shop_ready()) {
        echo '<div class="wrap"><h1>سفارشات</h1><p>برای دیدن سفارش‌ها، ووکامرس باید فعال باشد.</p></div>';
        return;
    }
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $tab = isset($_GET['tab']) && $_GET['tab'] === 'settings' ? 'settings' : 'orders';
    $page_url = admin_url('admin.php?page=takav-orders');
    echo '<div class="wrap takav-orders"><h1 class="wp-heading-inline">سفارشات</h1>';
    echo '<nav class="nav-tab-wrapper"><a class="nav-tab' . ($tab === 'orders' ? ' nav-tab-active' : '') . '" href="' . esc_url($page_url) . '">سفارش‌ها</a><a class="nav-tab' . ($tab === 'settings' ? ' nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'settings', $page_url)) . '">تنظیمات فروشگاه</a></nav>';
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['takav_done'])) echo '<div class="notice notice-success is-dismissible"><p>ذخیره شد.</p></div>';
    $tab === 'settings' ? takav_admin_settings_tab() : takav_admin_orders_tab($page_url);
    echo '</div>';
}

function takav_admin_orders_tab($page_url) {
    list(, $filter, $from, $to, $search) = takav_admin_query_args();
    $orders = takav_admin_orders();

    // Summary over all orders, independent of the current filter.
    $all = wc_get_orders(array('limit' => -1, 'type' => 'shop_order', 'status' => array_keys(wc_get_order_statuses())));
    $paid = array_filter($all, function ($order) { return $order->has_status(array('processing', 'completed')); });
    $revenue = array_sum(array_map(function ($order) { return (float) $order->get_total(); }, $paid));
    $count = function ($statuses) use ($all) {
        return count(array_filter($all, function ($order) use ($statuses) { return $order->has_status($statuses); }));
    };
    echo '<div class="cards">';
    echo '<div class="card">پرداخت موفق<strong>' . esc_html(count($paid)) . '</strong></div>';
    echo '<div class="card">جمع پرداخت‌های موفق<strong>' . wp_kses_post(wc_price($revenue)) . '</strong></div>';
    echo '<div class="card">در حال آماده‌سازی<strong>' . esc_html($count(array('processing'))) . '</strong></div>';
    echo '<div class="card">ارسال‌شده<strong>' . esc_html($count(array('completed'))) . '</strong></div>';
    echo '<div class="card">پرداخت ناموفق یا انجام‌نشده<strong>' . esc_html($count(array('pending', 'failed', 'cancelled'))) . '</strong></div>';
    echo '</div>';

    echo '<ul class="subsubsub">';
    $links = array();
    foreach (takav_admin_filters() as $key => $data) {
        $number = $data[1] ? $count($data[1]) : count($all);
        $links[] = '<li><a href="' . esc_url(add_query_arg('filter', $key, $page_url)) . '"' . ($filter === $key ? ' class="current"' : '') . '>' . esc_html($data[0]) . ' <span class="count">(' . esc_html($number) . ')</span></a>';
    }
    echo implode(' | </li>', $links) . '</li></ul><br class="clear">'; // Escaped above.

    echo '<form class="filters" method="get" action="' . esc_url(admin_url('admin.php')) . '"><input type="hidden" name="page" value="takav-orders"><input type="hidden" name="filter" value="' . esc_attr($filter) . '">';
    echo '<label>جست‌وجو <input type="search" name="q" value="' . esc_attr($search) . '" placeholder="شماره، نام، موبایل، شهر"></label>';
    echo '<label>از <input type="date" name="from" value="' . esc_attr($from) . '"></label><label>تا <input type="date" name="to" value="' . esc_attr($to) . '"></label>';
    echo '<button class="button">اعمال</button>';
    $export = wp_nonce_url(add_query_arg(array('action' => 'takav_orders_csv', 'filter' => $filter, 'q' => $search, 'from' => $from, 'to' => $to), admin_url('admin-post.php')), 'takav-orders-csv');
    echo '<a class="button" href="' . esc_url($export) . '">خروجی اکسل (CSV)</a></form>';

    if (!$orders) {
        echo '<p>سفارشی با این شرایط پیدا نشد.</p>';
        return;
    }
    $per_page = 30;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $paged = max(1, isset($_GET['paged']) ? absint($_GET['paged']) : 1);
    $pages = (int) ceil(count($orders) / $per_page);
    $shown = array_slice($orders, ($paged - 1) * $per_page, $per_page);

    echo '<div class="table-scroll"><table class="widefat striped"><thead><tr><th>شماره</th><th>تاریخ</th><th>مشتری</th><th>محصولات</th><th>مبلغ</th><th>پرداخت</th><th>وضعیت</th><th>نشانی ارسال</th><th>کارها</th></tr></thead><tbody>';
    foreach ($shown as $order) {
        list($pay_label, $pay_class) = takav_admin_payment_state($order);
        $date = $order->get_date_created();
        echo '<tr>';
        echo '<td><strong>' . esc_html($order->get_order_number()) . '</strong></td>';
        echo '<td>' . esc_html($date ? $date->date_i18n('Y-m-d H:i') : '') . '</td>';
        echo '<td>' . esc_html($order->get_billing_first_name()) . '<br><a class="ltr" href="tel:' . esc_attr($order->get_billing_phone()) . '">' . esc_html($order->get_billing_phone()) . '</a>' . ($order->get_billing_email() ? '<br><span class="muted ltr">' . esc_html($order->get_billing_email()) . '</span>' : '') . '</td>';
        echo '<td>' . implode('<br>', array_map('esc_html', takav_admin_items($order))) . '</td>';
        echo '<td>' . wp_kses_post($order->get_formatted_order_total()) . '</td>';
        echo '<td><span class="badge ' . esc_attr($pay_class) . '">' . esc_html($pay_label) . '</span>' . ($order->get_payment_method_title() ? '<br><span class="muted">' . esc_html($order->get_payment_method_title()) . '</span>' : '') . ($order->get_transaction_id() ? '<br><span class="muted">کد رهگیری: <span class="ltr">' . esc_html($order->get_transaction_id()) . '</span></span>' : '') . '</td>';
        echo '<td>' . esc_html(takav_status_label($order)) . '</td>';
        echo '<td>' . esc_html(takav_admin_address($order)) . ($order->get_billing_postcode() ? '<br><span class="muted">کد پستی: ' . esc_html($order->get_billing_postcode()) . '</span>' : '') . ($order->get_customer_note() ? '<br><span class="muted">یادداشت: ' . esc_html($order->get_customer_note()) . '</span>' : '') . '</td>';
        echo '<td class="actions">';
        foreach (array('processing' => 'در حال آماده‌سازی', 'completed' => 'ارسال شد') as $status => $label) {
            if ($order->has_status($status) || !$order->has_status(array('processing', 'completed'))) continue;
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="takav_order_status"><input type="hidden" name="order_id" value="' . esc_attr($order->get_id()) . '"><input type="hidden" name="status" value="' . esc_attr($status) . '">';
            wp_nonce_field('takav-order-status-' . $order->get_id());
            echo '<button class="button button-small">' . esc_html($label) . '</button></form>';
        }
        echo '<a class="button button-small" href="' . esc_url($order->get_edit_order_url()) . '">جزئیات کامل</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    if ($pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => $pages))) . '</div></div>';
    }
}

add_action('admin_post_takav_order_status', function () {
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    check_admin_referer('takav-order-status-' . $order_id);
    if (!current_user_can('edit_shop_orders')) wp_die('دسترسی ندارید.');
    $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';
    $order = wc_get_order($order_id);
    if ($order && in_array($status, array('processing', 'completed'), true)) {
        $order->update_status($status, 'از صفحهٔ «سفارشات».');
    }
    wp_safe_redirect(add_query_arg('takav_done', 1, wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=takav-orders')));
    exit;
});

/** Explicit delimiter/enclosure/escape: PHP 8.4 warns when they are left out. */
function takav_csv_row($handle, $fields) {
    fputcsv($handle, $fields, ',', '"', '\\');
}

add_action('admin_post_takav_orders_csv', function () {
    check_admin_referer('takav-orders-csv');
    if (!current_user_can('edit_shop_orders') || !takav_shop_ready()) wp_die('دسترسی ندارید.');
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="takav-orders-' . gmdate('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // Lets Excel read Persian text.
    takav_csv_row($out, array('شماره', 'تاریخ', 'نام', 'موبایل', 'ایمیل', 'محصولات', 'مبلغ', 'ارز', 'پرداخت', 'روش پرداخت', 'کد رهگیری پرداخت', 'وضعیت', 'استان', 'شهر', 'نشانی', 'کد پستی'));
    $states = takav_provinces();
    foreach (takav_admin_orders() as $order) {
        $date = $order->get_date_created();
        $state = $order->get_billing_state();
        list($pay_label) = takav_admin_payment_state($order);
        takav_csv_row($out, array(
            $order->get_order_number(), $date ? $date->date_i18n('Y-m-d H:i') : '', $order->get_billing_first_name(), $order->get_billing_phone(), $order->get_billing_email(),
            implode(' | ', takav_admin_items($order)), $order->get_total(), $order->get_currency(), $pay_label, $order->get_payment_method_title(), $order->get_transaction_id(),
            takav_status_label($order), isset($states[$state]) ? $states[$state] : $state, $order->get_billing_city(), $order->get_billing_address_1(), $order->get_billing_postcode(),
        ));
    }
    fclose($out);
    exit;
});

function takav_admin_settings_tab() {
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="takav_preorder_settings">';
    wp_nonce_field('takav-preorder-settings');
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row"><label for="takav_telegram_url">لینک تلگرام</label></th><td><input id="takav_telegram_url" name="takav_telegram_url" type="url" class="regular-text ltr" placeholder="https://t.me/..." value="' . esc_attr(takav_telegram_url()) . '"><p class="description">بعد از ثبت سفارش، در صفحهٔ پایان خرید، صفحهٔ پیگیری و ایمیل سفارش به مشتری نشان داده می‌شود. خالی بگذاری، دکمه نمایش داده نمی‌شود.</p></td></tr>';
    echo '<tr><th scope="row"><label for="takav_preorder_days">زمان آماده‌سازی (روز)</label></th><td><input id="takav_preorder_days" name="takav_preorder_days" type="number" min="1" max="120" class="small-text" value="' . esc_attr(takav_preorder_days()) . '"><p class="description">در صفحهٔ محصول، سبد، پرداخت و پیگیری نوشته می‌شود: «حدود … روز».</p></td></tr>';
    echo '</tbody></table>';
    echo '<h2>اطلاعات تماس</h2><p class="description">در صفحهٔ «تماس با ما» نشان داده می‌شود. هر خانه‌ای را خالی بگذاری، نمایش داده نمی‌شود.</p>';
    echo '<table class="form-table" role="presentation"><tbody>';
    $contact = takav_contact();
    $fields = array(
        'phone' => array('تلفن', 'text', 'ltr', '09390709672'),
        'email' => array('ایمیل', 'email', 'ltr', 'info@takav.shop'),
        'hours' => array('ساعت پاسخ‌گویی', 'text', '', 'شنبه تا چهارشنبه، ۱۰ تا ۱۸'),
        'instagram' => array('لینک اینستاگرام', 'url', 'ltr', 'https://instagram.com/...'),
    );
    foreach ($fields as $key => $field) {
        $id = 'takav_contact_' . $key;
        echo '<tr><th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($field[0]) . '</label></th><td>';
        if ($field[1] === 'textarea') {
            echo '<textarea id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" rows="3" class="large-text">' . esc_textarea($contact[$key]) . '</textarea>';
        } else {
            echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" type="' . esc_attr($field[1]) . '" class="regular-text' . ($field[2] ? ' ' . esc_attr($field[2]) : '') . '" placeholder="' . esc_attr($field[3]) . '" value="' . esc_attr($contact[$key]) . '">';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
    submit_button('ذخیره');
    echo '</form>';
}

add_action('admin_post_takav_preorder_settings', function () {
    check_admin_referer('takav-preorder-settings');
    if (!current_user_can('manage_woocommerce')) wp_die('دسترسی ندارید.');
    $url = isset($_POST['takav_telegram_url']) ? esc_url_raw(trim(wp_unslash($_POST['takav_telegram_url'])), array('https', 'http')) : '';
    update_option('takav_telegram_url', $url);
    $days = isset($_POST['takav_preorder_days']) ? absint($_POST['takav_preorder_days']) : 20;
    update_option('takav_preorder_days', max(1, min(120, $days)));
    foreach (array('phone', 'email', 'hours', 'instagram') as $key) {
        $raw = isset($_POST['takav_contact_' . $key]) ? wp_unslash($_POST['takav_contact_' . $key]) : '';
        if ($key === 'email') $value = sanitize_email($raw);
        elseif ($key === 'instagram') $value = esc_url_raw(trim($raw), array('https', 'http'));
        else $value = sanitize_text_field($raw);
        update_option('takav_contact_' . $key, $value);
    }
    wp_safe_redirect(admin_url('admin.php?page=takav-orders&tab=settings&takav_done=1'));
    exit;
});
