# High Severity

## H1 — Checkout-এ cart-এর price stale থাকে
> **Status: fixed।** `CartService::currentPrice()` এক জায়গায় দামের নিয়ম রাখে; cart দেখানো আর checkout দুটোই এখনকার দাম ধরে। Test: `tests/Feature/CartPriceTest.php`।
- **কোথায়:** `app/Services/CartService.php`-এর `addItem()` item add করার সময় `price` save করে রাখে; `app/Services/CheckoutService.php` subtotal আর order item-এ সেই `$item->price`-ই ব্যবহার করে।
- **সমস্যা:** Item add করার মুহূর্তের price আটকে থাকে। Sale শেষ হয়ে গেলে বা price বাড়লেও customer পুরোনো price-এই order করতে পারে।
- **Fix:** Checkout-এর সময় product/variant থেকে unit price আবার calculate করো (`addItem`-এর logic-টাই) আর সেটা ব্যবহার করো। চাইলে cart পড়ার সময়ও item-এর price refresh করতে পারো।
- **Test:** Item add করো, product-এর price বদলাও, checkout করো; order-এ নতুন price আসছে কিনা assert করো।

## H2 — Guest session ID predictable; cart/wishlist hijack
> **Status: fixed।** Browser এখন `crypto.randomUUID()` দিয়ে ID বানায় (পুরোনো ID হলে বদলে নেয়)। `App\Support\GuestSession` header UUID না হলে 422 দেয়; login/register-এ শুধু `user_id`-ছাড়া guest cart/wishlist merge হয়, আর user-এর আগে থেকে cart থাকলে item যোগ হয়। Test: `tests/Feature/GuestSessionTest.php`।
- **কোথায়:** `resources/js/api.js` (ID বানানো হয় `Math.random()` দিয়ে), `CartService::getOrCreateCart()`, `CustomerAuthController`-এর register/login (`X-Session-ID` ধরে merge)।
- **সমস্যা:** Client `X-Session-ID` header পাঠায়, server-এ তার format check নেই। Weak ID guess করা যায়, আর কারো ID জানা থাকলে তার cart দেখা যায়। Login/register-এ ওই ID-র cart আর wishlist সরাসরি user-এর account-এ চলে যায়।
- **Fix:** `crypto.randomUUID()` দিয়ে ID বানাও; server-এ header UUID format-এ validate করো; merge-এর সময় শুধু সেই guest cart নাও যেটার `user_id` নেই; user-এর আগে থেকে cart থাকলে নতুন cart না বানিয়ে item merge করো।
- **Test:** `user_id` set করা cart session ID দিয়ে নেওয়া যায় না; invalid ID reject হয়।

## H3 — Token কখনো expire বা revoke হয় না
> **Status: fixed (আংশিক)।** Admin login token ১২ ঘণ্টায় expire হয় (`ADMIN_TOKEN_HOURS`), বাকি token ৩০ দিনে (`SANCTUM_EXPIRATION`)। `User` model-এ user deactivate, delete, role বা password বদলালে আর `UserController`-এ role assign বদলালে সব token delete হয়। Test: `tests/Feature/TokenLifecycleTest.php`। বাকি: token এখনো localStorage-এ; HttpOnly cookie (Sanctum SPA) করা হয়নি।
- **কোথায়:** `config/sanctum.php` (`expiration => null`), `resources/js/stores/auth.js` (token localStorage-এ), `UserController`-এর update/destroy।
- **সমস্যা:** Admin token চুরি হলে সেটা চিরকাল কাজ করবে। User deactivate, role change বা password change করলেও তার token revoke হয় না।
- **Fix:** Expiry বসাও (admin-এর জন্য ৮–১২ ঘণ্টা)। Deactivate, delete, role change আর password change-এ user-এর সব token delete করো। Best: admin-এর জন্য Sanctum SPA cookie auth (HttpOnly)।
- **Test:** Deactivated user-এর token 401/403 পায়; expired token reject হয়।

## H4 — RBAC permission কোথাও enforce হয় না
> **Status: fixed।** `permission` middleware (`EnsurePermission`) দিয়ে route গুলো module অনুযায়ী guard করা, HTTP method থেকে action (view/create/update/delete) ঠিক হয়। Registry resource-এর check `ResourceController`-এ। `User::permissionSlugs()` role না থাকা manager-কে default `manager` role-এর permission দেয়। Migration `2026_11_28_100000_seed_default_role_permissions` permission আর manager role সব install-এ নিশ্চিত করে। Test: `tests/Feature/RolePermissionsTest.php`।
- **কোথায়:** `app/Models/User.php`-এর `hasPermission()` define করা আছে কিন্তু কোথাও call হয় না; `routes/api.php` শুধু admin/manager check করে।
- **সমস্যা:** যেকোনো `manager` orders, coupons, products, media, settings, audit log সবকিছু ব্যবহার করতে পারে। Role/permission-এর screen-এর আসলে কোনো effect নেই।
- **Fix:** আগে decide করো: হয় enforce করবে (`permission:slug` middleware বা module-wise Policy, আর seed করা permission-এর সাথে মিলিয়ে), নয়তো permission UI সরিয়ে দেবে। Users/roles/SMTP-এর জন্য `admin:full` থাকবে।
- **Test:** Permission ছাড়া manager ওই module-এ 403 পায়।

## H5 — Rate limit নেই
> **Status: fixed।** `AppServiceProvider`-এ named limiter: track-order ৫, coupon ১০, contact ৫, checkout ১০, cart ৬০ (প্রতি মিনিটে, IP+session ধরে) আর তার সাথে IP-প্রতি ৫ গুণ সীমা, যাতে নতুন session ID বানিয়ে ফাঁকি দেওয়া না যায়। Test: `tests/Feature/RateLimitTest.php`। Production-এ `TrustProxies` শুধু `127.0.0.1` বিশ্বাস করে (nginx একই machine-এ থাকলে ঠিক আছে)।
- **কোথায়:** `routes/api.php`-এর public group।
- **সমস্যা:** `/checkout`, `/track-order` (order number + email brute-force করা যায়), `/cart/coupon` (coupon code guess করা যায়), `/contact` (spam), `/cart/*`-এ কোনো `throttle` নেই।
- **Fix:** `throttle` middleware বসাও: track-order 5/min, coupon 10/min, contact 5/min, checkout ~10/min, cart ~60/min। IP + session দিয়ে named limiter বানানোর কথাও ভাবতে পারো।
- **Test:** N+1 তম request 429 ফেরত দেয়।

## H6 — Steadfast courier integration আসলে fake
- **কোথায়:** `app/Http/Controllers/Api/V1/Admin/LogisticsController.php`-এর `createConsignment()` / `trackStatus()`।
- **সমস্যা:** কোনো API call হয় না। Consignment ID আসে `uniqid()` থেকে, tracking code `rand()` থেকে, আর response বলে "created via Steadfast Logistics API"। Admin ভাবে courier book হয়ে গেছে, আসলে হয়নি।
- **Fix:** হয় আসল Steadfast API integrate করো (key/secret settings-এ encrypted রেখে), নয়তো screen আর response-এ পরিষ্কার লিখে দাও যে এটা manual/simulated।
