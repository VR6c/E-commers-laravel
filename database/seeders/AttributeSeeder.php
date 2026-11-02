<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Size' => ['XS', 'Small', 'Medium', 'Large', 'XL', 'XXL'],
            'Color' => ['Red', 'Green', 'Blue', 'Black', 'White', 'Yellow', 'Navy', 'Grey'],
            'Material' => ['Cotton', 'Polyester', 'Leather', 'Denim', 'Linen', 'Wool'],
        ];

        foreach ($attributes as $attrName => $values) {
            $attribute = Attribute::firstOrCreate(['name' => $attrName]);

            foreach ($values as $val) {
                AttributeValue::firstOrCreate([
                    'attribute_id' => $attribute->id,
                    'value' => $val,
                ]);
            }
        }

        $this->command->info('AttributeSeeder: attributes and values seeded.');
    }
}
