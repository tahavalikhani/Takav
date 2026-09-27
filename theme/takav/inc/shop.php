<?php
/**
 * WooCommerce ordering: guest checkout in short steps and order tracking.
 * Orders go through WooCommerce's own checkout, so stock, emails and payment plugins keep working.
 */
defined('ABSPATH') || exit;

function takav_shop_ready() {
    return class_exists('WooCommerce') && function_exists('WC');
}

function takav_fa_digits($value) {
    return strtr((string) $value, array('0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'));
}

function takav_en_digits($value) {
    return strtr((string) $value, array(
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ));
}

/** 0912 345 6789, +98912…, 0098912…, 98912…, 912…, ۰۹۱۲… → 09123456789. */
function takav_normalize_phone($phone) {
    $phone = preg_replace('/\D+/', '', takav_en_digits($phone));
    if (strpos($phone, '00') === 0) $phone = substr($phone, 2);
    if (strlen($phone) === 12 && strpos($phone, '98') === 0) $phone = '0' . substr($phone, 2);
    if (strlen($phone) === 10 && strpos($phone, '9') === 0) $phone = '0' . $phone;
    return $phone;
}

/** Published WooCommerce product for a catalogue item, matched by SKU (takav-hoodie, takav-pants). */
function takav_wc_product($id) {
    if (!takav_shop_ready()) return null;
    $product_id = wc_get_product_id_by_sku('takav-' . $id);
    $product = $product_id ? wc_get_product($product_id) : null;
    return $product && $product->get_status() === 'publish' ? $product : null;
}

function takav_can_buy($product) {
    return $product && $product->is_purchasable() && $product->is_in_stock();
}

/** Catalogue id (hoodie/pants) of a WooCommerce product or variation, or ''. */
function takav_catalog_id($product) {
    if (!$product) return '';
    $sku = $product->get_sku();
    if ($product->get_parent_id()) {
        $parent = wc_get_product($product->get_parent_id());
        $sku = $parent ? $parent->get_sku() : $sku;
    }
    $id = strpos((string) $sku, 'takav-') === 0 ? substr($sku, 6) : '';
    return isset(takav_catalog()[$id]) ? $id : '';
}

/** Chosen options of a cart line, e.g. "سایز: M". */
function takav_cart_item_options($item) {
    $product = $item['data'];
    $parts = array();
    if ($product->is_type('variation')) $parts[] = wc_get_formatted_variation($product, true, true);
    $data = trim(wc_get_formatted_cart_item_data($item, true));
    foreach ($data === '' ? array() : explode("\n", $data) as $line) {
        if (trim($line) !== '') $parts[] = trim($line);
    }
    return implode(' · ', array_unique(array_filter($parts)));
}

function takav_cart_url() {
    return takav_shop_ready() && wc_get_page_id('cart') > 0 ? wc_get_cart_url() : takav_view_url('cart');
}

function takav_cart_count() {
    return takav_shop_ready() && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}

function takav_is_checkout_form() {
    return takav_shop_ready() && is_checkout() && !is_wc_endpoint_url();
}

/** WooCommerce notices in the theme's own markup. */
function takav_notices() {
    if (!takav_shop_ready() || !function_exists('wc_get_notices') || !WC()->session) return;
    $all = wc_get_notices();
    wc_clear_notices();
    $items = '';
    foreach ($all as $type => $notices) {
        foreach ($notices as $notice) {
            $text = is_array($notice) ? $notice['notice'] : $notice;
            if (trim(wp_strip_all_tags($text)) === '') continue;
            $field = is_array($notice) && !empty($notice['data']['id']) ? $notice['data']['id'] : '';
            $items .= '<li class="shop-notice is-' . esc_attr($type) . '"' . ($field ? ' data-field="' . esc_attr($field) . '"' : '') . '>' . wp_kses_post($text) . '</li>';
        }
    }
    if ($items) echo '<ul class="shop-notices" role="alert">' . $items . '</ul>'; // Escaped per item above.
}

// ---------------------------------------------------------------------------
// Setup: theme support, products and page routing.
// ---------------------------------------------------------------------------

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
});

