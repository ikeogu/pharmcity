<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDrugRequest extends FormRequest
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
            'drug_name' => ['sometimes', 'required', 'string', 'max:255'],
            'generic_name' => ['sometimes', 'required', 'string', 'max:255'],
            'drug_group_class' => ['sometimes', 'required', 'string', 'max:255'],
            'drug_dose' => ['sometimes', 'required', 'string', 'max:255'],
            'drug_company' => ['nullable', 'string', 'max:255'],
            'drug_country' => ['nullable', 'string', 'max:255'],
            
            'dose_unit' => ['sometimes', 'required', 'string'],
            'drug_route' => ['sometimes', 'required', 'string', ],
            'min_daily_dose' => ['nullable', 'numeric', 'min:0'],
            'max_daily_dose' => ['nullable', 'numeric', 'min:0', 'gte:min_daily_dose'],
            
            'purchase_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['sometimes', 'required', 'date', 'after:today'],
            'total_sachets_in_stock' => ['sometimes', 'required', 'integer', 'min:0'],
            
            'packaging' => ['sometimes', 'required', 'string'],
            'form_of_items_in_package' => ['sometimes', 'required', 'string', ],
            'cards_per_sachet' => ['sometimes', 'required', 'integer', 'min:1'],
            'sell_the_drug_as' => ['sometimes', 'required', 'string', ],
            'price_per_sachet' => ['sometimes', 'required', 'numeric', 'min:0'],
            
            'drug_description' => ['sometimes', 'required', 'string'],
            'drug_composition' => ['nullable', 'string'],
        ];
    }

     public function messages(): array
    {
        return [
            'drug_name.required' => 'Drug name is required',
            'generic_name.required' => 'Generic name is required',
            'drug_group_class.required' => 'Drug group/class is required',
            'drug_dose.required' => 'Drug dose is required',
            'dose_unit.required' => 'Dose unit is required',
            'drug_route.required' => 'Drug route is required',
            'expiry_date.required' => 'Expiry date is required',
            'expiry_date.after' => 'Expiry date must be in the future',
            'max_daily_dose.gte' => 'Maximum daily dose must be greater than or equal to minimum daily dose',
            'purchase_price.required' => 'Purchase price is required',
            'total_sachets_in_stock.required' => 'Stock quantity is required',
            'packaging.required' => 'Packaging type is required',
            'form_of_items_in_package.required' => 'Form of items in package is required',
            'cards_per_sachet.required' => 'Cards per sachet is required',
            'sell_the_drug_as.required' => 'Selling unit is required',
            'price_per_sachet.required' => 'Price per sachet is required',
            'drug_description.required' => 'Drug description is required',
        ];
    }
}
