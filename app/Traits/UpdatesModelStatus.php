<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait UpdatesModelStatus
{
    /**
     * Validate and apply a status change to any Eloquent model.
     *
     * Expects the incoming request to carry:
     *   - id     (integer, must exist in the model's table)
     *   - status (boolean / 0|1)
     *
     * @param  class-string  $modelClass  Fully-qualified Eloquent model class.
     * @param  string  $statusField  Column name to update (default: 'status').
     * @param  string  $table  Table name used for the exists rule.
     *                         Defaults to the model's own table via ::getModel()->getTable().
     */
    public function performStatusUpdate(
        string $modelClass,
        Request $request,
        string $statusField = 'status',
        ?string $table = null
    ): JsonResponse {
        $table = $table ?? (new $modelClass)->getTable();

        $request->validate([
            'id' => "required|exists:{$table},id",
            'status' => 'required|boolean',
        ]);

        $modelClass::whereKey($request->id)->update([$statusField => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }
}
