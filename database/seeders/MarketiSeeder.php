<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliverySlot;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
                'image' => $this->categoryImage($cat['name']),
                'is_active' => true,
            ]);
        }

        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'LG', 'Dyson', 'Zara', 'H&M', 'Canon', 'Bose', 'Puma'];
        foreach ($brands as $i => $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'logo' => $this->brandLogo($i),
                'is_active' => true,
            ]);
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

    private function categoryImage(string $name): string
    {
        return $this->downloadSeedImage('categories', Str::slug($name), 600, 400);
    }

    private function brandLogo(int $index): string
    {
        return $this->downloadSeedImage('brands', "brand-{$index}", 400, 400);
    }

    private function downloadSeedImage(string $folder, string $key, int $w, int $h): string
    {
        $path = "{$folder}/{$key}.jpg";

        try {
            $response = Http::timeout(15)->get("https://picsum.photos/seed/{$key}/{$w}/{$h}");
            if ($response->successful() && strlen($response->body()) > 500) {
                Storage::disk('public')->put($path, $response->body());

                return $path;
            }
        } catch (\Throwable) {
            //
        }

        return "https://picsum.photos/seed/{$key}/{$w}/{$h}";
    }
}
