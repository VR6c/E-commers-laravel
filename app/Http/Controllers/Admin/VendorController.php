<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    public function index()
    {
        return view('admin.vendors.index');
    }

    public function getVendorData()
    {
        $vendors = Vendor::select(['id', 'name', 'email', 'phone', 'status']);

        return DataTables::of($vendors)
            ->addColumn('action', function ($vendor) {
                return '<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteVendor('.$vendor->id.')"><i class="bi bi-trash-fill text-danger"></i></span>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:vendors,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->symbols(),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'status' => ['required', 'in:active,inactive,banned'],
        ]);

        Vendor::create([
            'name' => trim($validatedData['name']),
            'email' => strtolower(trim($validatedData['email'])),
            'password' => Hash::make($validatedData['password']),
            'phone' => $validatedData['phone'] ?? null,
            'status' => $validatedData['status'],
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor registered successfully!');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully.',
        ]);
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);

        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:vendors,email,'.$id],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'status' => ['required', 'in:active,inactive,banned'],
        ];

        // Only validate password if provided
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)->symbols()];
        }

        $validated = $request->validate($rules);

        $vendor->name = trim($validated['name']);
        $vendor->email = strtolower(trim($validated['email']));
        $vendor->phone = $validated['phone'] ?? null;
        $vendor->status = $validated['status'];

        if ($request->filled('password')) {
            $vendor->password = Hash::make($validated['password']);
        }

        $vendor->save();

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }
}
