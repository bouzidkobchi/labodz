<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UnifiedAccessController extends Controller
{
    /**
     * Show the unified login page.
     */
    public function show()
    {
        // If already logged in as doctor
        if (Auth::guard('doctor')->check()) {
            return redirect()->route('doctor.dashboard');
        }

        // If already logged in as patient
        if (Session::has('patient_reservation_id')) {
            return redirect()->route('patient.dashboard');
        }

        return view('auth.unified-login');
    }

    /**
     * Handle authentication for both doctors and patients.
     */
    public function login(Request $request)
    {
        $type = $request->get('auth_type', 'patient');

        if ($type === 'doctor') {
            return $this->handleDoctorAuth($request);
        }

        return $this->handlePatientAuth($request);
    }

    /**
     * Doctor Authentication logic.
     */
    protected function handleDoctorAuth(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('doctor')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('doctor.dashboard'));
        }

        return back()->withErrors([
            'doctor_auth' => __('auth.failed'),
        ])->withInput($request->only('email'));
    }

    /**
     * Patient Access logic.
     */
    protected function handlePatientAuth(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'reservation_id' => 'required|string',
        ]);

        // Normalize Case Number (Strip #, V-, R-, spaces)
        $inputID = strtoupper(trim($request->reservation_id));
        $inputID = str_replace(['#', ' '], '', $inputID);
        $numericID = $inputID;

        if (preg_match('/^[VR]-?(\d+)$/', $inputID, $matches)) {
            $numericID = $matches[1];
        }

        // Normalize Phone (Strip spaces and non-digits)
        $inputPhone = preg_replace('/[^0-9]/', '', $request->phone);

        $reservation = Reservation::with('patient')
            ->where('id', $numericID)
            ->whereHas('patient', function ($q) use ($inputPhone) {
                // Check against normalized phone
                $q->where('phone', $inputPhone)
                  ->orWhereRaw("REPLACE(phone, ' ', '') = ?", [$inputPhone]);
            })
            ->first();

        if (!$reservation) {
            return back()->withErrors(['patient_auth' => __('messages.invalid_patient_credentials')])->withInput();
        }

        Session::put('patient_reservation_id', $reservation->id);
        Session::put('patient_phone', $reservation->patient->phone); // Store the canonical phone

        return redirect()->route('patient.dashboard');
    }
}
