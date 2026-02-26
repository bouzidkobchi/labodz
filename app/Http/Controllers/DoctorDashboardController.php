<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorDashboardController extends Controller
{
    /**
     * Display the doctor dashboard with patient summaries.
     */
    public function dashboard()
    {
        $doctor = Auth::guard('doctor')->user();

        // Get reservations linked to this doctor
        $query = Reservation::with(['patient', 'reservationAnalyses.analyse'])
            ->where('doctor_id', $doctor->id);

        $stats = [
            'total_patients' => (clone $query)->distinct('patient_id')->count(),
            'pending_results' => (clone $query)->where('status', '!=', 'completed')->where('status', '!=', 'ready')->count(),
            'ready_results' => (clone $query)->whereIn('status', ['ready', 'completed'])->count(),
        ];

        $reservations = $query->orderBy('analysis_date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(15);

        return view('doctor.dashboard', compact('stats', 'reservations', 'doctor'));
    }

    /**
     * View detailed results for a specific patient referal.
     */
    public function showPatientResult($id)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse'])
            ->where('doctor_id', $doctor->id)
            ->findOrFail($id);

        return view('doctor.patient-result', compact('reservation'));
    }

    /**
     * Download result file for doctor.
     */
    public function downloadResult($id)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $reservation = Reservation::where('doctor_id', $doctor->id)
            ->findOrFail($id);

        if (!$reservation->result_file_path || !\Storage::disk('public')->exists($reservation->result_file_path)) {
            return back()->with('error', __('messages.result_not_found'));
        }

        return \Storage::disk('public')->download($reservation->result_file_path);
    }
}
