<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $sampleOrders = array_slice([
            [
                'customer_name' => 'Aditi Sharma',
                'customer_phone' => '9876543210',
                'customer_email' => 'aditi@example.com',
                'shipping_address' => '12 MG Road, Indiranagar',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pincode' => '560038',
                'payment_method' => 'cod',
                'subtotal' => 799,
                'total' => 799,
                'order_status' => 'Delivered',
                'item_name' => 'Floral Printed Smocked Peplum Top',
                'item_size' => 'M',
                'item_color' => 'Pink',
                'item_qty' => 1,
                'item_price' => 799,
            ],
            [
                'customer_name' => 'Priya Verma',
                'customer_phone' => '9812345678',
                'customer_email' => 'priya@example.com',
                'shipping_address' => '5B Andheri West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400058',
                'payment_method' => 'upi',
                'subtotal' => 1498,
                'total' => 1498,
                'order_status' => 'Shipped',
                'item_name' => 'Handblock Printed Pure Cotton Straight Kurti',
                'item_size' => 'L',
                'item_color' => 'Beige',
                'item_qty' => 1,
                'item_price' => 1498,
            ],
            [
                'customer_name' => 'Kavita Nair',
                'customer_phone' => '9923456789',
                'customer_email' => 'kavita@example.com',
                'shipping_address' => '33 Lajpat Nagar',
                'city' => 'Delhi NCR',
                'state' => 'Delhi',
                'pincode' => '110024',
                'payment_method' => 'card',
                'subtotal' => 1699,
                'total' => 1699,
                'order_status' => 'Processing',
                'item_name' => 'Floor Sweeping Flared Anarkali Kurti',
                'item_size' => 'M',
                'item_color' => 'Pink',
                'item_qty' => 1,
                'item_price' => 1699,
            ],
        ], 0, 1);

        $products = Product::with('images')->take(5)->get();

        foreach ($sampleOrders as $i => $o) {
            $order = Order::create([
                'order_number' => 'ZYRA-' . (849201 + $i),
                'customer_name' => $o['customer_name'],
                'customer_email' => $o['customer_email'],
                'customer_phone' => $o['customer_phone'],
                'shipping_address' => $o['shipping_address'],
                'city' => $o['city'],
                'state' => $o['state'],
                'pincode' => $o['pincode'],
                'payment_method' => $o['payment_method'],
                'payment_status' => $o['order_status'] === 'Delivered' ? 'paid' : 'pending',
                'subtotal' => $o['subtotal'],
                'discount' => 0,
                'shipping_cost' => 0,
                'tax' => 0,
                'total' => $o['total'],
                'order_status' => $o['order_status'],
            ]);

            $product = $products->get($i);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product?->id,
                'product_name' => $o['item_name'],
                'product_image' => $product?->image ?? '',
                'price' => $o['item_price'],
                'quantity' => $o['item_qty'],
                'size' => $o['item_size'],
                'color' => $o['item_color'],
                'total' => $o['item_price'] * $o['item_qty'],
            ]);
        }
    }
}
