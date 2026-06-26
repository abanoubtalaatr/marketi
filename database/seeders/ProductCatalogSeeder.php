<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    private int $imageSeed = 1000;

    public function run(): void
    {
        $this->command?->info('Seeding product catalog with images...');

        $catalog = $this->catalog();

        foreach ($catalog as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($products as $index => $item) {
                $slug = Str::slug($item['name']).'-'.Str::random(4);
                $imagePath = $this->fetchImage("cat-{$category->id}-{$index}");

                $product = Product::create([
                    'name' => $item['name'],
                    'slug' => $slug,
                    'description' => $item['description'],
                    'main_image' => $imagePath,
                    'price' => $item['price'],
                    'rating' => round(mt_rand(350, 500) / 100, 2),
                    'rating_count' => mt_rand(25, 1200),
                    'stock_quantity' => mt_rand(15, 200),
                    'category_id' => $category->id,
                    'brand_id' => Brand::inRandomOrder()->value('id'),
                    'is_active' => true,
                ]);

                foreach ($item['sizes'] ?? ['S', 'M', 'L', 'XL'] as $size) {
                    $product->sizes()->create([
                        'size' => $size,
                        'stock_quantity' => mt_rand(5, 40),
                    ]);
                }

                for ($g = 1; $g <= 2; $g++) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => "https://picsum.photos/seed/gallery-{$product->id}-{$g}/800/800",
                        'sort_order' => $g,
                    ]);
                }
            }
        }

        $this->command?->info('Product catalog seeded: '.Product::count().' products.');
    }

    private function fetchImage(string $seedKey): string
    {
        $seed = $this->imageSeed++;
        $filename = Str::slug($seedKey).'-'.$seed.'.jpg';
        $path = "products/{$filename}";

        try {
            $response = Http::timeout(20)
                ->withOptions(['allow_redirects' => true])
                ->get("https://picsum.photos/seed/{$seedKey}{$seed}/800/800");

            if ($response->successful() && strlen($response->body()) > 1000) {
                Storage::disk('public')->put($path, $response->body());

                return $path;
            }
        } catch (\Throwable) {
            // fallback below
        }

        return "https://picsum.photos/seed/{$seedKey}{$seed}/800/800";
    }

    private function catalog(): array
    {
        return [
            'Electronics' => $this->electronics(),
            'Fashion' => $this->fashion(),
            'Home & Garden' => $this->homeGarden(),
            'Sports' => $this->sports(),
            'Beauty' => $this->beauty(),
        ];
    }

    private function electronics(): array
    {
        $names = [
            'AirPods Pro Wireless Earbuds', 'Samsung Galaxy S24 Ultra', 'iPhone 15 Pro Max Case',
            'Sony WH-1000XM5 Headphones', 'MacBook Pro 14-inch Sleeve', 'Logitech MX Master 3S Mouse',
            'Anker 737 Power Bank 24000mAh', 'iPad Air Tablet Stand', 'JBL Flip 6 Bluetooth Speaker',
            'Ring Video Doorbell', 'Amazon Echo Dot 5th Gen', 'Google Nest Mini Smart Speaker',
            'Samsung 55" QLED 4K TV', 'LG OLED C3 65-inch TV', 'Roku Streaming Stick 4K',
            'Canon EOS R50 Mirrorless Camera', 'GoPro HERO12 Black', 'DJI Mini 3 Drone',
            'Apple Watch Series 9', 'Fitbit Charge 6 Fitness Tracker', 'Garmin Forerunner 265',
            'Bose QuietComfort Ultra Earbuds', 'SteelSeries Arctis Nova Pro Headset', 'Razer BlackWidow V4 Keyboard',
            'ASUS ROG Strix Gaming Laptop Bag', 'SanDisk 1TB Extreme Portable SSD', 'Samsung T7 Shield 2TB SSD',
            'Belkin 3-in-1 MagSafe Charger', 'Anker USB-C Hub 7-in-1', 'Philips Hue Smart Bulb Starter Kit',
        ];

        return $this->mapProducts($names, 'Premium electronics with warranty and fast delivery.', [49, 4999]);
    }

    private function fashion(): array
    {
        $names = [
            'Nike Air Max 90 Sneakers', 'Adidas Ultraboost Running Shoes', 'Levi\'s 501 Original Jeans',
            'Zara Linen Blend Shirt', 'H&M Cotton Oversized Hoodie', 'Calvin Klein Slim Fit Polo',
            'Tommy Hilfiger Classic Chinos', 'Puma Essentials Track Jacket', 'New Balance 574 Sneakers',
            'Ray-Ban Aviator Sunglasses', 'Michael Kors Leather Handbag', 'Coach Crossbody Bag',
            'North Face Puffer Jacket', 'Columbia Waterproof Rain Jacket', 'Uniqlo Heattech Crew Neck',
            'Lululemon Align Leggings', 'Under Armour Training Shorts', 'Ralph Lauren Oxford Shirt',
            'Gucci Inspired Silk Scarf', 'Burberry Style Trench Coat', 'Dr. Martens 1460 Boots',
            'Vans Old Skool Classic', 'Converse Chuck Taylor All Star', 'Timberland Premium Boots',
            'Fossil Leather Watch', 'Casio G-Shock Digital Watch', 'Swarovski Crystal Earrings',
            'Pandora Charm Bracelet', 'Gold Plated Chain Necklace', 'Silver Hoop Earrings Set',
        ];

        return $this->mapProducts($names, 'Trendy fashion piece crafted for comfort and style.', [29, 899]);
    }

    private function homeGarden(): array
    {
        $names = [
            'Dyson V15 Detect Vacuum', 'KitchenAid Stand Mixer', 'Ninja Foodi Air Fryer',
            'Instant Pot Duo 7-in-1', 'Nespresso Vertuo Coffee Machine', 'Keurig K-Elite Brewer',
            'Cuisinart 14-Cup Coffee Maker', 'iRobot Roomba j7+ Robot Vacuum', 'Shark Navigator Lift-Away',
            'Philips Air Fryer XXL', 'Tefal Non-Stick Cookware Set', 'Pyrex Glass Storage Set',
            'IKEA Style Desk Lamp LED', 'Modern Ceramic Plant Pot Set', 'Outdoor Patio String Lights',
            'Garden Tool Set 10-Piece', 'Hose Reel with 50ft Hose', 'BBQ Grill Cover Premium',
            'Memory Foam Pillow Queen', 'Egyptian Cotton Sheet Set', 'Weighted Blanket 15lbs',
            'Blackout Curtains 2-Panel', 'Wall Mirror Round 24-inch', 'Floating Shelves Set of 3',
            'Scented Candle Gift Set', 'Essential Oil Diffuser', 'Robot Lawn Mower Compact',
            'Smart Thermostat WiFi', 'Security Camera 2-Pack', 'Smart Door Lock Keyless',
        ];

        return $this->mapProducts($names, 'Quality home essential designed for everyday living.', [19, 1499]);
    }

    private function sports(): array
    {
        $names = [
            'Wilson NBA Official Basketball', 'Adidas Tiro Training Pants', 'Nike Dri-FIT Training Top',
            'Yoga Mat 6mm Non-Slip', 'Resistance Bands Set 5-Pack', 'Adjustable Dumbbell Set 24kg',
            'Bowflex SelectTech Dumbbells', 'Peloton Style Spin Bike', 'Treadmill Folding Electric',
            'Everlast Pro Boxing Gloves', 'UFC MMA Training Gloves', 'Speed Jump Rope Pro',
            'Foam Roller Muscle Recovery', 'Pull-Up Bar Doorway', 'Ab Roller Wheel Core Trainer',
            'Camping Tent 4-Person', 'Sleeping Bag -10°C Rated', 'Hiking Backpack 50L',
            'Trekking Poles Carbon Fiber', 'Hydration Pack 2L', 'Cycling Helmet MIPS',
            'Mountain Bike Gloves', 'Swim Goggles Anti-Fog', 'Snorkel Set Adult',
            'Tennis Racket Graphite Pro', 'Badminton Racket Set', 'Golf Polo Shirt Dry Fit',
            'Football Boots FG Studs', 'Cricket Bat English Willow', 'Skateboard Complete 8.0"',
        ];

        return $this->mapProducts($names, 'Professional sports gear for training and competition.', [15, 2999]);
    }

    private function beauty(): array
    {
        $names = [
            'La Mer Moisturizing Cream', 'Estée Lauder Advanced Night Serum', 'Clinique Dramatically Different Lotion',
            'MAC Ruby Woo Lipstick', 'Charlotte Tilbury Pillow Talk Lip', 'NARS Radiant Creamy Concealer',
            'Urban Decay Naked Eyeshadow Palette', 'Too Faced Born This Way Foundation', 'Fenty Beauty Pro Filt\'r Foundation',
            'Olaplex No.3 Hair Perfector', 'Dyson Airwrap Multi-Styler', 'GHD Platinum+ Hair Straightener',
            'Moroccanoil Treatment Original', 'Kerastase Elixir Ultime Oil', 'Briogeo Don\'t Despair Repair Mask',
            'CeraVe Hydrating Cleanser', 'The Ordinary Niacinamide 10%', 'Paula\'s Choice BHA Exfoliant',
            'Neutrogena Hydro Boost Gel', 'La Roche-Posay Anthelios SPF50', 'Kiehl\'s Ultra Facial Cream',
            'Tom Ford Black Orchid Perfume', 'Chanel No.5 Eau de Parfum', 'Dior Sauvage EDT',
            'Jo Malone English Pear Cologne', 'Versace Eros Eau de Toilette', 'YSL Libre Eau de Parfum',
            'Gillette Fusion5 Razor Kit', 'Philips OneBlade Face + Body', 'Oral-B iO Series 9 Toothbrush',
        ];

        return $this->mapProducts($names, 'Luxury beauty product for radiant skin and hair.', [12, 599], ['50ml', '100ml', '200ml']);
    }

    private function mapProducts(array $names, string $descriptionTemplate, array $priceRange, array $sizes = ['S', 'M', 'L', 'XL']): array
    {
        return array_map(function (string $name) use ($descriptionTemplate, $priceRange, $sizes) {
            return [
                'name' => $name,
                'description' => "{$descriptionTemplate} {$name} — authentic quality, ships within 2-3 business days across Saudi Arabia.",
                'price' => round(mt_rand($priceRange[0] * 100, $priceRange[1] * 100) / 100, 2),
                'sizes' => $sizes,
            ];
        }, $names);
    }
}
