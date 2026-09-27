<?php
/** Presentation routes; both pretty and plain WordPress permalinks work. */
defined('ABSPATH') || exit;

add_action('init', function () {
    add_rewrite_rule('^collection/([^/]+)/?$', 'index.php?takav_product=$matches[1]', 'top');
});
add_filter('query_vars', function ($vars) { $vars[] = 'takav_product'; return $vars; });
add_filter('query_vars', function ($vars) { $vars[] = 'takav_view'; return $vars; });

// Also handles a request before the host has flushed its rewrite rules.
add_action('parse_request', function ($request) {
    $path = trim($request->request, '/');
    if ($path === 'collection-one' || $path === 'track') {
        $request->query_vars = array('takav_view' => $path);
        return;
    }
    // /cart/ and /checkout/ open WooCommerce's own pages, whatever their slug; without WooCommerce, the theme's cart.
    if ($path === 'cart' || $path === 'checkout') {
        $page = function_exists('wc_get_page_id') ? wc_get_page_id($path) : 0;
        if ($page > 0 && get_post_status($page) === 'publish') {
            $request->query_vars = array('page_id' => $page);
        } elseif ($path === 'cart') {
            $request->query_vars = array('takav_view' => 'cart');
        }
        return;
    }
    if (preg_match('#^collection/([^/]+)/?$#', $request->request, $matches)) {
        $request->query_vars = array('takav_product' => $matches[1]);
    }
});

function takav_views() {
    return array('collection-one', 'cart', 'track');
}

function takav_view_url($view) {
    if (!in_array($view, takav_views(), true)) return home_url('/');
    return get_option('permalink_structure') ? home_url('/' . $view . '/') : add_query_arg('takav_view', $view, home_url('/'));
}

// The collection home also covers a root post list, e.g. "static page" chosen with no homepage set.
function takav_is_home_view() {
    return is_front_page() || (is_home() && !get_queried_object_id());
}

function takav_current_product_id() {
    $value = get_query_var('takav_product', '');
    return is_string($value) && isset(takav_catalog()[$value]) ? $value : '';
}

function takav_product_url($id) {
    if (!isset(takav_catalog()[$id])) return home_url('/');
    if (get_option('permalink_structure')) return home_url('/collection/' . $id . '/');
    return add_query_arg('takav_product', $id, home_url('/'));
}

add_action('template_redirect', function () {
    $view = get_query_var('takav_view', '');
    if ($view !== '') {
        global $wp_query;
        if (!in_array($view, takav_views(), true)) {
            $wp_query->set_404(); status_header(404); return;
        }
        $wp_query->is_404 = false; $wp_query->is_home = false; $wp_query->is_page = false;
        status_header(200); return;
    }
    $requested = get_query_var('takav_product', '');
    if ($requested === '') return;
    global $wp_query;
    if (!takav_current_product_id()) {
        $wp_query->set_404();
        status_header(404);
        return;
    }
    $wp_query->is_404 = false;
    $wp_query->is_home = false;
    $wp_query->is_page = false;
    status_header(200);
}, 0);

add_filter('template_include', function ($template) {
    $view = get_query_var('takav_view', '');
    if (in_array($view, takav_views(), true)) return get_template_directory() . '/page-' . $view . '.php';
    if ($view !== '') return get_template_directory() . '/404.php';
    if (takav_current_product_id()) return get_template_directory() . '/single-takav.php';
    if (get_query_var('takav_product', '') !== '') return get_template_directory() . '/404.php';
    if (takav_is_home_view()) return get_template_directory() . '/front-page.php';
    return $template;
});

add_filter('redirect_canonical', function ($redirect) {
    return get_query_var('takav_product', '') !== '' || get_query_var('takav_view', '') !== '' ? false : $redirect;
});