/** Creates the two products once, as drafts, so the owner only adds a price and publishes. */
function takav_seed_products() {
    if (!takav_shop_ready() || get_option('takav_products_seeded')) return;
    foreach (takav_catalog() as $id => $item) {
        if (wc_get_product_id_by_sku('takav-' . $id)) continue;
        $product = new WC_Product_Simple();
        $product->set_name($item['name']);
        $product->set_sku('takav-' . $id);
        $product->set_status('draft');
        $product->set_description($item['description']);
        $product->set_short_description(implode('، ', $item['features']));
        $image = takav_import_image($item['image']);
        if ($image) $product->set_image_id($image);
        $product->save();
    }
    update_option('takav_products_seeded', 1);
}

function takav_import_image($name) {
    $source = get_template_directory() . '/assets/images/' . $name . '.jpg';
    if (!file_exists($source)) return 0;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $tmp = wp_tempnam($name . '.jpg');
    if (!$tmp || !copy($source, $tmp)) return 0;
    $id = media_handle_sideload(array('name' => $name . '.jpg', 'tmp_name' => $tmp), 0);
    if (is_wp_error($id)) {
        @unlink($tmp);
        return 0;
    }
    return $id;
}

add_action('admin_init', function () {
    if (current_user_can('manage_woocommerce')) takav_seed_products();
});

add_filter('template_include', function ($template) {
    if (!takav_shop_ready()) return $template;
    if (is_wc_endpoint_url('order-received')) return get_template_directory() . '/order-received.php';
    if (takav_is_checkout_form()) return get_template_directory() . '/checkout.php';
    if (is_cart()) return get_template_directory() . '/page-cart.php';
    return $template;
}, 20);

