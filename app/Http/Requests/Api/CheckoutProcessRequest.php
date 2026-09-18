<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'address'           => 'required|string|max:255',
            'suite'             => 'nullable|string|max:100',
            'city'              => 'required|string|max:100',
            'state'             => 'nullable|string|max:100',
            'country'           => 'required|string|max:100',
            'email'             => 'required|email|max:50',
            'phone'             => 'required|string|max:20',
            'gateway'           => 'required|string|in:abapayway,cod',
            'cart'              => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer|exists:products,id',
            'cart.*.quantity'   => 'required|integer|min:1',
            'cart.*.variant_id'  => 'nullable|integer',
        ];
    }

    /**
     * Handle a failed validation attempt for API consistency.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
