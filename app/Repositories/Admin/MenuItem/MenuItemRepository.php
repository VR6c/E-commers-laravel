<?php

namespace App\Repositories\Admin\MenuItem;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function getAll()
    {
        return MenuItem::all();
    }

    public function getMenuItemsByMenuId($menuId)
    {
        return MenuItem::where('menu_id', $menuId)->get();
    }

    public function createMenuItem(Request $request, $menuId)
    {
        return DB::transaction(function () use ($request, $menuId) {
            // title is submitted as title[en]
            $title = is_array($request->title)
                ? ($request->title['en'] ?? reset($request->title))
                : $request->title;

            $slug = Str::slug($title);
            $slugCount = MenuItem::where('slug', 'like', "{$slug}%")->count();
            if ($slugCount > 0) {
                $slug .= '-'.($slugCount + 1);
            }

            return MenuItem::create([
                'menu_id' => $menuId,
                'title' => $title,
                'slug' => $slug,
                'order_number' => $request->order_number,
                'parent_id' => $request->parent_id ?? null,
            ]);
        });
    }

    public function updateMenuItem(Request $request, $menuId, $menuItemId)
    {
        $menuItem = MenuItem::findOrFail($menuItemId);

        $title = is_array($request->title)
            ? ($request->title['en'] ?? reset($request->title))
            : $request->title;

        $menuItem->update([
            'menu_id' => $request->menu_id,
            'parent_id' => $request->parent_id,
            'order_number' => $request->order_number,
            'slug' => Str::slug($title),
            'title' => $title,
        ]);

        return $menuItem;
    }

    public function deleteMenuItem($menuItemId)
    {
        return MenuItem::findOrFail($menuItemId)->delete();
    }
}
