<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientService
{
    /**
     * Get paginated list of patients with filters
     */
    public function getAllPatients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Patient::with('consultant');

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Consultant filter
        if (!empty($filters['consultant_id'])) {
            $query->byConsultant($filters['consultant_id']);
        }

        // Date range filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('registered_at', [$filters['start_date'], $filters['end_date']]);
        }

        // Patient type filter
        if (!empty($filters['patient_type'])) {
            $query->where('patient_type', $filters['patient_type']);
        }

        // Payment party filter
        if (!empty($filters['payment_party'])) {
            $query->where('payment_party', $filters['payment_party']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get a single patient by ID
     */
    public function getPatientById(Patient $patient): ?Patient
    {
        return $patient->load('consultant');
    }

    /**
     * Get patient by hospital ID
     */
    public function getPatientByHospitalId(string $hospitalId): ?Patient
    {
        return Patient::with('consultant')->where('hospital_id', $hospitalId)->first();
    }

    /**
     * Create a new patient
     */
    public function createPatient(array $data): Patient
    {
        try {
            DB::beginTransaction();

            $patient = Patient::create($data);

            Log::info('Patient created successfully', [
                'patient_id' => $patient->id,
                'hospital_id' => $patient->hospital_id,
                'name' => $patient->full_name
            ]);

            DB::commit();

            return $patient->fresh(['consultant']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create patient', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing patient
     */
    public function updatePatient(int $id, array $data): Patient
    {
        try {
            DB::beginTransaction();

            $patient = Patient::findOrFail($id);
            $patient->update($data);

            Log::info('Patient updated successfully', [
                'patient_id' => $patient->id,
                'hospital_id' => $patient->hospital_id
            ]);

            DB::commit();

            return $patient->fresh(['consultant']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update patient', ['patient_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a patient (soft delete)
     */
    public function deletePatient(int $id): bool
    {
        try {
            DB::beginTransaction();

            $patient = Patient::findOrFail($id);
            $deleted = $patient->delete();

            Log::info('Patient deleted successfully', ['patient_id' => $id]);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete patient', ['patient_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted patient
     */
    public function restorePatient(int $id): bool
    {
        try {
            $patient = Patient::withTrashed()->findOrFail($id);
            $restored = $patient->restore();

            Log::info('Patient restored successfully', ['patient_id' => $id]);

            return $restored;
        } catch (\Exception $e) {
            Log::error('Failed to restore patient', ['patient_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update patient status
     */
    public function updateStatus(int $id, string $status): Patient
    {
        try {
            DB::beginTransaction();

            $patient = Patient::findOrFail($id);
            $patient->update(['status' => $status]);

            if ($status === 'active') {
                $patient->update(['last_seen' => now()]);
            }

            Log::info('Patient status updated', [
                'patient_id' => $id,
                'status' => $status
            ]);

            DB::commit();

            return $patient->fresh(['consultant']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update patient status', [
                'patient_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get patient statistics
     */
    public function getPatientStats(): array
    {
        return [
            'total_patients' => Patient::count(),
            'active_patients' => Patient::active()->count(),
            'pending_patients' => Patient::pending()->count(),
            'inactive_patients' => Patient::inactive()->count(),
            'new_today' => Patient::whereDate('created_at', today())->count(),
            'new_this_week' => Patient::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_this_month' => Patient::whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Search patients for autocomplete
     */
    public function searchPatients(string $query, int $limit = 20): Collection
    {
        return Patient::search($query)->limit($limit)->get();
    }

    /**
     * Batch import patients from Excel
     */
    public function batchImportPatients(array $patients): array
    {
        try {
            DB::beginTransaction();

            $results = [
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            foreach ($patients as $index => $patientData) {
                try {
                    Patient::create($patientData);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'data' => $patientData
                    ];
                }
            }

            Log::info('Batch import completed', $results);

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch import failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Export patients to Excel
     */
    public function exportPatients(array $filters = []): Collection
    {
        $query = Patient::with('consultant');

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('registered_at', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->get();
    }
}
