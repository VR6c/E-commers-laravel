<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = DB::table('menus')->where('title', 'Main Menu')->first();

        if (! $menu) {
            $menuId = DB::table('menus')->insertGetId([
                'title' => 'Main Menu',
                'status' => true,
                'date' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $menuId = $menu->id;
        }

        $items = [
            ['slug' => 'home',     'title' => 'Home',         'order_number' => 1, 'parent_id' => null],
            ['slug' => 'about',    'title' => 'About Us',     'order_number' => 2, 'parent_id' => null],
            ['slug' => 'services', 'title' => 'Our Services', 'order_number' => 3, 'parent_id' => null],
            ['slug' => 'blog',     'title' => 'Blog',         'order_number' => 4, 'parent_id' => null],
            ['slug' => 'contact',  'title' => 'Contact Us',   'order_number' => 5, 'parent_id' => null],
        ];

        foreach ($items as $item) {
            $exists = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->where('slug', $item['slug'])
                ->exists();

            if (! $exists) {
                DB::table('menu_items')->insert([
                    'menu_id' => $menuId,
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'order_number' => $item['order_number'],
                    'parent_id' => $item['parent_id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('MenuSeeder: main menu with '.count($items).' items seeded.');
    }
}
