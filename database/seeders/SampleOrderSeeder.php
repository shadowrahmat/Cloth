<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::count() > 0) {
            return;
        }

        $customer = User::where('role', 'customer')->first();
        $products = Product::with('variants')->take(8)->get();

        if ($products->isEmpty()) {
            return;
        }

        $statuses = [
            ['order' => 'delivered', 'payment' => 'paid', 'days_ago' => 6],
            ['order' => 'delivered', 'payment' => 'paid', 'days_ago' => 5],
            ['order' => 'shipped', 'payment' => 'paid', 'days_ago' => 4],
            ['order' => 'processing', 'payment' => 'paid', 'days_ago' => 3],
            ['order' => 'confirmed', 'payment' => 'paid', 'days_ago' => 2],
            ['order' => 'pending', 'payment' => 'pending', 'days_ago' => 1],
            ['order' => 'pending', 'payment' => 'unpaid', 'days_ago' => 0],
        ];

        $clients = [
            ['name' => 'Elena Rostova', 'email' => 'elena.rostova@luxury.com', 'phone' => '+880 1711 001122'],
            ['name' => 'Sophia Al-Mansoor', 'email' => 'sophia.m@atelier.ae', 'phone' => '+880 1722 334455'],
            ['name' => 'Claire Delacroix', 'email' => 'c.delacroix@haute.fr', 'phone' => '+880 1733 445566'],
            ['name' => 'Tahmid Rahman', 'email' => 'tahmid.rahman@dhaka.com', 'phone' => '+880 1744 556677'],
            ['name' => 'Aria Sterling', 'email' => 'aria@sterlingatelier.com', 'phone' => '+880 1755 667788'],
            ['name' => 'Marcello Rossi', 'email' => 'm.rossi@milano.it', 'phone' => '+880 1766 778899'],
            ['name' => 'Zoya Khan', 'email' => 'zoya.k@voguebd.com', 'phone' => '+880 1777 889900'],
        ];

        foreach ($statuses as $index => $cfg) {
            $client = $clients[$index % count($clients)];
            $prod1 = $products[$index % $products->count()];
            $prod2 = $products[($index + 1) % $products->count()];

            $price1 = (float) ($prod1->sale_price ?: $prod1->price);
            $price2 = (float) ($prod2->sale_price ?: $prod2->price);

            $subtotal = $price1 + ($price2 * 2);
            $discount = $index % 2 === 0 ? 30.00 : 0.00;
            $shipping = 15.00;
            $tax = round($subtotal * 0.05, 2);
            $total = $subtotal - $discount + $shipping + $tax;

            $createdAt = now()->subDays($cfg['days_ago'])->subHours(rand(1, 12));

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $customer?->id,
                'customer_name' => $client['name'],
                'customer_email' => $client['email'],
                'customer_phone' => $client['phone'],
                'shipping_address' => [
                    'first_name' => explode(' ', $client['name'])[0],
                    'last_name' => explode(' ', $client['name'])[1] ?? 'Client',
                    'address_line_1' => 'Road ' . (rand(1, 20)) . ', House ' . (rand(1, 80)) . ', Gulshan-2',
                    'city' => 'Dhaka',
                    'state' => 'Dhaka Division',
                    'postal_code' => '1212',
                    'country' => 'Bangladesh',
                ],
                'billing_address' => [
                    'first_name' => explode(' ', $client['name'])[0],
                    'last_name' => explode(' ', $client['name'])[1] ?? 'Client',
                    'address_line_1' => 'Road ' . (rand(1, 20)) . ', House ' . (rand(1, 80)) . ', Gulshan-2',
                    'city' => 'Dhaka',
                    'state' => 'Dhaka Division',
                    'postal_code' => '1212',
                    'country' => 'Bangladesh',
                ],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $discount > 0 ? 'ATELIER30' : null,
                'shipping_amount' => $shipping,
                'tax_amount' => $tax,
                'total' => $total,
                'payment_method' => $index % 3 === 0 ? 'bkash' : ($index % 3 === 1 ? 'sslcommerz' : 'cod'),
                'payment_status' => $cfg['payment'],
                'order_status' => $cfg['order'],
                'tracking_number' => $cfg['order'] === 'delivered' || $cfg['order'] === 'shipped' ? 'STFD-' . rand(10000000, 99999999) : null,
                'shipping_method' => 'Steadfast Next-Day Express',
                'order_notes' => 'White-glove luxury garment packaging requested.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Add Items
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $prod1->id,
                'variant_id' => $prod1->variants->first()?->id,
                'product_name' => $prod1->name,
                'sku' => $prod1->sku,
                'price' => $price1,
                'quantity' => 1,
                'subtotal' => $price1,
                'color' => 'Noir',
                'size' => 'M',
                'image' => $prod1->featured_image,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $prod2->id,
                'variant_id' => $prod2->variants->first()?->id,
                'product_name' => $prod2->name,
                'sku' => $prod2->sku,
                'price' => $price2,
                'quantity' => 2,
                'subtotal' => $price2 * 2,
                'color' => 'Blanc',
                'size' => 'S',
                'image' => $prod2->featured_image,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($cfg['payment'] === 'paid') {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                    'payment_method' => $order->payment_method,
                    'amount' => $total,
                    'currency' => 'USD',
                    'status' => 'successful',
                    'gateway_response' => ['gateway_reference' => 'BKASH_' . rand(10000, 99999)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}