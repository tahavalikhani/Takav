<?php defined('ABSPATH') || exit; $current_product = takav_current_product_id(); ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">رفتن به محتوای اصلی</a>
<header class="site-header">
    <div class="header-inner wrap">
        <nav class="main-nav" aria-label="منوی اصلی">
            <a href="<?php echo esc_url(home_url('/')); ?>" <?php if (takav_is_home_view() && !$current_product) echo 'aria-current="page"'; ?>>همه</a>
            <?php foreach (array('hoodie' => 'هودی', 'pants' => 'شلوار') as $nav_product_id => $label) : ?>
            <a href="<?php echo esc_url(takav_product_url($nav_product_id)); ?>" <?php if ($current_product === $nav_product_id) echo 'aria-current="page"'; ?>><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </nav>
        <a class="wordmark" href="<?php echo esc_url(home_url('/')); ?>" aria-label="تکاو، صفحه اصلی"><bdi>TAKAV<span aria-hidden="true">.</span></bdi></a>
        <a class="header-cart" href="<?php echo esc_url(takav_view_url('cart')); ?>">سبد <span data-cart-count>۰</span></a>
    </div>
</header>
