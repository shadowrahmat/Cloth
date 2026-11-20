<?php

namespace App\Support;

/**
 * The store's emails: what they are for, when they go out, and their
 * standard wording. The admin can rewrite subject/heading/body/button and
 * "reset" brings back the text below.
 *
 * Body text is plain text. A blank line starts a new paragraph, **text** is
 * bold, and {{variable}} is replaced with the order's details.
 */
class EmailTemplateDefaults
{
    /** Variables an admin can put in any template, with a Bengali explanation. */
    public const VARIABLES = [
        'customer_name' => 'গ্রাহকের নাম',
        'order_number' => 'অর্ডার নম্বর',
        'order_date' => 'অর্ডারের তারিখ',
        'order_total' => 'মোট টাকা',
        'order_items' => 'পণ্যের তালিকা (টেবিল)',
        'subtotal' => 'পণ্যের দাম (ডেলিভারি ছাড়া)',
        'delivery_charge' => 'ডেলিভারি চার্জ',
        'discount' => 'ডিসকাউন্ট',
        'payment_method' => 'পেমেন্ট পদ্ধতি',
        'shipping_method' => 'ডেলিভারি এরিয়া',
        'shipping_address' => 'ডেলিভারি ঠিকানা',
        'customer_phone' => 'গ্রাহকের ফোন',
        'customer_email' => 'গ্রাহকের ইমেইল',
        'tracking_number' => 'ট্র্যাকিং নম্বর',
        'track_url' => 'অর্ডার ট্র্যাক করার লিংক',
        'admin_order_url' => 'অ্যাডমিনে অর্ডারের লিংক',
        'store_name' => 'দোকানের নাম',
        'store_phone' => 'দোকানের ফোন',
        'store_email' => 'দোকানের ইমেইল',
        'store_url' => 'ওয়েবসাইটের লিংক',
    ];

    /**
     * key => meta + default content.
     * audience: customer (goes to the buyer) | admin (goes to the shop's notification address)
     */
    public static function all(): array
    {
        return [
            'order_placed' => [
                'name' => 'অর্ডার কনফার্মেশন',
                'audience' => 'customer',
                'trigger' => 'গ্রাহক অর্ডার করার সাথে সাথে',
                'subject' => 'আপনার অর্ডার {{order_number}} আমরা পেয়েছি',
                'heading' => 'অর্ডারের জন্য ধন্যবাদ!',
                'body' => "প্রিয় {{customer_name}},\n\nআপনার অর্ডার **{{order_number}}** আমরা পেয়েছি। খুব শীঘ্রই ফোন করে অর্ডারটি কনফার্ম করা হবে।\n\n{{order_items}}\n\n**ডেলিভারি ঠিকানা:**\n{{shipping_address}}\n\nপেমেন্ট: {{payment_method}}\n\nকোনো প্রশ্ন থাকলে {{store_phone}} নম্বরে কল করুন।",
                'button_label' => 'অর্ডার ট্র্যাক করুন',
                'button_url' => '{{track_url}}',
            ],
            'order_confirmed' => [
                'name' => 'অর্ডার কনফার্ম হয়েছে',
                'audience' => 'customer',
                'trigger' => 'অর্ডারের স্ট্যাটাস “কনফার্মড” করলে',
                'subject' => 'অর্ডার {{order_number}} কনফার্ম হয়েছে',
                'heading' => 'আপনার অর্ডার কনফার্ম হয়েছে',
                'body' => "প্রিয় {{customer_name}},\n\nআপনার অর্ডার **{{order_number}}** কনফার্ম করা হয়েছে। আমরা পণ্য প্যাক করছি, কুরিয়ারে দেওয়ার পর আবার জানাব।\n\nমোট: **{{order_total}}** (ক্যাশ অন ডেলিভারি)",
                'button_label' => 'অর্ডার ট্র্যাক করুন',
                'button_url' => '{{track_url}}',
            ],
            'order_shipped' => [
                'name' => 'অর্ডার কুরিয়ারে দেওয়া হয়েছে',
                'audience' => 'customer',
                'trigger' => 'অর্ডারের স্ট্যাটাস “শিপড” করলে',
                'subject' => 'আপনার অর্ডার {{order_number}} পথে আছে',
                'heading' => 'আপনার পার্সেল রওনা দিয়েছে',
                'body' => "প্রিয় {{customer_name}},\n\nআপনার অর্ডার **{{order_number}}** কুরিয়ারে দেওয়া হয়েছে।\n\nট্র্যাকিং নম্বর: **{{tracking_number}}**\n\nডেলিভারির সময় **{{order_total}}** প্রস্তুত রাখুন। ডেলিভারিম্যান আপনাকে ফোন করবেন।",
                'button_label' => 'পার্সেল ট্র্যাক করুন',
                'button_url' => '{{track_url}}',
            ],
            'order_delivered' => [
                'name' => 'অর্ডার ডেলিভারি হয়েছে',
                'audience' => 'customer',
                'trigger' => 'অর্ডারের স্ট্যাটাস “ডেলিভারড” করলে',
                'subject' => 'অর্ডার {{order_number}} ডেলিভারি সম্পন্ন',
                'heading' => 'পণ্য হাতে পেয়েছেন তো?',
                'body' => "প্রিয় {{customer_name}},\n\nআপনার অর্ডার **{{order_number}}** ডেলিভারি সম্পন্ন হয়েছে। {{store_name}} থেকে কেনাকাটার জন্য ধন্যবাদ!\n\nসাইজ বা অন্য কোনো সমস্যা হলে ৭ দিনের মধ্যে এক্সচেঞ্জ করতে পারবেন। পণ্যটি কেমন লাগল, একটা রিভিউ দিলে খুশি হব।",
                'button_label' => 'আবার কেনাকাটা করুন',
                'button_url' => '{{store_url}}',
            ],
            'order_cancelled' => [
                'name' => 'অর্ডার বাতিল',
                'audience' => 'customer',
                'trigger' => 'অর্ডারের স্ট্যাটাস “বাতিল” করলে',
                'subject' => 'অর্ডার {{order_number}} বাতিল করা হয়েছে',
                'heading' => 'আপনার অর্ডার বাতিল হয়েছে',
                'body' => "প্রিয় {{customer_name}},\n\nআপনার অর্ডার **{{order_number}}** বাতিল করা হয়েছে।\n\nভুল করে বাতিল হয়ে থাকলে বা নতুন করে অর্ডার করতে চাইলে {{store_phone}} নম্বরে যোগাযোগ করুন।",
                'button_label' => 'ওয়েবসাইটে যান',
                'button_url' => '{{store_url}}',
            ],
            'admin_new_order' => [
                'name' => 'নতুন অর্ডারের নোটিফিকেশন',
                'audience' => 'admin',
                'trigger' => 'নতুন অর্ডার এলে আপনার কাছে',
                'subject' => 'নতুন অর্ডার {{order_number}} — {{order_total}}',
                'heading' => 'নতুন অর্ডার এসেছে',
                'body' => "**{{customer_name}}** ({{customer_phone}}) একটি অর্ডার করেছেন।\n\n{{order_items}}\n\n**ঠিকানা:**\n{{shipping_address}}\n\nডেলিভারি: {{shipping_method}}",
                'button_label' => 'অর্ডার দেখুন',
                'button_url' => '{{admin_order_url}}',
            ],
        ];
    }

    /** Which template goes out when an order moves to a status. */
    public const STATUS_TEMPLATES = [
        'confirmed' => 'order_confirmed',
        'shipped' => 'order_shipped',
        'delivered' => 'order_delivered',
        'cancelled' => 'order_cancelled',
    ];
}
