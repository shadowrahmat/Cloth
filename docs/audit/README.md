# Code Review ও Security Audit

Audit-এর তারিখ: ২০২৬-১১-২৮। যা দেখা হয়েছে: routes, auth, cart/checkout, admin controller, upload, deploy script আর frontend-এর storage।

**যা run করা হয়নি:** `php artisan test` আর Pint (`vendor/` install করা ছিল না), `composer audit`। `npm audit --omit=dev` run হয়েছে, 0 vulnerability পাওয়া গেছে। `.env` পড়া হয়নি।

## কীভাবে ব্যবহার করবে

একবারে একটা finding implement করবে। প্রতিটার details নিচের link-করা ফাইলে আছে। কাজ শেষ হলে এখানে tick দিয়ে commit hash লিখে রাখবে।

Suggested order: আগে H1, H5, M1 (ছোট change, বড় impact), তারপর H2 + H3, তারপর M7 + M2, তারপর H4, শেষে H6।

## Checklist

### High — [high.md](high.md)
- [x] **H1** Checkout-এ cart-এর price stale থাকে (fixed)
- [x] **H2** Guest session ID predictable, cart/wishlist merge hijack করা যায় (fixed, commit করা বাকি)
- [ ] **H3** Sanctum token কখনো expire বা revoke হয় না
- [x] **H4** RBAC permission কোথাও enforce হয় না (fixed, commit করা বাকি)
- [ ] **H5** Checkout, track-order, coupon, contact, cart-এ rate limit নেই
- [ ] **H6** Steadfast courier integration আসলে fake

### Medium — [medium.md](medium.md)
- [ ] **M1** Exception message client-এ leak হয়
- [ ] **M2** Coupon usage-এ race condition, per-user limit নেই
- [ ] **M3** ResourceController-এ `sort_by`, `per_page`, LIKE wildcard validate হয় না
- [ ] **M4** Password policy দুর্বল, login throttle শুধু IP-based
- [ ] **M5** Cart-এ unpublished product আর যেকোনো quantity add করা যায়
- [ ] **M6** Variant stock দুইবার decrement হতে পারে
- [ ] **M7** Deploy-এ HTTPS, HSTS, CSP, secure cookie নেই
- [ ] **M8** Image optimize-এ width-এর limit নেই, file পড়ে থাকে

### Low / code quality — [low.md](low.md)
- [ ] **L1** FormRequest, Policy, API Resource নেই
- [ ] **L2** `categories`-এর জন্য ৫টা route closure (`route:cache` আটকায়)
- [ ] **L3** ActivityLog-এর boilerplate ১৫+ জায়গায় copy-paste
- [ ] **L4** CheckoutService-এ USD / `US` hardcoded, client-এর `payment_id` নেওয়া হয়
- [ ] **L5** Order number-এ মাত্র ৫টা random character
- [ ] **L6** Register-এ জানা যায় কোন email আগে থেকে আছে
- [ ] **L7** Contact `message`-এর max length নেই
- [ ] **L8** Review-এর `author_email` verified না
- [ ] **L9** `SESSION_ENCRYPT` default false
- [ ] **L10** H1, H2, H5, M2, M6-এর test নেই

## যা আগে থেকেই ভালো আছে

- `EnsureAdmin` customer token দিয়ে admin API ব্যবহার আটকায়; `admin:full` users, roles, SMTP-কে protect করে।
- Upload hardening: SVG/HTML/PHP block, `mimes` + `extensions` দুইটা check, random file name, size limit।
- Email template-এ `e()` দিয়ে escape করা; SMTP password `Crypt` দিয়ে encrypted আর API-তে কখনো ফেরত যায় না।
- Review শুরুতে unapproved থাকে; newsletter-এ honeypot আছে; checkout-এ শুধু `cod`, আর payment `pending` দিয়ে শুরু হয়।
- Stock `lockForUpdate` আর transaction দিয়ে handle করা।
- Deploy: random DB password, creds file `chmod 600`, `ufw`, dotfile deny, direct `.php` access 404, `APP_DEBUG=false`।
- Frontend-এ কোথাও `v-html` / `innerHTML` নেই।
