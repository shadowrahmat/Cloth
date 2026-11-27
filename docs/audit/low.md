# Low Severity ও Code Quality

## L1 — FormRequest, Policy, API Resource নেই
Validation controller-এর ভেতরেই inline, আর response-এর shape এক controller থেকে আরেক controller-এ আলাদা। বড় endpoint (checkout, users, products) আগে FormRequest-এ সরাও, আর consistent output-এর জন্য Resource ব্যবহার করো। এটা ধীরে ধীরে করবে, এক ধাক্কায় বড় refactor না।

## L2 — `categories`-এর route closure
`routes/api.php`-এ ৫টা closure `ResourceController` call করে। Closure থাকলে `php artisan route:cache` কাজ করে না। এগুলো সাধারণ route group বা ছোট একটা controller দিয়ে replace করো।

## L3 — ActivityLog-এর duplicate code
`ActivityLog::create([... ip_address, user_agent])` ১৫+ জায়গায় repeat হয়েছে। একটা helper বানাও (যেমন `ActivityLog::record($request, $action, $model, $description)`)।

## L4 — CheckoutService-এ USD / US hardcoded
Bangladesh-এর store হলেও currency `'USD'` আর default country `'US'`। Client `payment_id` পাঠালে সেটা transaction ID হিসেবে save হয়। Currency আর country settings থেকে পড়ো; শুধু `cod` থাকা পর্যন্ত client-এর `payment_id` ignore করো।

## L5 — Order number ছোট
`ORD-YYYYMMDD-` এর পরে মাত্র ৫টা random character, collision হতে পারে। Unique violation হলে retry করো, নয়তো suffix বড় করো।

## L6 — Register-এ existing email ধরা যায়
`unique:users` error থেকে বোঝা যায় কোন email registered। UX-এর জন্য এটা মেনে নিতে পারো, অথবা generic message দিয়ে email-এ notice পাঠাতে পারো।

## L7 — Contact message-এর length
`message`-এ `max` নেই। একটা limit দাও (যেমন 5000)।

## L8 — Review-এর email verified না
`author_email` verify করা হয় না। এর ওপর ভিত্তি করে "verified buyer" badge দেখিও না।

## L9 — Session encryption
`SESSION_ENCRYPT` default false। `deploy/env.production.example`-এ true করে দাও।

## L10 — Test নেই
H1 (price), H2 (session merge), H5 (429), M2 (coupon limit), M6 (variant stock)-এর test লেখো। `composer install`-এর পর `php artisan test` চালাবে।
