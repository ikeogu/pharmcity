<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class PatientsExport implements FromCollection, WithHeadings, WithMapping
{

    public function __construct(protected  $patients) {}

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->patients;
    }

    /**
     * Map data for each row
     */
    public function map($patient): array
    {
        return [
            $patient->id,
            $patient->hospital_id,
            $patient->full_name,
            $patient->phone,
            $patient->email,
            $patient->gender,
            $patient->age,
            $patient->patient_type,
            $patient->payment_party,
            $patient->consultant?->first_name . ' ' . $patient->consultant?->last_name,
            $patient->status,
            $patient->last_seen?->format('Y-m-d'),
            $patient->registered_at?->format('Y-m-d'),
        ];
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            '#',
            'Patient ID',
            'Name',
            'Phone',
            'Email',
            'Gender',
            'Age',
            'Patient Type',
            'Payment Party',
            'Consultant',
            'Status',
            'Last Seen',
            'Registered At',
        ];
    }
}
