<?php

namespace App\Imports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PatientsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Patient([
            'phone' => $row['phone'],
            'email' => $row['email'] ?? null,
            'title' => $row['title'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'],
            'dob' => $row['dob'],
            'gender' => $row['gender'],
            'patient_type' => $row['patient_type'],
            'payment_party' => $row['payment_party'],
            'permanent_address' => $row['permanent_address'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'zipcode' => $row['zipcode'] ?? null,
            'nationality' => $row['nationality'] ?? null,
            'occupation' => $row['occupation'] ?? null,
            'marital_status' => $row['marital_status'] ?? null,
            'religion' => $row['religion'] ?? null,
            'nok_full_name' => $row['nok_full_name'] ?? null,
            'nok_phone' => $row['nok_phone'] ?? null,
            'nok_address' => $row['nok_address'] ?? null,
            'nok_relationship' => $row['nok_relationship'] ?? null,
        ]);
    }

    /**
     * Validation rules for import
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string',
            'title' => 'required|in:Mr,Mrs,Miss,Dr,Prof,Chief,Alhaji,Alhaja',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female',
            'patient_type' => 'required|in:NEW,REVIEW,EMERGENCY',
            'payment_party' => 'required|in:Self/General,HMO/NHIS,Employer',
        ];
    }

}
