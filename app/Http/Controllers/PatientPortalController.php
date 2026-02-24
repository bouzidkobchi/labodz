<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PatientPortalController extends Controller
{
    /**
     * Show the login form for patients.
     */
    public function showLogin()
    {
        if (Session::has('patient_reservation_id')) {
            return redirect()->route('patient.dashboard');
        }
        return view('patient-portal.login');
    }

    /**
     * Handle patient access request.
     */
    public function access(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'reservation_id' => 'required|integer',
        ]);

        $reservation = Reservation::with('patient')
            ->where('id', $request->reservation_id)
            ->whereHas('patient', function ($q) use ($request) {
                $q->where('phone', $request->phone);
            })
            ->first();

        if (!$reservation) {
            return back()->withErrors(['access' => __('messages.invalid_patient_credentials')])->withInput();
        }

        Session::put('patient_reservation_id', $reservation->id);
        Session::put('patient_phone', $request->phone);

        return redirect()->route('patient.dashboard');
    }

    /**
     * Display the patient dashboard.
     */
    public function dashboard()
    {
        $reservationId = Session::get('patient_reservation_id');
        $phone = Session::get('patient_phone');

        if (!$reservationId || !$phone) {
            return redirect()->route('patient.login');
        }

        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse', 'reminders'])
            ->where('id', $reservationId)
            ->firstOrFail();

        // Safety check
        if ($reservation->patient->phone !== $phone) {
            Session::forget(['patient_reservation_id', 'patient_phone']);
            return redirect()->route('patient.login');
        }

        return view('patient-portal.dashboard', compact('reservation'));
    }

    /**
     * Logout from the patient portal.
     */
    public function logout()
    {
        Session::forget(['patient_reservation_id', 'patient_phone']);
        return redirect()->route('patient.login');
    }

    /**
     * Download the result file securely.
     */
    public function downloadResult($id)
    {
        $reservationId = Session::get('patient_reservation_id');
        
        if ($reservationId != $id) {
            abort(403);
        }

        $reservation = Reservation::findOrFail($id);

        if (!$reservation->result_file_path || !\Storage::disk('public')->exists($reservation->result_file_path)) {
            return back()->with('error', __('messages.result_not_found'));
        }

        return \Storage::disk('public')->download($reservation->result_file_path);
    }
}
