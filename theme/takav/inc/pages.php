<?php
/**
 * Store pages the payment gateway and eNamad look for: about, contact and terms.
 * Contact details come from wp-admin → «سفارشات» → «تنظیمات فروشگاه»; nothing is invented here.
 * A real WordPress page with the same slug (about, contact, terms) replaces the theme's version.
 */
defined('ABSPATH') || exit;

function takav_info_pages() {
    return array('about' => 'درباره ما', 'contact' => 'تماس با ما', 'terms' => 'قوانین و مقررات');
}

/** Owner-entered contact details; empty values are simply not shown. */
function takav_contact() {
    $contact = array();
    foreach (array('phone', 'email', 'address', 'postcode', 'hours', 'instagram') as $key) {
        $contact[$key] = trim((string) get_option('takav_contact_' . $key, ''));
    }
    $contact['telegram'] = takav_telegram_url();
    return $contact;
}

/** URL of an info page: the owner's own WordPress page if one exists, otherwise the theme's. */
function takav_info_url($slug) {
    $page = get_page_by_path($slug);
    if ($page && $page->post_status === 'publish') return get_permalink($page);
    return takav_view_url($slug);
}

function takav_info_page_exists($slug) {
    $page = get_page_by_path($slug);
    return $page && $page->post_status === 'publish';
}

/**
 * eNamad seal, exactly as issued. eNamad treats any change to this markup as tampering, so it lives
 * here as a nowdoc and is printed raw: no escaping, no rel, no lazy-loading, never moved into editor content.
 * If a cache plugin lazy-loads images, exclude trustseal.enamad.ir in its settings.
 */
function takav_enamad_seal() {
    echo <<<'ENAMAD'
<a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=7907429&Code=wI8JHYPsuvlfhEP4Ns7dNggrp8bMYQIZ'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=7907429&Code=wI8JHYPsuvlfhEP4Ns7dNggrp8bMYQIZ' alt='' style='cursor:pointer' code='wI8JHYPsuvlfhEP4Ns7dNggrp8bMYQIZ'></a>
ENAMAD;
}
