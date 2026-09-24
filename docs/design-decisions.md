# Design decisions — 24 September 2026

- User scope: build the design first in the existing GitHub repository, on WordPress; backend later. No payment gateway or eNamad is currently available.
- User direction: **black with only a little orange**, like the collection. The initial ivory idea was superseded before implementation.
- The supplied garment photos define the art direction. The orange embroidery, drawstrings and curved piping recur in small accents, typography underlines and thin decorative curves. Do not substitute imagined products or colours.
- The user asked to wait for real photography. Earlier generated concepts were discarded and are not included in this repository or any deliverable.
- The chest close-up leads the page; a full garment inset provides context. Collection cards preserve the full garment framing. Detail galleries expose selected close-ups and original photos. Decorative hero cropping uses CSS only; original files are intact.
- One collection, two items. No artificial catalogue expansion, invented sizes, prices, discounts, review counts, deadlines, shipping promises or trust seals.
- Persian-first layout and logical CSS, Latin isolates, Persian interface digits. At the owner's request, Peyda Light/Regular lead the typography, with Medium headings; no heavy Persian weights. Files are locally hosted with no forced numeral substitutions. Vazirmatn is the public-repo fallback when owner-provided font files are absent. Promotional copy is proposed creative copy, not supplied brand history.
- A lightweight classic WordPress theme uses normal template hierarchy and enqueue hooks. No database migrations, custom routes, forced plugin overrides, page builders or checkout implementation.
- Product detail content remains in the document when JavaScript is off. Native dialog adds focus trapping and Escape dismissal; FAQ uses native details/summary.
- Local favourites are optional browser storage and clearly labelled as neither reservations nor orders. The interface reports storage failures accurately.
- Noindex is intentional until launch. Backend configuration, SEO launch setup and live-host performance verification remain a later phase.
