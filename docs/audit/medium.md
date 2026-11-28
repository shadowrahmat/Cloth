# Medium Severity

## M1 — Exception message client-এ leak হয়
- **কোথায়:** `ResourceController`-এর 500 handler, `MediaController::optimize`, `CheckoutController::process` (সব `Exception`-কে message সহ 422 বানায়)।
- **সমস্যা:** DB বা internal error-এর লেখা browser পর্যন্ত পৌঁছে যেতে পারে।
- **Fix:** User-কে দেখানোর মতো error-এর (cart empty, stock নেই ইত্যাদি) জন্য ছোট একটা business exception class বানাও। শুধু সেগুলোর message দেখাও; বাকি সব log করো আর generic message ফেরত দাও।
- **Status:** Fixed. `BusinessException` তৈরি করা হয়েছে, `CheckoutService` তা throw করে এবং controller শুধু `BusinessException`-এর মেসেজ দেখায়। অন্যান্য internal failure 500-এ generic message দেয় এবং server log-এ context সহ trace জমা রাখে।

## M2 — Coupon usage-এ race condition, per-user limit নেই
- **কোথায়:** `app/Services/CheckoutService.php`-এর step 3, `app/Models/Coupon.php::isValidFor`।
- **সমস্যা:** Limit check আর `increment('used_count')` atomic না, তাই একসাথে অনেক order এলে `usage_limit` পার হয়ে যায়। Customer-wise limit-ও নেই।
- **Fix:** Atomic query ব্যবহার করো: `UPDATE coupons SET used_count = used_count + 1 WHERE id = ? AND (usage_limit IS NULL OR used_count < usage_limit)`, তারপর affected rows check করো। Business-এ লাগলে per-user/email limit যোগ করো।
- **Status:** Fixed. মাইগ্রেশনে `per_user_limit` যোগ করা হয়েছে। `Coupon::isValidFor()` এখন user/email ধরে আগের অর্ডারের সংখ্যা চেক করে। `CheckoutService`-এ atomic conditional increment (`whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit')`) ব্যবহার করা হয়েছে। Test: `tests/Feature/CouponUsageTest.php`।

## M3 — ResourceController-এর input handling
- **কোথায়:** `app/Http/Controllers/Api/V1/Admin/ResourceController.php::index`।
- **সমস্যা:** `sort_by` সরাসরি `orderBy`-তে যায়; `per_page`-এর upper limit নেই আর `-1` দিলে সব row চলে আসে; `search`-এ `%` ও `_` escape হয় না।
- **Fix:** `ResourceRegistry` থেকে sortable column-এর whitelist বানাও; `per_page` cap করো (max 100); LIKE wildcard escape করো। অন্য admin list endpoint-এও একই cap দাও।

## M4 — Password policy আর login throttle
- **কোথায়:** `CustomerAuthController`, `UserController`, `Admin\AuthController`, `routes/api.php`।
- **সমস্যা:** সব জায়গায় `min:6`। Admin login-এর throttle শুধু IP দিয়ে।
- **Fix:** `Password::defaults()` ব্যবহার করো (admin-এর জন্য min 12)। Admin login throttle email + IP দিয়ে করো (named rate limiter)।

## M5 — Cart-এ unpublished product আর যেকোনো quantity add করা যায়
- **কোথায়:** `CartService::addItem`, `CartController::add`।
- **সমস্যা:** `findOrFail` `status` দেখে না, তাই draft/archived product-ও cart-এ যায়। Quantity-তে `min:1` আছে কিন্তু `max` নেই, আর stock শুধু checkout-এ check হয়।
- **Fix:** `status = published` আর active variant বাধ্যতামূলক করো; quantity-তে `max` দাও; `track_inventory` চালু থাকলে available stock-এর বেশি নিতে দিও না। `color`/`size`-এর length validate করো।

## M6 — Variant stock দুইবার decrement হতে পারে
- **কোথায়:** `CheckoutService`-এর step 6 আর `OrderController::updateStatus`-এর restore logic।
- **সমস্যা:** Variant line-এর ক্ষেত্রে product stock আর variant stock দুটোই কমে (আর cancel-এ দুটোই বাড়ে)। কোনটা আসল source of truth সেটা confirm করা দরকার।
- **Fix:** Rule ঠিক করো (variant line-এ variant stock, না হলে product stock), সেটা checkout, cancel/refund আর inventory movement সব জায়গায় একইভাবে apply করো, আর test দিয়ে cover করো।

## M7 — Deploy-এ HTTPS আর hardening header নেই
- **কোথায়:** `deploy/provision.sh`-এর nginx block, `deploy/env.production.example`।
- **সমস্যা:** শুধু port 80, `server_name _`, TLS/HSTS/CSP নেই, `SESSION_SECURE_COOKIE` set করা নেই। Tunnel বা proxy TLS না দিলে login আর token plain HTTP-তে যায়।
- **Fix:** certbot বসাও (বা Cloudflare tunnel document করো), HTTP থেকে HTTPS-এ redirect দাও, HSTS আর CSP যোগ করো, `SESSION_SECURE_COOKIE=true` দাও। `deploy/DEPLOY.md` update করবে (ওটা বাংলাতেই থাকবে)।

## M8 — Image optimize
- **কোথায়:** `MediaController::optimize`।
- **সমস্যা:** `width`-এর upper bound নেই, বড় image-এ memory শেষ হয়ে যেতে পারে। Media delete করলে optimized file মোছা হয় না। Error message leak করে (M1 দেখো)।
- **Fix:** `width` validate করো (যেমন 100–4000), source image-এর dimension cap করো, `destroy`-তে optimized file-ও delete করো।