// The collection pages are the storefront: send WooCommerce's own product and shop pages there.
add_action('template_redirect', function () {
    if (!takav_shop_ready() || takav_is_home_view()) return;
    if (is_product()) {
        $id = takav_catalog_id(wc_get_product(get_queried_object_id()));
        if ($id) {
            wp_safe_redirect(takav_product_url($id), 301);
            exit;
        }
    }
    if (is_shop() || is_product_taxonomy()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
}, 20);

// The theme's own script handles cart and checkout; WooCommerce's jQuery helpers are only kept on the
// payment pages, where a payment plugin may rely on them.
add_action('wp_enqueue_scripts', function () {
    if (!takav_shop_ready() || is_checkout() || is_account_page()) return;
    foreach (array('wc-add-to-cart', 'woocommerce', 'wc-cart-fragments', 'wc-jquery-blockui', 'wc-js-cookie') as $handle) {
        wp_dequeue_script($handle);
    }
}, 99);

// The theme styles its own shop pages; keep WooCommerce's CSS only where its own markup is shown.
add_filter('woocommerce_enqueue_styles', function ($styles) {
    return is_account_page() || is_wc_endpoint_url('order-pay') ? $styles : array();
});

add_filter('document_title_parts', function ($parts) {
    if (!takav_shop_ready()) return $parts;
    if (is_wc_endpoint_url('order-received')) $parts = array('title' => 'سفارش ثبت شد — تکاو');
    elseif (takav_is_checkout_form()) $parts = array('title' => 'تکمیل سفارش — تکاو');
    elseif (is_cart()) $parts = array('title' => 'سبد خرید — تکاو');
    return $parts;
}, 20);

// ---------------------------------------------------------------------------
// Cart.
// ---------------------------------------------------------------------------

add_filter('woocommerce_add_to_cart_redirect', function () {
    return takav_cart_url();
});

add_filter('wc_add_to_cart_message_html', function () {
    return 'به سبد اضافه شد.';
});

// WooCommerce 11 needs the variation id; the product form only sends the chosen size, so look it up first.
add_action('wp_loaded', function () {
    if (!takav_shop_ready() || empty($_REQUEST['add-to-cart']) || !empty($_REQUEST['variation_id'])) return; // phpcs:ignore WordPress.Security.NonceVerification
    $product = wc_get_product(absint($_REQUEST['add-to-cart'])); // phpcs:ignore WordPress.Security.NonceVerification
    if (!$product || !$product->is_type('variable')) return;
    $attributes = array();
    foreach ($_REQUEST as $key => $value) { // phpcs:ignore WordPress.Security.NonceVerification
        if (strpos($key, 'attribute_') === 0 && is_string($value)) $attributes[sanitize_title(wp_unslash($key))] = wc_clean(wp_unslash($value));
    }
    $variation_id = WC_Data_Store::load('product')->find_matching_product_variation($product, $attributes);
    if ($variation_id) $_REQUEST['variation_id'] = $_POST['variation_id'] = $variation_id;
}, 15);

// Quantity buttons and remove on the cart page (works without JavaScript).
add_action('wp_loaded', function () {
    if (!takav_shop_ready() || !isset($_POST['takav_cart_key'], $_POST['takav_qty'])) return;
    if (!wp_verify_nonce(isset($_POST['_takav_cart']) ? sanitize_text_field(wp_unslash($_POST['_takav_cart'])) : '', 'takav-cart')) return;
    $key = sanitize_text_field(wp_unslash($_POST['takav_cart_key']));
    $quantity = max(0, min(20, absint($_POST['takav_qty'])));
    if (WC()->cart->get_cart_item($key)) {
        $quantity ? WC()->cart->set_quantity($key, $quantity) : WC()->cart->remove_cart_item($key);
    }
    wc_nocache_headers();
    wp_safe_redirect(takav_cart_url());
    exit;
}, 25);

// Toman and Rial read "۲٬۴۰۰٬۰۰۰ تومان": amount first, then the unit.
add_filter('woocommerce_price_format', function ($format, $position) {
    return in_array(get_woocommerce_currency(), array('IRT', 'IRR', 'IRHT', 'IRHR'), true) ? '%2$s&nbsp;%1$s' : $format;
}, 10, 2);

// Prices with Persian digits on the storefront.
add_filter('formatted_woocommerce_price', function ($price) {
    return is_admin() && !wp_doing_ajax() ? $price : takav_fa_digits($price);
});

// ---------------------------------------------------------------------------
// Checkout: no account, a short list of fields.
// ---------------------------------------------------------------------------

add_filter('woocommerce_checkout_registration_required', '__return_false');
add_filter('woocommerce_checkout_registration_enabled', '__return_false');

add_filter('woocommerce_checkout_fields', function ($fields) {
    $labels = array(
        'first_name' => array('نام و نام خانوادگی', true),
        'phone' => array('شماره موبایل', true),
        'email' => array('ایمیل', false),
        'country' => array('کشور', true),
        'state' => array('استان', true),
        'city' => array('شهر', true),
        'address_1' => array('نشانی', true),
        'postcode' => array('کد پستی', false),
    );
    foreach (array('billing', 'shipping') as $group) {
        if (empty($fields[$group])) continue;
        foreach ($fields[$group] as $key => $field) {
            $name = substr($key, strlen($group) + 1);
            if (!isset($labels[$name])) {
                unset($fields[$group][$key]);
                continue;
            }
            $fields[$group][$key]['label'] = $labels[$name][0];
            $fields[$group][$key]['required'] = $labels[$name][1];
        }
    }
    return $fields;
}, 20);

add_filter('woocommerce_process_checkout_field_billing_phone', 'takav_normalize_phone');
add_filter('woocommerce_process_checkout_field_billing_postcode', function ($value) {
    return preg_replace('/\D/', '', takav_en_digits($value));
});

add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    if (!empty($data['billing_phone']) && !preg_match('/^09\d{9}$/', $data['billing_phone'])) {
        $errors->add('takav_phone', 'شماره موبایل را کامل وارد کن؛ مثل ۰۹۱۲۳۴۵۶۷۸۹.', array('id' => 'billing_phone'));
    }
    if (!empty($data['billing_postcode']) && !preg_match('/^\d{10}$/', $data['billing_postcode'])) {
        $errors->add('takav_postcode', 'کد پستی باید ۱۰ رقم باشد.', array('id' => 'billing_postcode'));
    }
}, 10, 2);

/** Iranian provinces with their Persian names. */
function takav_provinces() {
    $states = WC()->countries->get_states('IR');
    $names = array();
    foreach ((array) $states as $code => $name) {
        $names[$code] = preg_match('/\(([^)]+)\)/u', $name, $match) ? $match[1] : $name;
    }
    return $names;
}

