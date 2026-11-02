<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PaymentGatewayConfigController extends Controller
{
    public function index()
    {
        return view('admin.payment_gateway_configs.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $configs = PaymentGatewayConfig::with('gateway')->select('payment_gateway_configs.*');

            return DataTables::of($configs)
                ->addColumn('gateway_name', fn ($row) => $row->gateway?->name ?? '—')
                ->addColumn('environment', fn ($row) => ucfirst($row->environment))
                ->addColumn('action', function ($row) {
                    return '
                        <a href="'.route('admin.payment_gateway_configs.edit', $row->id).'"
                           class="btn btn-sm btn-primary me-1">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <span class="border border-danger dt-trash rounded-3 d-inline-block"
                              onclick="deleteConfig('.$row->id.')">
                            <i class="bi bi-trash-fill text-danger"></i>
                        </span>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function create()
    {
        $gateways = PaymentGateway::all();

        return view('admin.payment_gateway_configs.create', compact('gateways'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
            'key_name' => 'required|string|max:100',
            'key_value' => 'required|string',
            'environment' => 'required|in:sandbox,production',
        ]);

        PaymentGatewayConfig::create($request->only(['gateway_id', 'key_name', 'key_value', 'environment', 'is_encrypted']));

        return redirect()
            ->route('admin.payment_gateway_configs.index')
            ->with('success', 'Payment gateway config created successfully.');
    }

    public function edit($id)
    {
        $config = PaymentGatewayConfig::findOrFail($id);
        $gateways = PaymentGateway::all();

        return view('admin.payment_gateway_configs.edit', compact('config', 'gateways'));
    }

    public function update(Request $request, $id)
    {
        $config = PaymentGatewayConfig::findOrFail($id);

        $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
            'key_name' => 'required|string|max:100',
            'key_value' => 'required|string',
            'environment' => 'required|in:sandbox,production',
        ]);

        $config->update($request->only(['gateway_id', 'key_name', 'key_value', 'environment', 'is_encrypted']));

        return redirect()
            ->route('admin.payment_gateway_configs.index')
            ->with('success', 'Payment gateway config updated successfully.');
    }

    public function destroy($id)
    {
        try {
            PaymentGatewayConfig::findOrFail($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment gateway config deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting payment gateway config ID {$id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the config.',
            ]);
        }
    }
}
