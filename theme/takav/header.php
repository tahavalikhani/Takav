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
        <?php // Phones only: a menu button that opens every link. <details> keeps it working without JavaScript. ?>
        <details class="mobile-menu">
            <summary aria-label="منو"><span class="burger" aria-hidden="true"><span></span><span></span><span></span></span></summary>
            <nav class="mobile-menu-panel" aria-label="منوی موبایل">
                <a href="<?php echo esc_url(home_url('/')); ?>" <?php if (takav_is_home_view() && !$current_product) echo 'aria-current="page"'; ?>>همه</a>
                <?php foreach (array('hoodie' => 'هودی', 'pants' => 'شلوار') as $nav_product_id => $label) : ?>
                <a href="<?php echo esc_url(takav_product_url($nav_product_id)); ?>" <?php if ($current_product === $nav_product_id) echo 'aria-current="page"'; ?>><?php echo esc_html($label); ?></a>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(takav_view_url('collection-one')); ?>">کالکشن ۰۱</a>
                <a href="<?php echo esc_url(takav_cart_url()); ?>">سبد خرید</a>
                <a href="<?php echo esc_url(takav_view_url('track')); ?>">پیگیری سفارش</a>
                <span class="mobile-menu-small">
                    <a href="<?php echo esc_url(takav_info_url('about')); ?>">درباره ما</a>
                    <a href="<?php echo esc_url(takav_info_url('contact')); ?>">تماس با ما</a>
                    <a href="<?php echo esc_url(takav_info_url('terms')); ?>">قوانین و مقررات</a>
                    <?php $menu_contact = takav_contact(); if ($menu_contact['instagram']) : ?><a href="<?php echo esc_url($menu_contact['instagram']); ?>" target="_blank" rel="noopener">اینستاگرام</a><?php endif; ?>
                </span>
            </nav>
        </details>
        <nav class="main-nav" aria-label="منوی اصلی">
            <a href="<?php echo esc_url(home_url('/')); ?>" <?php if (takav_is_home_view() && !$current_product) echo 'aria-current="page"'; ?>>همه</a>
            <?php foreach (array('hoodie' => 'هودی', 'pants' => 'شلوار') as $nav_product_id => $label) : ?>
            <a href="<?php echo esc_url(takav_product_url($nav_product_id)); ?>" <?php if ($current_product === $nav_product_id) echo 'aria-current="page"'; ?>><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </nav>
        <a class="wordmark" href="<?php echo esc_url(home_url('/')); ?>" aria-label="تکاو، صفحه اصلی"><bdi>TAKAV<span aria-hidden="true">.</span></bdi></a>
        <a class="header-cart" href="<?php echo esc_url(takav_cart_url()); ?>">سبد <span data-cart-count><?php echo esc_html(takav_fa_digits(takav_cart_count())); ?></span></a>
    </div>
</header>
