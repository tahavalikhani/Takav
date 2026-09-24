<?php
defined('ABSPATH') || exit;

/** Only facts visible in photographs supplied by the owner. */
function takav_catalog() {
    return array(
        'hoodie' => array(
            'name' => 'هودی تکاو',
            'english' => 'THE HOODIE',
            'number' => '۰۱',
            'image' => 'takav-hoodie',
            'width' => 2035,
            'alt' => 'عکس واقعی هودی مشکی تکاو با لوگو و بند نارنجی و خطوط منحنی روی آستین‌ها',
            'description' => 'مشکی، با جزئیاتی که دیده می‌شوند. لوگوی نارنجی تکاو، بندهای هم‌رنگ و خطی که روی آستین ادامه پیدا می‌کند.',
            'features' => array('گلدوزی نارنجی روی سینه و کلاه', 'بند کلاه نارنجی با سر فلزی', 'خط منحنی روی آستین‌ها', 'جیب یک‌تکه در جلو'),
            'gallery' => array('takav-hoodie' => 'نمای کامل هودی', 'takav-embroidery' => 'گلدوزی سینه و بند کلاه', 'takav-hood-detail' => 'نشان گلدوزی روی کلاه'),
        ),
        'pants' => array(
            'name' => 'شلوار تکاو',
            'english' => 'THE PANTS',
            'number' => '۰۲',
            'image' => 'takav-pants',
            'width' => 2097,
            'alt' => 'عکس واقعی شلوار مشکی تکاو با لوگوی نارنجی، خطوط منحنی پایین پاچه و بند تنظیم',
            'description' => 'ادامهٔ همان خط. شلوار مشکی با لوگوی نارنجی و خطوط منحنی در پایین پاچه؛ کنار هودی، یک ترکیب کامل.',
            'features' => array('گلدوزی نارنجی تکاو', 'خط منحنی روی پاچه‌ها', 'کمر کشی و جیب کناری زیپ‌دار', 'بند تنظیم پایین پاچه'),
            'gallery' => array('takav-pants' => 'نمای کامل شلوار', 'takav-pocket-detail' => 'جیب پشت و نشان گلدوزی', 'takav-waist-detail' => 'کمر و جیب زیپ‌دار', 'takav-hem-detail' => 'خط نارنجی و بند تنظیم پاچه'),
        ),
    );
}
