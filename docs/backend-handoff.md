# Backend: status

Ordering is live through WooCommerce (theme 0.3.0). What the theme does is described in the README under "Ordering".

## Done in the theme

- Guest checkout in three short steps plus a tick page with the tracking number; no accounts.
- Tracking page (`/track/`) by order number + mobile number.
- Products are WooCommerce products found by SKU (`takav-hoodie`, `takav-pants`); price, sizes and stock are edited in wp-admin.
- Persian digits in prices on the storefront only; Latin digits in wp-admin. Mobile numbers are stored as `09XXXXXXXXX`.

## Still the owner's decisions

1. Real prices in Toman, sizes, stock, shipping costs per province and return policy.
2. Payment: pay on delivery and/or card-to-card work with WooCommerce alone. For online payment, install ZarinPal's **official** plugin after merchant onboarding; keep credentials out of git and check the Toman/Rial unit. Test a real small payment including the **failed** and **cancelled** paths.
3. eNamad: reserve a footer slot once issued; the seal must be pasted verbatim in a PHP template (see the Persian web playbook, §5.4).
4. SMS notifications to customers (optional): an Iranian SMS plugin (e.g. Persian WooCommerce SMS with Kavenegar or MeliPayamak).
5. Size chart, fabric and care information, support channel.
