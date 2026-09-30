<?php
/** About, contact and terms. Facts only: the collection, pre-order, Tipax, ZarinPal, tracking, and the owner's contact details. */
defined('ABSPATH') || exit;
$view = get_query_var('takav_view', '');
$pages = takav_info_pages();
if (!isset($pages[$view])) { get_template_part('404'); return; }
$contact = takav_contact();
$days = takav_fa_digits(takav_preorder_days());
$has_contact = $contact['phone'] || $contact['email'] || $contact['address'];
get_header(); ?>
<main id="main" class="info-page wrap">
    <div class="cart-heading"><p><bdi>TAKAV / <?php echo esc_html(strtoupper($view)); ?></bdi></p><h1><?php echo esc_html($pages[$view]); ?></h1></div>
    <div class="info-body">
    <?php if ($view === 'about') : ?>
        <p>تکاو یک برند پوشاک است. کالکشن اول تکاو یک هودی و یک شلوار است: مشکی، با جزئیات نارنجی؛ گلدوزی لوگو، بند نارنجی و خطی که روی آستین و پاچه ادامه پیدا می‌کند.</p>
        <p>محصولات تکاو پیش‌فروش می‌شوند. سفارشت را ثبت و آنلاین پرداخت می‌کنی؛ سفارش حدود <?php echo esc_html($days); ?> روز بعد آماده می‌شود و با تیپاکس برایت ارسال می‌شود. مراحل آماده‌سازی را در تلگرام اطلاع می‌دهیم.</p>
        <p>برای خرید به عضویت نیاز نیست. بعد از پرداخت یک کد پیگیری می‌گیری و با آن و شماره موبایلت می‌توانی وضعیت سفارش را ببینی.</p>
        <p><a href="<?php echo esc_url(home_url('/')); ?>">دیدن کالکشن</a> · <a href="<?php echo esc_url(takav_info_url('contact')); ?>">تماس با ما</a></p>

    <?php elseif ($view === 'contact') : ?>
        <?php if ($has_contact || $contact['instagram'] || $contact['telegram']) : ?>
        <dl class="contact-list">
            <?php if ($contact['phone']) : ?><div><dt>تلفن</dt><dd><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', takav_en_digits($contact['phone']))); ?>"><bdi><?php echo esc_html($contact['phone']); ?></bdi></a></dd></div><?php endif; ?>
            <?php if ($contact['email']) : ?><div><dt>ایمیل</dt><dd><a href="mailto:<?php echo esc_attr($contact['email']); ?>"><bdi><?php echo esc_html($contact['email']); ?></bdi></a></dd></div><?php endif; ?>
            <?php if ($contact['address']) : ?><div><dt>نشانی</dt><dd><?php echo esc_html($contact['address']); ?><?php if ($contact['postcode']) echo '<br>کد پستی: <bdi>' . esc_html($contact['postcode']) . '</bdi>'; ?></dd></div><?php endif; ?>
            <?php if ($contact['hours']) : ?><div><dt>ساعت پاسخ‌گویی</dt><dd><?php echo esc_html($contact['hours']); ?></dd></div><?php endif; ?>
            <?php if ($contact['instagram']) : ?><div><dt>اینستاگرام</dt><dd><a href="<?php echo esc_url($contact['instagram']); ?>" target="_blank" rel="noopener"><bdi><?php echo esc_html(preg_replace('#^https?://(www\.)?#', '', untrailingslashit($contact['instagram']))); ?></bdi></a></dd></div><?php endif; ?>
            <?php if ($contact['telegram']) : ?><div><dt>تلگرام</dt><dd><a href="<?php echo esc_url($contact['telegram']); ?>" target="_blank" rel="noopener"><bdi><?php echo esc_html(preg_replace('#^https?://#', '', untrailingslashit($contact['telegram']))); ?></bdi></a></dd></div><?php endif; ?>
        </dl>
        <?php else : ?>
        <p>اطلاعات تماس به‌زودی اینجا قرار می‌گیرد.</p>
        <?php endif; ?>
        <p>برای پیگیری سفارش، کد پیگیری و شماره موبایلت را در <a href="<?php echo esc_url(takav_view_url('track')); ?>">صفحهٔ پیگیری سفارش</a> وارد کن.</p>

    <?php else : ?>
        <h2>۱. ثبت سفارش</h2>
        <p>برای خرید از تکاو به عضویت نیاز نیست. هنگام ثبت سفارش نام، شماره موبایل و نشانی را وارد می‌کنی؛ درستی این اطلاعات با توست و سفارش به همان نشانی ارسال می‌شود. با ثبت سفارش، این قوانین را می‌پذیری.</p>
        <h2>۲. قیمت و پرداخت</h2>
        <p>قیمت‌ها به تومان است. پرداخت آنلاین و از طریق درگاه امن زرین‌پال با همهٔ کارت‌های عضو شتاب انجام می‌شود. سفارش بعد از پرداخت موفق ثبت نهایی می‌شود. اگر پرداخت ناموفق باشد و مبلغی از حسابت کم شده باشد، طبق مقررات بانکی معمولاً تا ۷۲ ساعت به حسابت برمی‌گردد.</p>
        <h2>۳. پیش‌فروش و زمان آماده‌سازی</h2>
        <p>محصولات تکاو پیش‌فروش می‌شوند. سفارش حدود <?php echo esc_html($days); ?> روز بعد از پرداخت آماده می‌شود. مراحل آماده‌سازی در کانال تلگرام تکاو اطلاع‌رسانی می‌شود و لینک آن بعد از ثبت سفارش به تو داده می‌شود.</p>
        <h2>۴. ارسال</h2>
        <p>سفارش‌ها با تیپاکس به سراسر ایران ارسال می‌شوند. هزینهٔ ارسال پس‌کرایه است، یعنی هنگام تحویل مستقیماً به تیپاکس پرداخت می‌شود و در مبلغ سفارش حساب نمی‌شود.</p>
        <h2>۵. پیگیری سفارش</h2>
        <p>بعد از پرداخت یک کد پیگیری می‌گیری. با این کد و شماره موبایلی که هنگام خرید وارد کردی، وضعیت سفارش را در <a href="<?php echo esc_url(takav_view_url('track')); ?>">صفحهٔ پیگیری سفارش</a> می‌بینی.</p>
        <h2>۶. بازگشت کالا</h2>
        <p>طبق قانون تجارت الکترونیکی، تا ۷ روز کاری پس از تحویل می‌توانی از خرید انصراف دهی؛ کالا باید سالم، استفاده‌نشده و با بسته‌بندی اولیه برگردانده شود. برای بازگشت کالا ابتدا از راه‌های <a href="<?php echo esc_url(takav_info_url('contact')); ?>">تماس با ما</a> هماهنگ کن. هزینهٔ ارسال برگشت با خریدار است.</p>
        <h2>۷. حریم خصوصی</h2>
        <p>نام، شماره موبایل، ایمیل و نشانی تو فقط برای ثبت، ارسال و پیگیری سفارش استفاده می‌شود و فقط در همین حد در اختیار تیپاکس (برای ارسال) و زرین‌پال (برای پرداخت) قرار می‌گیرد. اطلاعات کارت بانکی در درگاه بانک وارد می‌شود و به تکاو نمی‌رسد. اطلاعاتت را به هیچ شخص یا شرکت دیگری نمی‌دهیم.</p>
        <h2>۸. ارتباط با ما</h2>
        <p>برای هر سؤال یا مشکلی از راه‌های <a href="<?php echo esc_url(takav_info_url('contact')); ?>">تماس با ما</a> در ارتباط باش.</p>
    <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
