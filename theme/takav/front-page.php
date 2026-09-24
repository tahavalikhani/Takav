<?php
defined('ABSPATH') || exit;
get_header();
$products = takav_catalog();
?>
<main id="main">
    <section class="hero wrap" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="tiny-cross" aria-hidden="true">+</span> کالکشن اول <span class="eyebrow-rule"></span> <bdi>COLLECTION 01</bdi></p>
            <h1 id="hero-title">مشکی.<br>با امضای<br><span class="orange-word">نارنجی<span class="orange-period">.</span></span></h1>
            <p class="hero-description">یک هودی. یک شلوار. یک خط مشترک.<br>اولین کالکشن تکاو را از نزدیک ببین.</p>
            <a class="button button-primary" href="#collection">کالکشن رو ببین <?php takav_icon('arrow'); ?></a>
            <div class="hero-footnote"><span class="orange-line"></span><span>دو تکه، یک هویت</span><bdi>BLACK / ORANGE</bdi></div>
        </div>
        <div class="hero-art">
            <span class="art-coordinate" dir="ltr">TKV—001 / FIRST COLLECTION</span>
            <div class="hero-frame">
                <img class="hero-photo" src="<?php echo esc_url(takav_asset('images/takav-embroidery-1280.webp')); ?>" srcset="<?php echo esc_url(takav_asset('images/takav-embroidery-640.webp')); ?> 640w, <?php echo esc_url(takav_asset('images/takav-embroidery-1280.webp')); ?> 1280w" sizes="(max-width: 600px) 80vw, 48vw" width="2560" height="1928" alt="نمای نزدیک گلدوزی نارنجی TAKAV و بندهای کلاه روی هودی مشکی" fetchpriority="high" decoding="async">
                <span class="photo-label"><span class="status-dot"></span> عکس کالکشن تکاو</span>
                <span class="frame-corner corner-one" aria-hidden="true"></span><span class="frame-corner corner-two" aria-hidden="true"></span>
            </div>
            <a href="#detail-hoodie" data-open-product="hoodie" class="hero-inset" aria-label="دیدن نمای کامل هودی تکاو">
                <?php takav_product_image($products['hoodie'], '', false); ?>
                <span><bdi>THE FULL PICTURE</bdi><?php takav_icon('arrow'); ?></span>
            </a>
            <div class="art-bottom"><span>خطی که ادامه دارد.</span><bdi>01 / 02</bdi></div>
            <svg class="hero-piping" viewBox="0 0 500 650" fill="none" aria-hidden="true"><path d="M490 1C210 50 480 240 195 400S50 555 1 649" stroke="currentColor" stroke-width="1"/></svg>
        </div>
    </section>

    <div class="collection-band" aria-hidden="true"><div class="wrap"><bdi>TAKAV</bdi><span class="band-cross">✳</span><span>از جزئیات، متفاوت.</span><span class="band-cross">✳</span><bdi>BLACK WITH A TRACE OF ORANGE</bdi></div></div>

    <section class="collection-section wrap" id="collection" aria-labelledby="collection-title">
        <div class="section-heading"><div><p class="eyebrow"><span class="tiny-cross" aria-hidden="true">+</span> اولین فصل تکاو</p><h2 id="collection-title">دو تکه. <span class="muted">یک امضا.</span></h2></div><span class="section-index" dir="ltr">[ 01 — COLLECTION ]</span></div>
        <div class="collection-toolbar"><div class="filters" role="group" aria-label="فیلتر کالکشن" hidden><button type="button" class="filter is-active" data-filter="all" aria-pressed="true">همه <span>۰۲</span></button><button type="button" class="filter" data-filter="hoodie" aria-pressed="false">هودی</button><button type="button" class="filter" data-filter="pants" aria-pressed="false">شلوار</button></div><p class="availability"><span class="status-dot"></span> به‌زودی برای خرید</p></div>
        <p id="filter-status" class="sr-only" aria-live="polite"></p>
        <div class="product-grid">
            <?php foreach ($products as $id => $product) : ?>
            <article class="product-card" data-product="<?php echo esc_attr($id); ?>">
                <a class="product-photo-link" href="#detail-<?php echo esc_attr($id); ?>" data-open-product="<?php echo esc_attr($id); ?>" aria-label="دیدن جزئیات <?php echo esc_attr($product['name']); ?>">
                    <div class="product-image-wrap"><?php takav_product_image($product); ?><span class="product-number"><?php echo esc_html($product['number']); ?> / <bdi>TAKAV</bdi></span><span class="product-image-arrow"><?php takav_icon('arrow'); ?></span></div>
                </a>
                <div class="product-info"><div><p class="product-kicker"><bdi><?php echo esc_html($product['english']); ?></bdi></p><h3><a href="#detail-<?php echo esc_attr($id); ?>" data-open-product="<?php echo esc_attr($id); ?>"><?php echo esc_html($product['name']); ?></a></h3><p class="color-line"><span class="color-swatch" aria-hidden="true"></span> مشکی / نارنجی</p></div><a class="text-link" href="#detail-<?php echo esc_attr($id); ?>" data-open-product="<?php echo esc_attr($id); ?>">جزئیات <?php takav_icon('plus'); ?></a></div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="set-note"><p><span class="tiny-cross" aria-hidden="true">+</span> کنار هم، کامل‌تر.</p><span>هودی و شلوار از یک کالکشن؛ با همان امضای نارنجی.</span><a class="text-link" href="#signature">داستان جزئیات <?php takav_icon('arrow'); ?></a></div>
    </section>

    <section class="signature-section" id="signature" aria-labelledby="signature-title"><div class="signature-inner wrap">
        <div class="signature-copy"><p class="eyebrow"><span class="tiny-cross" aria-hidden="true">+</span> امضای تکاو</p><h2 id="signature-title">تفاوت،<br>توی جزئیاته<span class="orange-period">.</span></h2><p>یک خط نارنجی روی مشکی.<br>از آستین هودی تا پایین شلوار، همین جزئیات دو تکه را به هم وصل می‌کند.</p><div class="signature-facts"><span><bdi>01</bdi> زمینهٔ مشکی</span><span><bdi>02</bdi> خط نارنجی</span><span><bdi>03</bdi> امضای تکاو</span></div></div>
        <div class="signature-visual"><div class="signature-statement" dir="ltr">ALL BLACK.<br>A LITTLE<br><span>ORANGE.</span></div><svg viewBox="0 0 560 430" fill="none" aria-hidden="true"><path d="M575 3C155-40 630 190 290 232S70 290-20 420" stroke="currentColor" stroke-width="2"/></svg><p>گاهی یک خط، تمام تفاوت است.</p><span class="signature-index" dir="ltr">THE TAKAV SIGNATURE ↗</span></div>
    </div></section>

    <section class="questions-section wrap" id="questions" aria-labelledby="questions-title"><div class="questions-heading"><p class="eyebrow"><span class="tiny-cross" aria-hidden="true">+</span> قبل از انتخاب</p><h2 id="questions-title">خوب است بدانی.</h2><p>اطلاعاتی که برای انتخاب این کالکشن نیاز داری.</p><span class="section-index" dir="ltr">[ 02 — GOOD TO KNOW ]</span></div><div class="faq-list">
        <details><summary>چطور می‌توانم این کالکشن را بخرم؟<span class="faq-symbol" aria-hidden="true">+</span></summary><p>کالکشن فعلاً برای معرفی نمایش داده می‌شود. قیمت و امکان خرید آنلاین به‌زودی اضافه می‌شود؛ در حال حاضر سفارشی ثبت نمی‌شود.</p></details>
        <details><summary>سایزها و اندازه‌ها را کجا ببینم؟<span class="faq-symbol" aria-hidden="true">+</span></summary><p>جدول اندازه‌های دقیق هودی و شلوار پیش از شروع فروش در همین صفحه قرار می‌گیرد.</p></details>
        <details><summary>هودی و شلوار جدا هم عرضه می‌شوند؟<span class="faq-symbol" aria-hidden="true">+</span></summary><p>جزئیات عرضهٔ کالکشن، همراه با قیمت‌ها، پیش از شروع فروش اعلام می‌شود.</p></details>
    </div></section>

    <section class="closing-section wrap"><div><p class="eyebrow">اولین کالکشن. اولین امضا.</p><h2>خط خودت رو داشته باش.</h2></div><a class="circle-link" href="#collection" aria-label="بازگشت به کالکشن"><?php takav_icon('arrow'); ?></a></section>

    <section class="product-details wrap" aria-label="جزئیات کالکشن">
    <?php foreach ($products as $id => $product) : ?>
        <article class="product-detail" id="detail-<?php echo esc_attr($id); ?>" data-detail="<?php echo esc_attr($id); ?>" aria-labelledby="title-<?php echo esc_attr($id); ?>">
            <button type="button" class="icon-button detail-close" hidden aria-label="بستن جزئیات محصول"><?php takav_icon('close'); ?></button>
            <div class="detail-gallery"><div class="detail-photo"><?php takav_product_image($product); ?><a class="original-photo-link" href="<?php echo esc_url(takav_asset('images/' . $product['image'] . '.jpg')); ?>" target="_blank" rel="noopener">دیدن عکس اصلی <span aria-hidden="true">↗</span></a></div><div class="gallery-thumbnails" aria-label="عکس‌های <?php echo esc_attr($product['name']); ?>"><?php foreach ($product['gallery'] as $image => $label) : ?><a href="<?php echo esc_url(takav_asset('images/' . $image . '.jpg')); ?>" target="_blank" rel="noopener" data-gallery-image="<?php echo esc_url(takav_asset('images/' . $image . '-1280.webp')); ?>" data-gallery-alt="<?php echo esc_attr($label); ?>" <?php if ($image === $product['image']) echo 'aria-current="true"'; ?>><img src="<?php echo esc_url(takav_asset('images/' . $image . '-640.webp')); ?>" alt="<?php echo esc_attr($label); ?>" width="72" height="72" loading="lazy"></a><?php endforeach; ?></div><p class="gallery-caption" aria-live="polite">نمای کامل محصول</p></div>
            <div class="detail-copy"><p class="eyebrow"><bdi>TAKAV / COLLECTION 01</bdi></p><h2 id="title-<?php echo esc_attr($id); ?>"><?php echo esc_html($product['name']); ?></h2><p class="color-line"><span class="color-swatch" aria-hidden="true"></span> مشکی / نارنجی</p><p class="detail-description"><?php echo esc_html($product['description']); ?></p><ul class="feature-list"><?php foreach ($product['features'] as $feature) : ?><li><?php echo esc_html($feature); ?></li><?php endforeach; ?></ul><div class="coming-soon"><span class="status-dot"></span><div><strong>به‌زودی برای خرید</strong><p>قیمت و راهنمای سایز پیش از شروع فروش اعلام می‌شود.</p></div></div><button type="button" class="button save-button" data-save="<?php echo esc_attr($id); ?>" hidden aria-pressed="false" data-label-default="ذخیره برای بعد" data-label-saved="در علاقه‌مندی‌ها ذخیره شد"><?php takav_icon('heart'); ?><span>ذخیره برای بعد</span></button><p class="save-note" hidden>فقط در همین مرورگر ذخیره می‌شود؛ به معنی رزرو یا ثبت سفارش نیست.</p><a class="detail-back text-link" href="#collection">بازگشت به کالکشن <?php takav_icon('arrow'); ?></a></div>
        </article>
    <?php endforeach; ?>
    </section>
    <dialog class="product-dialog" aria-label="جزئیات محصول"></dialog>
    <div class="toast" role="status" hidden></div>
</main>
<?php get_footer(); ?>