function takav_checkout_value($key) {
    $value = WC()->checkout()->get_value($key);
    if ($key === 'billing_first_name' && !$value && WC()->customer) {
        $value = trim(WC()->customer->get_billing_first_name() . ' ' . WC()->customer->get_billing_last_name());
    }
    return is_string($value) ? $value : '';
}

/** Delivery choices and totals for step 3; also returned by the wc-ajax=takav_review request. */
function takav_checkout_review() {
    WC()->cart->calculate_totals();
    ob_start();
    if (WC()->cart->needs_shipping()) {
        $packages = WC()->shipping()->get_packages();
        echo '<fieldset class="choice-group"><legend>روش ارسال</legend>';
        $has_rates = false;
        foreach ($packages as $index => $package) {
            $chosen = wc_get_chosen_shipping_method_for_package($index, $package);
            foreach ($package['rates'] as $rate_id => $rate) {
                $has_rates = true;
                echo '<div class="choice"><label><input type="radio" name="shipping_method[' . esc_attr($index) . ']" value="' . esc_attr($rate_id) . '"' . checked($rate_id, $chosen, false) . ' required data-shipping-method>';
                echo '<span class="choice-body"><span class="choice-title">' . wp_kses_post(wc_cart_totals_shipping_method_label($rate)) . '</span></span></label></div>';
            }
        }
        if (!$has_rates) echo '<p class="choice-empty">برای این نشانی هنوز روش ارسالی تعریف نشده است.</p>';
        echo '</fieldset>';
    }
    echo '<dl class="checkout-totals">';
    echo '<div><dt>جمع کالاها</dt><dd>' . wp_kses_post(WC()->cart->get_cart_subtotal()) . '</dd></div>';
    foreach (WC()->cart->get_coupons() as $code => $coupon) {
        echo '<div><dt>تخفیف</dt><dd>−' . wp_kses_post(wc_price(WC()->cart->get_coupon_discount_amount($code, WC()->cart->display_cart_ex_tax))) . '</dd></div>';
    }
    if (WC()->cart->needs_shipping()) {
        echo '<div><dt>هزینهٔ ارسال</dt><dd>' . wp_kses_post(WC()->cart->get_cart_shipping_total()) . '</dd></div>';
    }
    foreach (WC()->cart->get_fees() as $fee) {
        echo '<div><dt>' . esc_html($fee->name) . '</dt><dd>' . wp_kses_post(wc_price($fee->total)) . '</dd></div>';
    }
    if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
        echo '<div><dt>مالیات</dt><dd>' . wp_kses_post(wc_price(WC()->cart->get_taxes_total())) . '</dd></div>';
    }
    echo '<div class="checkout-total"><dt>مبلغ قابل پرداخت</dt><dd>' . wp_kses_post(WC()->cart->get_total()) . '</dd></div>';
    echo '</dl>';
    return ob_get_clean();
}

// Recalculates delivery options when the customer picks a province or delivery method.
add_action('wc_ajax_takav_review', function () {
    if (!wp_verify_nonce(isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '', 'takav-review') || WC()->cart->is_empty()) {
        wp_send_json_error(null, 400);
    }
    $customer = WC()->customer;
    $country = 'IR';
    $state = isset($_POST['state']) ? wc_clean(wp_unslash($_POST['state'])) : '';
    $city = isset($_POST['city']) ? wc_clean(wp_unslash($_POST['city'])) : '';
    $postcode = isset($_POST['postcode']) ? preg_replace('/\D/', '', takav_en_digits(wc_clean(wp_unslash($_POST['postcode'])))) : '';
    $customer->set_props(array(
        'billing_country' => $country, 'billing_state' => $state, 'billing_city' => $city, 'billing_postcode' => $postcode,
        'shipping_country' => $country, 'shipping_state' => $state, 'shipping_city' => $city, 'shipping_postcode' => $postcode,
    ));
    $customer->set_calculated_shipping(true);
    $customer->save();
    if (isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
        $chosen = WC()->session->get('chosen_shipping_methods', array());
        foreach (wc_clean(wp_unslash($_POST['shipping_method'])) as $index => $method) {
            $chosen[absint($index)] = $method;
        }
        WC()->session->set('chosen_shipping_methods', $chosen);
    }
    wp_send_json_success(array('html' => takav_checkout_review()));
});

