<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cultunant = User::whereHas('roles', function($query){
            $query->where('name', 'consultant');
        })->first();

        if(!$cultunant){
            return;
        }
        $patients = [
            [
                'phone' => '+2348089894594',
                'title' => 'Mr',
                'first_name' => 'Kay',
                'last_name' => 'Intron',
                'dob' => '1985-05-15',
                'gender' => 'male',
                'patient_type' => 'NEW',
                'payment_party' => 'Self/General',
                'consultant_id' => $cultunant->id,
                'status' => 'pending',
                'registered_at' => now(),
            ],
            [
                'phone' => '+2347012345678',
                'email' => 'grace@example.com',
                'title' => 'Mrs',
                'first_name' => 'Grace',
                'last_name' => 'Uzo',
                'dob' => '1990-08-20',
                'gender' => 'female',
                'patient_type' => 'REVIEW',
                'payment_party' => 'HMO/NHIS',
                'consultant_id' => $cultunant->id,
                'status' => 'active',
                'last_seen' => now()->subDays(3),
                'registered_at' => now()->subDays(30),
            ],
        ];

        foreach ($patients as $patientData) {
            Patient::create($patientData);
        }
    }
}
