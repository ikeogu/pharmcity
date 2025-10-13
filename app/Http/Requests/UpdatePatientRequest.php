<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patientId = $this->route('patient');
        
        return [
             'hospital_id' => ['sometimes', 'exists:hospitals,id'],
            'phone' => ['sometimes', 'string', 'max:20', "unique:patients,phone,{$patientId}"],
            'email' => ['nullable', 'email', "unique:patients,email,{$patientId}"],
            'title' => ['nullable', 'string', 'max:20'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'gender' => ['sometimes', 'in:male,female,other'],
            'additional_details' => ['nullable', 'string'],
            'registration_id' => ['nullable', 'string', 'max:50', "unique:patients,registration_id,{$patientId}"],
            'service_location_id' => ['nullable', 'exists:service_locations,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'consultant_id' => ['nullable', 'exists:users,id'],
            'patient_type' => ['nullable', 'in:inpatient,outpatient'],
            'payment_party' => ['nullable', 'string', 'max:100'],
            'permanent_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zipcode' => ['nullable', 'string', 'max:20'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'religion' => ['nullable', 'string', 'max:100'],

            // NOK
            'nok_full_name' => ['nullable', 'string', 'max:150'],
            'nok_phone' => ['nullable', 'string', 'max:20'],
            'nok_address' => ['nullable', 'string', 'max:255'],
            'nok_relationship' => ['nullable', 'string', 'max:100'],
            'nok_occupation' => ['nullable', 'string', 'max:100'],
            'nok_gender' => ['nullable', 'in:male,female,other'],

            'status' => ['nullable', 'in:active,inactive,deceased'],
            'last_seen' => ['nullable', 'date'],
            'registered_at' => ['nullable', 'date'],
        ];
    }
}
