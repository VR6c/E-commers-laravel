<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'About Us',
                'content' => '<p>Welcome to TVR! We are dedicated to providing the best online shopping experience. Founded in 2026, our mission is to offer premium products and top-notch customer service.</p><p>Our collection features curated clothing, accessories, and electronics from verified vendors worldwide. Thank you for choosing us!</p>',
            ],
            [
                'slug' => 'services',
                'title' => 'Our Services',
                'content' => '<p>At TVR, we offer a wide range of services to our customers and vendors:</p><ul><li><strong>Fast Shipping</strong>: Delivery to your doorstep with real-time tracking.</li><li><strong>Vendor Support</strong>: Access to a robust multi-vendor panel with detailed metrics.</li><li><strong>Secure Payments</strong>: Industry-standard encryption using Stripe and PayPal.</li><li><strong>24/7 Support</strong>: Friendly agents ready to assist you any time.</li></ul>',
            ],
            [
                'slug' => 'blog',
                'title' => 'Blog',
                'content' => '<p>Stay tuned for our latest fashion updates, vendor highlights, and tech articles. We regularly share styling tips, product reviews, and ecommerce best practices here!</p>',
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Us',
                'content' => '<p>Have questions, concerns, or feedback? We\'d love to hear from you!</p><p><strong>Email:</strong> tharyvireak121@gmail.com<br><strong>Phone:</strong> 071 675 5350<br><strong>Address:</strong> Phnom Penh, Cambodia</p>',
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'status' => 1,
                ]
            );
        }
    }
}
