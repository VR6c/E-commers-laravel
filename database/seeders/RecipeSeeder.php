<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(5)->get();

        $recipes = [
            [
                'title'       => 'Artisan Espresso Macchiato',
                'slug'        => 'artisan-espresso-macchiato',
                'summary'     => 'A velvety double-shot of specialty espresso "marked" with a silky dollop of steamed whole milk microfoam.',
                'content'     => "The secret to an authentic macchiato lies in the extraction ratio. Grind fresh specialty beans to a fine consistency, tamp with even pressure, and extract in 25 to 30 seconds for optimal crema. Steam the milk until microfoam forms with no visible bubbles.",
                'image_url'   => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&auto=format&fit=crop&q=80',
                'prep_time'   => 5,
                'cook_time'   => 5,
                'servings'    => 1,
                'difficulty'  => 'easy',
                'cuisine'     => 'Italian',
                'calories'    => 45,
                'ingredients' => [
                    ['quantity' => '18', 'unit' => 'g', 'item' => 'Specialty Dark Roast Espresso Beans'],
                    ['quantity' => '60', 'unit' => 'ml', 'item' => 'Filtered Hot Water (92°C - 96°C)'],
                    ['quantity' => '30', 'unit' => 'ml', 'item' => 'Fresh Whole Milk or Oat Milk'],
                    ['quantity' => '1', 'unit' => 'pinch', 'item' => 'Raw Demerara Sugar (Optional)'],
                ],
                'instructions' => [
                    ['step' => 1, 'title' => 'Grind & Dose', 'text' => 'Weigh 18 grams of coffee beans and grind to a fine espresso texture directly into your portafilter.'],
                    ['step' => 2, 'title' => 'Distribute & Tamp', 'text' => 'Evenly level the coffee grounds using a distribution tool, then tamp firmly with 30 lbs of level pressure.'],
                    ['step' => 3, 'title' => 'Extract Espresso', 'text' => 'Lock portafilter into grouphead and extract a double shot (approx. 36g liquid yield in 26-28 seconds) into a preheated demitasse cup.'],
                    ['step' => 4, 'title' => 'Steam & Mark', 'text' => 'Steam 30ml of milk to 60°C creating dense microfoam. Spoon 2 tablespoons of velvety froth onto the center of the crema.'],
                ],
                'nutritional_info' => [
                    'calories' => '45 kcal',
                    'protein'  => '2g',
                    'carbs'    => '3g',
                    'fat'      => '2.5g',
                ],
                'is_active'   => true,
                'is_featured' => true,
            ],
            [
                'title'       => 'Traditional Roman Pasta Carbonara',
                'slug'        => 'traditional-roman-pasta-carbonara',
                'summary'     => 'Authentic Roman carbonara made with crispy guanciale, pecorino romano, fresh egg yolks, and cracked black pepper.',
                'content'     => "True Italian carbonara requires zero cream. The luscious creaminess comes strictly from emulsifying hot pasta water with whipped egg yolks, freshly grated Pecorino Romano, and rendered fat from the crispy guanciale.",
                'image_url'   => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=800&auto=format&fit=crop&q=80',
                'prep_time'   => 15,
                'cook_time'   => 15,
                'servings'    => 2,
                'difficulty'  => 'medium',
                'cuisine'     => 'Italian',
                'calories'    => 620,
                'ingredients' => [
                    ['quantity' => '220', 'unit' => 'g', 'item' => 'Artisan Spaghetti or Rigatoni'],
                    ['quantity' => '120', 'unit' => 'g', 'item' => 'Guanciale (or cured pork jowl/pancetta)'],
                    ['quantity' => '3', 'unit' => 'large', 'item' => 'Fresh Egg Yolks + 1 Whole Egg'],
                    ['quantity' => '50', 'unit' => 'g', 'item' => 'Pecorino Romano DOP Cheese, freshly grated'],
                    ['quantity' => '2', 'unit' => 'tsp', 'item' => 'Freshly Crushed Tellicherry Black Peppercorns'],
                ],
                'instructions' => [
                    ['step' => 1, 'title' => 'Boil Water & Prep Pasta', 'text' => 'Bring a large pot of moderately salted water to a rolling boil. Add pasta and cook until 2 minutes shy of al dente.'],
                    ['step' => 2, 'title' => 'Crisp Guanciale', 'text' => 'Slice guanciale into 1/4-inch lardons and cook in a cold skillet over medium-low heat until browned and crispy. Turn off heat.'],
                    ['step' => 3, 'title' => 'Whisk Egg Cream', 'text' => 'In a bowl, whisk together egg yolks, whole egg, grated Pecorino Romano, and toasted black pepper into a thick paste.'],
                    ['step' => 4, 'title' => 'Emulsify & Serve', 'text' => 'Transfer hot pasta directly into the pan with rendered fat. Pour egg mixture over pasta off-heat, tossing vigorously with a splash of pasta water until glossy and creamy.'],
                ],
                'nutritional_info' => [
                    'calories' => '620 kcal',
                    'protein'  => '28g',
                    'carbs'    => '72g',
                    'fat'      => '26g',
                ],
                'is_active'   => true,
                'is_featured' => true,
            ],
            [
                'title'       => 'Kyoto Ceremony Matcha Latte',
                'slug'        => 'kyoto-ceremony-matcha-latte',
                'summary'     => 'Ceremonial grade Uji matcha whisked with bamboo chasen, balanced with creamy oat milk and organic blossom honey.',
                'content'     => "Ceremonial matcha has a vibrant emerald hue and deep umami sweetness without bitterness. Sift the powder beforehand to prevent clumps, and whisk with water cooled to 80°C in an active 'W' motion.",
                'image_url'   => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=800&auto=format&fit=crop&q=80',
                'prep_time'   => 5,
                'cook_time'   => 5,
                'servings'    => 1,
                'difficulty'  => 'easy',
                'cuisine'     => 'Asian',
                'calories'    => 120,
                'ingredients' => [
                    ['quantity' => '2.5', 'unit' => 'g', 'item' => 'Ceremonial Grade Matcha Powder'],
                    ['quantity' => '50', 'unit' => 'ml', 'item' => 'Pure Spring Water (80°C)'],
                    ['quantity' => '200', 'unit' => 'ml', 'item' => 'Barista Blend Oat Milk or Almond Milk'],
                    ['quantity' => '1', 'unit' => 'tsp', 'item' => 'Organic Wildflower Honey or Agave'],
                ],
                'instructions' => [
                    ['step' => 1, 'title' => 'Sift Matcha', 'text' => 'Sift matcha powder through a fine stainless steel mesh into a traditional chawan bowl.'],
                    ['step' => 2, 'title' => 'Whisk Microfoam', 'text' => 'Pour 50ml hot water (80°C) into bowl. Using a bamboo chasen whisk rapidly in a W shape until thick emerald foam crowns the surface.'],
                    ['step' => 3, 'title' => 'Steam Milk', 'text' => 'Steam oat milk until warm and silky. Sweeten with a touch of organic honey.'],
                    ['step' => 4, 'title' => 'Layer & Enjoy', 'text' => 'Pour steamed milk into a ceramic tumbler and gently float the concentrated whisked matcha over top.'],
                ],
                'nutritional_info' => [
                    'calories' => '120 kcal',
                    'protein'  => '3g',
                    'carbs'    => '14g',
                    'fat'      => '4g',
                ],
                'is_active'   => true,
                'is_featured' => false,
            ],
            [
                'title'       => 'Molten Belgian Dark Chocolate Soufflé',
                'slug'        => 'molten-belgian-dark-chocolate-souffle',
                'summary'     => 'Decadent individual soufflés featuring 72% Belgian dark chocolate with a warm flowing molten center.',
                'content'     => "Butter your ramekins with upward strokes and dust with cocoa powder to encourage maximum vertical rise in the oven. Do not open the oven door during the first 10 minutes of baking.",
                'image_url'   => 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=800&auto=format&fit=crop&q=80',
                'prep_time'   => 20,
                'cook_time'   => 14,
                'servings'    => 2,
                'difficulty'  => 'hard',
                'cuisine'     => 'French',
                'calories'    => 380,
                'ingredients' => [
                    ['quantity' => '100', 'unit' => 'g', 'item' => '72% Single-Origin Belgian Dark Chocolate'],
                    ['quantity' => '40', 'unit' => 'g', 'item' => 'European Grass-Fed Unsalted Butter'],
                    ['quantity' => '2', 'unit' => 'large', 'item' => 'Organic Egg Whites at Room Temperature'],
                    ['quantity' => '2', 'unit' => 'tbsp', 'item' => 'Superfine Castor Sugar'],
                    ['quantity' => '1', 'unit' => 'tbsp', 'item' => 'Dutch Process Cocoa Powder (for ramekins)'],
                ],
                'instructions' => [
                    ['step' => 1, 'title' => 'Prep Ramekins', 'text' => 'Brush two 6oz ramekins with softened butter using vertical strokes, then coat thoroughly with cocoa powder.'],
                    ['step' => 2, 'title' => 'Melt Chocolate & Butter', 'text' => 'Gently melt chopped chocolate and butter over a double boiler until smooth and glossy. Cool slightly.'],
                    ['step' => 3, 'title' => 'Whip Meringue', 'text' => 'Beat egg whites with an electric mixer until frothy, gradually stream in sugar, and whip to medium-firm glossy peaks.'],
                    ['step' => 4, 'title' => 'Fold & Bake', 'text' => 'Gently fold 1/3 of meringue into chocolate, then fold remaining meringue without deflating. Pour into ramekins, level top, and bake at 190°C for 13-14 minutes.'],
                ],
                'nutritional_info' => [
                    'calories' => '380 kcal',
                    'protein'  => '7g',
                    'carbs'    => '34g',
                    'fat'      => '24g',
                ],
                'is_active'   => true,
                'is_featured' => true,
            ],
        ];

        foreach ($recipes as $index => $data) {
            $recipe = Recipe::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Link to products if available
            if ($products->isNotEmpty()) {
                // Link product at matching index or first product
                $linkedProduct = $products->get($index) ?? $products->first();
                if ($linkedProduct) {
                    $recipe->products()->syncWithoutDetaching([$linkedProduct->id]);
                }
            }
        }
    }
}
