<?php

namespace App\Http\Controllers;

use App\Exports\PatientsExport;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Imports\PatientsImport;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PatientController extends Controller
{
    public function __construct(
        public readonly PatientService $patientService
    ) {}

    /**
     * Display a listing of the patients.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
            'consultant_id',
            'start_date',
            'end_date',
            'patient_type',
            'payment_party',
            'sort_by',
            'sort_order'
        ]);

        $patients = $this->patientService->getAllPatients($filters, perPage: 15);

        return Inertia::render('Patients/Index', [
            'patients' => PatientResource::collection($patients),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        $consultants = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['consultant', 'doctor']);

        })->get();
    
        return Inertia::render('Patients/Create',[
            'consultants' => UserResource::collection($consultants)->resolve()
        ]);
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(StorePatientRequest $request)
    {
        $patient = $this->patientService->createPatient($request->validated());

        return redirect()
            ->route('patients.index')
            ->with('success', "Patient {$patient->full_name} created successfully.");
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        $patient = $this->patientService->getPatientById($patient);

        return Inertia::render('Patients/Show', [
            'patient' => new PatientResource($patient),
        ]);
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        return Inertia::render('Patients/Edit', [
            'patient' => new PatientResource($patient->load('consultant')),
        ]);
    }

    /**
     * Update the specified patient in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $updated = $this->patientService->updatePatient($patient->id, $request->validated());

        return redirect()
            ->route('patients.index')
            ->with('success', "Patient {$updated->full_name} updated successfully.");
    }

    /**
     * Remove the specified patient from storage (soft delete).
     */
    public function destroy(Patient $patient)
    {
        $this->patientService->deletePatient($patient->id);

        return redirect()
            ->route('patients.index')
            ->with('success', "Patient {$patient->full_name} deleted successfully.");
    }

    /**
     * Restore a soft-deleted patient.
     */
    public function restore($id)
    {
        $this->patientService->restorePatient($id);

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient restored successfully.');
    }

    /**
     * Update the status of a patient (active/inactive/pending).
     */
    public function updateStatus(Patient $patient, Request $request)
    {
        $request->validate(['status' => 'required|string|in:active,inactive,pending']);

        $updated = $this->patientService->updateStatus($patient->id, $request->status);

        return back()->with('success', "Patient {$updated->full_name} status updated to {$updated->status}.");
    }


    /**
     * Display patient statistics
     */
    public function stats()
    {
        $stats = $this->patientService->getPatientStats();

        return Inertia::render('Patients/Stats', [
            'stats' => $stats
        ]);
    }

    /**
     * Search patients (for autocomplete)
     */
    public function search(Request $request)
    {
        $request->validate(['query' => 'required|string']);

        $patients = $this->patientService->searchPatients($request->query(), $request->get('limit', 20));

        return response()->json(PatientResource::collection($patients));
    }

    /**
     * Batch import patients from Excel
     */
    public function batchImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv'
        ]);

        $path = $request->file('file')->store('temp');

        $importedData = Excel::toArray(new PatientsImport, $path)[0];

        $result = $this->patientService->batchImportPatients($importedData);

        Storage::delete($path);

        return redirect()
            ->back()
            ->with('success', "{$result['success']} patients imported successfully. {$result['failed']} failed.");
    }

    /**
     * Export patients to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['status', 'start_date', 'end_date']);

        $patients = $this->patientService->exportPatients($filters);

        return Excel::download(new PatientsExport($patients), 'patients.xlsx');
    }
}
