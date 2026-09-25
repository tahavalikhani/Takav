# Design decisions — 25 September 2026

The storefront follows the owner's requested clean product-first direction: two product cards, very little text, dedicated product URLs, and no FAQ or product pop-ups. Black dominates, with restrained orange from the owner's actual garments. Lighter Peyda weights are used locally; the public checkout falls back to Vazirmatn.

A separate `/collection-one/` presentation has moving garment photos, an animated title area and a scrolling collection strip. It uses the supplied real photos rather than generated 3D imagery, so garment details stay accurate. The homepage links to it with a small invitation.

The preview cart stores hoodie and pants quantities in the browser. It clearly says that checkout and payment are inactive. Sizes, prices, stock and shipping promises have not been invented. Presentation routes work with pretty or plain WordPress permalinks, without database changes.
