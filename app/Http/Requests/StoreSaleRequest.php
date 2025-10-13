<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Change to auth check if needed: auth()->check()
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Customer & Prescription
            'patient_id' => 'nullable|exists:patients,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',

            // Items array validation
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.batch_id' => 'nullable|exists:drug_batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',

            // Payment details
            'payment_method' => 'required|in:cash,card,transfer,insurance',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Customer & Prescription
            'customer_id.exists' => 'The selected customer does not exist.',
            'prescription_id.exists' => 'The selected prescription does not exist.',

            // Items
            'items.required' => 'At least one item is required for the sale.',
            'items.min' => 'At least one item is required for the sale.',
            'items.*.drug_id.required' => 'Drug selection is required for each item.',
            'items.*.drug_id.exists' => 'One or more selected drugs do not exist.',
            'items.*.batch_id.exists' => 'One or more selected batches do not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'items.*.discount.numeric' => 'Discount must be a valid number.',
            'items.*.discount.min' => 'Discount cannot be negative.',

            // Payment
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method. Must be: cash, card, transfer, or insurance.',
            'discount.numeric' => 'Discount must be a valid number.',
            'discount.min' => 'Discount cannot be negative.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'customer',
            'prescription_id' => 'prescription',
            'items.*.drug_id' => 'drug',
            'items.*.batch_id' => 'batch',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
            'items.*.discount' => 'item discount',
            'payment_method' => 'payment method',
            'discount' => 'sale discount',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up numeric values
        if ($this->has('discount')) {
            $this->merge([
                'discount' => $this->discount ? (float) $this->discount : 0,
            ]);
        }

        // Clean up items
        if ($this->has('items')) {
            $items = collect($this->items)->map(function ($item) {
                return [
                    'drug_id' => $item['drug_id'] ?? null,
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : null,
                    'unit_price' => isset($item['unit_price']) ? (float) $item['unit_price'] : null,
                    'discount' => isset($item['discount']) ? (float) $item['discount'] : 0,
                ];
            })->toArray();

            $this->merge(['items' => $items]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation: check if prescription is required for any item
            if ($this->has('items')) {
                foreach ($this->items as $index => $item) {
                    // You can add custom logic here if needed
                    // For example, verify that discount doesn't exceed item total
                    if (isset($item['discount']) && isset($item['unit_price']) && isset($item['quantity'])) {
                        $itemTotal = $item['unit_price'] * $item['quantity'];
                        if ($item['discount'] > $itemTotal) {
                            $validator->errors()->add(
                                "items.{$index}.discount",
                                'Item discount cannot exceed item total.'
                            );
                        }
                    }
                }
            }

            // Validate that overall discount doesn't exceed subtotal (if needed)
            // This is optional - remove if you allow over-discounting
            if ($this->has('discount') && $this->has('items')) {
                $subtotal = collect($this->items)->sum(function ($item) {
                    $unitPrice = $item['unit_price'] ?? 0;
                    $quantity = $item['quantity'] ?? 0;
                    $itemDiscount = $item['discount'] ?? 0;
                    return ($unitPrice * $quantity) - $itemDiscount;
                });

                if ($this->discount > $subtotal) {
                    $validator->errors()->add('discount', 'Sale discount cannot exceed the subtotal.');
                }
            }
        });
    }
}
