<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'admin_or_user_id' => 1,
                'item_code' => 'COD-001',
                'item_name' => 'Clear Float Glass 4mm',
                'product_mode' => 'measurements',
                'height' => 12,
                'width' => 12,
                'area' => 144,
                'wholesale_price' => 150.00,
                'retail_price' => 180.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'COD-002',
                'item_name' => 'Clear Float Glass 6mm',
                'product_mode' => 'measurements',
                'height' => 12,
                'width' => 12,
                'area' => 144,
                'wholesale_price' => 200.00,
                'retail_price' => 240.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'COD-003',
                'item_name' => 'Tinted Glass Bronze 5mm',
                'product_mode' => 'measurements',
                'height' => 10,
                'width' => 10,
                'area' => 100,
                'wholesale_price' => 250.00,
                'retail_price' => 300.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'COD-004',
                'item_name' => 'Mirror Glass 4mm',
                'product_mode' => 'measurements',
                'height' => 8,
                'width' => 6,
                'area' => 48,
                'wholesale_price' => 180.00,
                'retail_price' => 220.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'COD-005',
                'item_name' => 'Tempered Glass 8mm',
                'product_mode' => 'measurements',
                'height' => 24,
                'width' => 18,
                'area' => 432,
                'wholesale_price' => 450.00,
                'retail_price' => 550.00,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['item_code' => $product['item_code']],
                $product
            );
        }
    }
}
