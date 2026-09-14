<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\MenuItemService;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    protected $menuItemService;

    public function __construct(MenuItemService $menuItemService)
    {
        $this->menuItemService = $menuItemService;
    }

    public function getData(Request $request)
    {
        $menuItems = $this->menuItemService->getAllMenuItems();

        return datatables()->of($menuItems)
            ->addColumn('action', function ($row) {
                return '<div class="dt-actions">
                    <a href="'.route('admin.items.edit', $row->id).'" class="btn-action btn-action-edit" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <button type="button" class="btn-action btn-action-delete" onclick="deleteMenuItem('.$row->id.')" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function index(Request $request, $menu = null)
    {
        if (! $menu && $request->has('menu_id')) {
            $menu = $request->get('menu_id');
        }
        $menuModel = $menu instanceof Menu ? $menu : ($menu ? Menu::find($menu) : Menu::first());

        return view('admin.menu_items.index', ['menu' => $menuModel]);
    }

    public function create($menuId)
    {
        $menu = Menu::findOrFail($menuId);
        $menus = Menu::all();

        return view('admin.menu_items.create', compact('menu', 'menus'));
    }

    public function store(Request $request, $menuId)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'order_number' => 'required|integer',
            'parent_id' => 'nullable|exists:menu_items,id',
            'title' => 'required|array',
            'title.*' => 'required|string|max:255',
        ]);

        try {
            $this->menuItemService->createMenuItem($request, $menuId);

            return redirect()->route('admin.menus.items.index', ['menu' => $menuId])
                ->with('success', 'Menu item created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create menu item.');
        }
    }

    public function edit($id)
    {
        $menuItem = MenuItem::with('menu')->findOrFail($id);
        $menus = Menu::with('menuItems')->get();

        return view('admin.menu_items.edit', compact('menuItem', 'menus'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order_number' => 'required|integer',
            'title' => 'required|array',
            'title.*' => 'required|string|max:255',
        ]);

        $this->menuItemService->updateMenuItem($request, $request->menu_id, $id);

        return redirect()->route('admin.menus.item.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        if ($menuItem->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error deleting menu item.',
        ]);
    }
}
