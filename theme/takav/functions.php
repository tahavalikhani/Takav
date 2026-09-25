<?php
/** Presentation only. No orders, accounts or payment integrations. */
defined('ABSPATH') || exit;

require_once get_template_directory() . '/inc/catalog.php';
require_once get_template_directory() . '/inc/routes.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
});

add_filter('language_attributes', function () {
    return 'lang="fa-IR" dir="rtl"';
});

function takav_asset($file) {
    return get_template_directory_uri() . '/assets/' . ltrim($file, '/');
}

function takav_has_peyda() {
    foreach (array('Light', 'Regular', 'Medium') as $weight) {
        if (!file_exists(get_template_directory() . '/assets/fonts/Peyda-' . $weight . '.ttf')) return false;
    }
    return true;
}

add_action('wp_enqueue_scripts', function () {
    $dir = get_template_directory();
    wp_enqueue_style('takav', takav_asset('css/storefront.css'), array(), filemtime($dir . '/assets/css/storefront.css'));
    if (takav_has_peyda()) {
        wp_enqueue_style('takav-peyda', takav_asset('css/peyda.css'), array('takav'), filemtime($dir . '/assets/css/peyda.css'));
    }
    wp_enqueue_script('takav', takav_asset('js/storefront.js'), array(), filemtime($dir . '/assets/js/storefront.js'), array('strategy' => 'defer', 'in_footer' => true));
    if (is_front_page()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('global-styles');
    }
});

add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
});

add_action('wp_head', function () {
    $font = takav_has_peyda() ? 'fonts/Peyda-Light.ttf' : 'fonts/Vazirmatn.woff2';
    $type = takav_has_peyda() ? 'font/ttf' : 'font/woff2';
    echo '<link rel="preload" href="' . esc_url(takav_asset($font)) . '" as="font" type="' . esc_attr($type) . '" crossorigin>' . "\n";
    echo '<meta name="theme-color" content="#101010">' . "\n";
});

add_filter('document_title_parts', function ($parts) {
    $view = get_query_var('takav_view', '');
    if ($view === 'collection-one') { $parts['title'] = 'کالکشن ۰۱ — تکاو'; return $parts; }
    if ($view === 'cart') { $parts['title'] = 'سبد نمایشی — تکاو'; return $parts; }
    $id = takav_current_product_id();
    if ($id) {
        $parts['title'] = takav_catalog()[$id]['name'] . ' — کالکشن اول';
    } elseif (is_front_page()) {
        $parts['title'] = 'تکاو — هودی و شلوار، کالکشن اول';
    }
    return $parts;
});

function takav_icon($name, $class = '') {
    $paths = array(
        'arrow' => '<path d="M19 5 5 19M5 5v14h14"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'menu' => '<path d="M4 8h16M4 16h16"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>',
    );
    if (!isset($paths[$name])) return;
    echo '<svg class="icon ' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>'; // Static allowlisted SVG.
}

function takav_product_image($product, $class = '', $eager = false) {
    $name = $product['image'];
    $srcset = takav_asset('images/' . $name . '-640.webp') . ' 640w, ' . takav_asset('images/' . $name . '-1280.webp') . ' 1280w';
    echo '<img class="' . esc_attr($class) . '" src="' . esc_url(takav_asset('images/' . $name . '-1280.webp')) . '" srcset="' . esc_attr($srcset) . '" sizes="(max-width: 700px) 92vw, 48vw" width="' . esc_attr($product['width']) . '" height="2560" alt="' . esc_attr($product['alt']) . '" loading="' . ($eager ? 'eager' : 'lazy') . '"' . ($eager ? ' fetchpriority="high"' : '') . ' decoding="async">';
}
