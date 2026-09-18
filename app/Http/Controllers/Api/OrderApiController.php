<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Store\RecipeAccessService;
use App\Services\Store\RecipePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    /**
     * GET /api/orders
     * List all orders for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        $orders = Order::with(['details.product.thumbnail', 'shippingAddress'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => OrderResource::collection($orders),
        ]);
    }

    /**
     * GET /api/orders/{id}
     * Single order detail for the authenticated customer.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $order = Order::with(['details.product.thumbnail', 'shippingAddress'])
            ->where('customer_id', $customer->id)
            ->find($id);

        if (! $order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => new OrderDetailResource($order),
        ]);
    }

    /**
     * GET /api/orders/{id}/receipt
     * Download or stream the order receipt PDF.
     */
    public function receipt(Request $request, int $id)
    {
        $customer = $request->user('sanctum') ?? $request->user();
        $token = $request->input('token');
        $order = Order::with(['details.product', 'shippingAddress', 'payments'])->findOrFail($id);

        $accessService = app(RecipeAccessService::class);
        $isOwner = ($customer && $order->customer_id === $customer->id);
        $isValidToken = ($token && $accessService->verifyOrderToken($order, $token));

        if (! $isOwner && ! $isValidToken) {
            return response()->json(['status' => false, 'message' => 'Unauthorized to download this order receipt.'], 403);
        }

        $stream = $request->boolean('preview', false);
        $pdfService = app(RecipePdfService::class);

        return $pdfService->generateOrderReceiptPdf($order, $stream);
    }
}