// ---------------------------------------------------------------------------
// Order received and tracking.
// ---------------------------------------------------------------------------

// The theme shows its own order summary on the tick page.
add_action('wp', function () {
    remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
});

/** Customer-facing status names, independent of the site language. */
function takav_status_label($order) {
    $labels = array(
        'pending' => 'در انتظار پرداخت',
        'on-hold' => 'در انتظار تأیید پرداخت',
        'processing' => 'در حال آماده‌سازی',
        'completed' => 'ارسال شد',
        'cancelled' => 'لغو شد',
        'refunded' => 'مبلغ برگشت داده شد',
        'failed' => 'پرداخت ناموفق',
    );
    $status = $order->get_status();
    return isset($labels[$status]) ? $labels[$status] : wc_get_order_status_name($status);
}

/** Steps shown on the tracking page: placed → confirmed and being prepared → sent. */
function takav_order_progress($order) {
    $status = $order->get_status();
    if (in_array($status, array('cancelled', 'refunded', 'failed'), true)) return -1;
    if ($status === 'completed') return 2;
    if ($status === 'processing') return 1;
    return 0;
}

function takav_find_order($number, $phone) {
    $number = preg_replace('/\D/', '', takav_en_digits($number));
    $phone = takav_normalize_phone($phone);
    if ($number === '' || $phone === '') return null;
    $order = wc_get_order(apply_filters('woocommerce_shortcode_order_tracking_order_id', $number));
    if (!$order || !is_a($order, 'WC_Order')) return null;
    return takav_normalize_phone($order->get_billing_phone()) === $phone ? $order : null;
}

/** Limits guesses on the tracking form: 15 lookups per 10 minutes per visitor address. */
function takav_track_allowed() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $key = 'takav_track_' . md5($ip);
    $count = (int) get_transient($key);
    if ($count >= 15) return false;
    set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);
    return true;
}

// ---------------------------------------------------------------------------
// Product page purchase box.
// ---------------------------------------------------------------------------

/** Options of one variation attribute as value => label, in the order set in WooCommerce. */
function takav_attribute_options($product, $attribute, $options) {
    $choices = array();
    if (taxonomy_exists($attribute)) {
        foreach (wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all')) as $term) {
            if (in_array($term->slug, $options, true)) $choices[$term->slug] = $term->name;
        }
    } else {
        foreach ($options as $option) $choices[$option] = $option;
    }
    return $choices;
}

function takav_purchase_form($id) {
    $product = takav_wc_product($id);
    echo '<div class="purchase-state">';
    if (!takav_can_buy($product)) {
        $sold_out = $product && $product->is_purchasable() && !$product->is_in_stock();
        echo '<button type="button" disabled>' . ($sold_out ? 'ناموجود' : 'به‌زودی') . '</button>';
        echo '<p>' . ($sold_out ? 'این محصول فعلاً موجود نیست.' : 'فروش این محصول به‌زودی شروع می‌شود.') . '</p></div>';
        return;
    }
    echo '<p class="product-price">' . wp_kses_post($product->get_price_html()) . '</p>';
    takav_notices();
    echo '<form class="add-form" method="post" action="' . esc_url(takav_product_url($id)) . '">';
    if ($product->is_type('variable')) {
        foreach ($product->get_variation_attributes() as $attribute => $options) {
            $name = 'attribute_' . sanitize_title($attribute);
            $picked = isset($_REQUEST[$name]) ? wc_clean(wp_unslash($_REQUEST[$name])) : $product->get_variation_default_attribute($attribute); // phpcs:ignore WordPress.Security.NonceVerification
            echo '<fieldset class="option-picker"><legend>' . esc_html(wc_attribute_label($attribute, $product)) . '</legend><div>';
            foreach (takav_attribute_options($product, $attribute, $options) as $value => $label) {
                echo '<label><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . checked($picked, $value, false) . ' required><span>' . esc_html($label) . '</span></label>';
            }
            echo '</div></fieldset>';
        }
    }
    echo '<input type="hidden" name="add-to-cart" value="' . esc_attr($product->get_id()) . '"><input type="hidden" name="quantity" value="1">';
    echo '<button type="submit">افزودن به سبد</button></form>';
    echo '<p>ثبت سفارش بدون نیاز به عضویت.</p></div>';
}
