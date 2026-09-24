<?php defined('ABSPATH') || exit; ?>
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
<div class="announcement"><span class="status-dot" aria-hidden="true"></span> اولین کالکشن تکاو <span class="announcement-separator">/</span> شروع یک خط تازه</div>
<header class="site-header">
    <div class="header-inner wrap">
        <a class="wordmark" href="<?php echo esc_url(home_url('/')); ?>" aria-label="تکاو، صفحه اصلی"><bdi>TAKAV<span class="brand-dot" aria-hidden="true">•</span></bdi></a>
        <nav class="main-nav" aria-label="منوی اصلی" id="main-nav">
            <a href="<?php echo esc_url(home_url('/#collection')); ?>">کالکشن اول</a>
            <a href="<?php echo esc_url(home_url('/#signature')); ?>">امضای تکاو</a>
            <a href="<?php echo esc_url(home_url('/#questions')); ?>">راهنمای خرید</a>
        </nav>
        <a class="header-cta" href="<?php echo esc_url(home_url('/#collection')); ?>">کشف کالکشن <?php takav_icon('arrow'); ?></a>
        <button class="icon-button menu-toggle" hidden aria-controls="main-nav" aria-expanded="false" aria-label="باز کردن منو"><?php takav_icon('menu'); ?></button>
    </div>
</header>
