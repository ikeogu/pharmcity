<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hospital_id' => $this->hospital_id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'dob' => $this->dob->format('Y-m-d'),
            'age' => $this->age,
            'gender' => $this->gender,
            'additional_details' => $this->additional_details,

            // Registration
            'registration_id' => $this->registration_id,
            'service_location_id' => $this->service_location_id,
            'unit_id' => $this->unit_id,
            'consultant_id' => $this->consultant_id,
            'consultant' => $this->whenLoaded('consultant', function () {
                return [
                    'id' => $this->consultant->id,
                    'name' => $this->consultant->first_name . ' ' . $this->consultant->last_name,
                ];
            }),
            'patient_type' => $this->patient_type,
            'payment_party' => $this->payment_party,

            // Contact
            'permanent_address' => $this->permanent_address,
            'city' => $this->city,
            'state' => $this->state,
            'zipcode' => $this->zipcode,
            'nationality' => $this->nationality,
            'occupation' => $this->occupation,
            'marital_status' => $this->marital_status,
            'religion' => $this->religion,

            // Next of Kin
            'nok_full_name' => $this->nok_full_name,
            'nok_phone' => $this->nok_phone,
            'nok_address' => $this->nok_address,
            'nok_relationship' => $this->nok_relationship,
            'nok_occupation' => $this->nok_occupation,
            'nok_gender' => $this->nok_gender,

            // Status
            'status' => $this->status,
            'is_active' => $this->is_active,
            'last_seen' => $this->last_seen?->format('Y-m-d H:i:s'),
            'registered_at' => $this->registered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
