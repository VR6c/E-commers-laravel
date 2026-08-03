<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'status'  => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * Return a paginated success response.
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, mixed $transformedData = null, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $transformedData ?? $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], 200);
    }

    /**
     * Return an RFC 7807 Problem Details error response.
     */
    protected function problemResponse(
        string $type,
        string $title,
        int $status,
        string $detail,
        ?string $instance = null,
        array $errors = []
    ): JsonResponse {
        $payload = [
            'type'   => $type,
            'title'  => $title,
            'status' => $status,
            'detail' => $detail,
        ];

        if ($instance !== null) {
            $payload['instance'] = $instance;
        }

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status, [
            'Content-Type' => 'application/problem+json',
        ]);
    }
}
