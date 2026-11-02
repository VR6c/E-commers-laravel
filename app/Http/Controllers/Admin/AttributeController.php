<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Services\Admin\AttributeService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AttributeController extends Controller
{
    protected AttributeService $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function index()
    {
        $attributes = $this->attributeService->getAllAttributes();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function getAttributesData(Request $request)
    {
        if ($request->ajax()) {
            $attributes = Attribute::with('values')->get();

            return DataTables::of($attributes)
                ->addColumn('values', function ($attribute) {
                    return $attribute->values->map(function ($value) {
                        return '<span class="badge bg-primary">'.e($value->value).'</span>';
                    })->implode(' ');
                })
                ->addColumn('action', function ($attribute) {
                    return '<a href="'.route('admin.attributes.edit', $attribute->id).'" class="btn btn-sm btn-warning">Edit</a>
                            <button class="btn btn-sm btn-danger" onclick="deleteAttribute('.$attribute->id.')">Delete</button>';
                })
                ->rawColumns(['values', 'action'])
                ->make(true);
        }
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:attributes,name',
            'values' => 'required|array|min:1',
        ];

        foreach ($request->input('values', []) as $index => $value) {
            $rules["values.$index"] = 'required|string|max:255|distinct';
        }

        $validated = $request->validate($rules);

        $this->attributeService->createAttribute(['name' => $validated['name'], 'values' => $validated['values']]);

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute = $this->attributeService->getAttributeById($attribute->id);

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*' => 'string|max:255',
        ]);

        $this->attributeService->updateAttribute($attribute, $request->only(['name', 'values']));

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $this->attributeService->deleteAttribute($id);

            return response()->json(['success' => true, 'message' => 'Attribute deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting attribute! Please try again.']);
        }
    }
}
