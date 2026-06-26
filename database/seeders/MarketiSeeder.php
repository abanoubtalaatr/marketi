<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliverySlot;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketiSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@marketi.com',
            'phone' => '0500000000',
            'country_phone_code' => '+966',
            'password' => 'password',
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        UserProfile::create(['user_id' => $admin->id]);

        $customer = User::create([
            'name' => 'Customer',
            'username' => 'customer',
            'email' => 'customer@marketi.com',
            'phone' => '0511111111',
            'country_phone_code' => '+966',
            'password' => 'password',
            'role' => UserRole::Customer,
            'is_active' => true,
        ]);
        UserProfile::create(['user_id' => $customer->id, 'city' => 'Riyadh', 'country' => 'SA']);

        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Fashion', 'description' => 'Clothing and accessories'],
            ['name' => 'Home & Garden', 'description' => 'Home improvement and garden'],
            ['name' => 'Sports', 'description' => 'Sports equipment and apparel'],
            ['name' => 'Beauty', 'description' => 'Beauty and personal care'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                ...$cat,
                'slug' => Str::slug($cat['name']),
                'is_active' => true,
            ]);
        }

        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'LG'];
        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'is_active' => true,
            ]);
        }

        $productNames = [
            'Wireless Headphones', 'Smart Watch', 'Running Shoes', 'Laptop Bag',
            'Bluetooth Speaker', 'Fitness Tracker', 'Sunglasses', 'Backpack',
            'Phone Case', 'Tablet Stand', 'Gaming Mouse', 'USB-C Hub',
            'Portable Charger', 'Desk Lamp', 'Water Bottle', 'Yoga Mat',
            'Winter Jacket', 'Casual T-Shirt', 'Jeans', 'Sneakers',
        ];

        foreach ($productNames as $i => $name) {
            $product = Product::create([
                'name' => $name,
                'slug' => Str::slug($name).'-'.$i,
                'description' => "High quality {$name} for everyday use.",
                'price' => fake()->randomFloat(2, 29, 999),
                'rating' => fake()->randomFloat(2, 3, 5),
                'rating_count' => fake()->numberBetween(10, 500),
                'stock_quantity' => fake()->numberBetween(10, 100),
                'category_id' => Category::inRandomOrder()->first()->id,
                'brand_id' => Brand::inRandomOrder()->first()->id,
                'is_active' => true,
            ]);

            foreach (['S', 'M', 'L', 'XL'] as $size) {
                $product->sizes()->create([
                    'size' => $size,
                    'stock_quantity' => fake()->numberBetween(5, 30),
                ]);
            }
        }

        DeliverySlot::create(['label' => 'Morning (9AM - 12PM)', 'start_time' => '09:00', 'end_time' => '12:00']);
        DeliverySlot::create(['label' => 'Afternoon (12PM - 5PM)', 'start_time' => '12:00', 'end_time' => '17:00']);
        DeliverySlot::create(['label' => 'Evening (5PM - 9PM)', 'start_time' => '17:00', 'end_time' => '21:00']);

        SubscriptionPlan::create([
            'name' => 'Premium Monthly',
            'description' => 'Monthly premium subscription with exclusive offers',
            'price' => 49.99,
            'billing_cycle' => 'monthly',
        ]);

        SubscriptionPlan::create([
            'name' => 'Premium Yearly',
            'description' => 'Yearly premium subscription with 20% savings',
            'price' => 479.99,
            'billing_cycle' => 'yearly',
        ]);
    }
}
