<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Option;
use App\Models\Patient;
use App\Models\PatientAnswer;
use App\Models\Question;
use App\Models\Reminder;
use App\Models\Request_reservation;
use App\Models\Reservation;
use App\Models\ReservationAnalysis;
use App\Services\AnalysisEligibilityService;
use Illuminate\Http\Request;

class reservationsController extends Controller
{
    public function reservations(Request $request)
    {
        // Start the query, eager loading patient and analyses
        $query = Reservation::with(['patient', 'reservationAnalyses.analyse']);

        // Apply date filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('analysis_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('analysis_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('analysis_date', '<=', $request->end_date);
        }

        // Filter by booking status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search for patient by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Fetch booked reservations, newest first
        $bookings = $query->orderByDesc('analysis_date')
            ->orderByDesc('time')
            ->paginate(10);

        // Add Professional QR Codes to the collection (Null-safe)
        foreach ($bookings as $booking) {
            $patientName = $booking->patient->name ?? $booking->name ?? 'Patient';
            $patientPhone = $booking->patient->phone ?? $booking->phone ?? 'N/A';
            $qrData = "VIST-" . $booking->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
            $booking->barcode = \App\Helpers\BarcodeHelper::getQRUrl($qrData, '150x150');
        }

        return view('Adminstration.reservations', [
            'bookings' => $bookings,
        ]);
    }

    public function filterReservations(Request $request)
    {
        // This method handles the filter form submission
        // It redirects back to reservations with query parameters
        return $this->reservations($request);
    }

    // Add method to update booking status
    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:booked,ready,blocked,warning,pending_approval,completed',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => $request->status]);

        return redirect()->route('reservations')->with('success', 'تم تحديث حالة الحجز بنجاح');
    }

    // Show reservation requests
    public function reservationRequests(Request $request)
    {
        $query = Request_reservation::with(['analyse']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(10);

        return view('Adminstration.reservation-requests', [
            'requests' => $requests,
        ]);
    }

    // Confirm reservation request
    public function confirmRequest(Request $request, $id)
    {
        $reservationRequest = Request_reservation::with('analyses')->findOrFail($id);

        if ($reservationRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'هذا الطلب تمت معالجته بالفعل');
        }

        $request->validate([
            'analysis_date' => 'required|date',
            'time' => 'required',
            'admin_notes' => 'nullable|string',
        ]);

        try {
            // Find or Create patient record
            $patient = null;
            if ($reservationRequest->patient_id) {
                $patient = Patient::find($reservationRequest->patient_id);
            }

            if (! $patient) {
                $patient = Patient::where('phone', $reservationRequest->phone)->first();
            }

            if (! $patient) {
                $patient = Patient::create([
                    'name' => $reservationRequest->name,
                    'email' => $reservationRequest->email,
                    'phone' => $reservationRequest->phone,
                    'gender' => $reservationRequest->gender,
                    'birth_date' => $reservationRequest->birth_date,
                ]);
            } else {
                // Update existing patient info if it was missing
                $patient->update(array_filter([
                    'email' => $patient->email ?: $reservationRequest->email,
                    'gender' => $patient->gender ?: $reservationRequest->gender,
                    'birth_date' => $patient->birth_date ?: $reservationRequest->birth_date,
                ]));
            }

            // Create ONE parent reservation
            $reservation = Reservation::create([
                'patient_id' => $patient->id,
                'analysis_date' => $request->analysis_date,
                'time' => $request->time,
                'status' => 'booked',
                'prescription_path' => $reservationRequest->prescription_path,
            ]);

            // Determine which analyses to add (pivot vs single column)
            $analyses = $reservationRequest->analyses;
            if ($analyses->isEmpty() && $reservationRequest->analyse_id) {
                $analyses = collect([$reservationRequest->analyse]);
            }

            // Create linked reservation analyses
            foreach ($analyses as $analyse) {
                if (! $analyse) {
                    continue;
                }

                $resAnalysis = ReservationAnalysis::create([
                    'reservation_id' => $reservation->id,
                    'analysis_id' => $analyse->id,
                    'status' => 'booked',
                ]);

                // Also create a history record as it might be used for medical records/results
                $history = History::create([
                    'patient_id' => $patient->id,
                    'analyse_id' => $analyse->id,
                    'analysis_date' => $request->analysis_date,
                    'time' => $request->time,
                    'status' => 'booked',
                    'result' => null,
                ]);

                // Create reminder for the reservation analysis
                Reminder::create([
                    'history_id' => $history->id,
                    'reservation_id' => $reservation->id,
                    'patient_id' => $patient->id,
                    'analyse_id' => $analyse->id,
                    'scheduled_for' => \Carbon\Carbon::parse($request->analysis_date)->subDay(),
                    'is_sent' => false,
                ]);
            }

            // Update reservation request
            $reservationRequest->update([
                'status' => 'confirmed',
                'patient_id' => $patient->id,
                'reservation_id' => $reservation->id,
                'admin_notes' => $request->admin_notes,
            ]);

            return redirect()->route('reservation.requests')->with('success', 'تم تأكيد الطلب وإنشاء الحجز بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تأكيد الطلب: '.$e->getMessage());
        }
    }

    // Reject reservation request
    public function rejectRequest(Request $request, $id)
    {
        $reservationRequest = Request_reservation::findOrFail($id);

        if ($reservationRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'هذا الطلب تمت معالجته بالفعل');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $reservationRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('reservation.requests')->with('success', 'تم رفض الطلب بنجاح');
    }

    /**
     * Check eligibility during the execution phase.
     */
    public function checkExecutionEligibility(AnalysisEligibilityService $eligibilityService, $id)
    {
        $resAnalysis = ReservationAnalysis::findOrFail($id);

        // Call the eligibility service
        $result = $eligibilityService->checkEligibility($resAnalysis->reservation->patient_id, $resAnalysis->analysis_id);

        $statusMap = [
            'block' => 'blocked',
            'warning' => 'warning',
            'approval' => 'pending_approval',
            'eligible' => 'ready',
        ];

        $newStatus = $statusMap[$result['status']] ?? 'ready';

        $resAnalysis->update(['status' => $newStatus]);

        return response()->json([
            'status' => $newStatus,
            'reason' => $result['reason'] ?? null,
            'original_action' => $result['status'],
        ]);
    }

    /**
     * Show the eligibility check form for a specific reservation analysis.
     */
    public function showEligibilityCheck($id)
    {
        $resAnalysis = ReservationAnalysis::with(['reservation.patient', 'analyse.questions.options'])->findOrFail($id);

        $questions = Question::where('analyse_id', $resAnalysis->analysis_id)->with('options')->get();

    // Generate Professional QR Code (Base64 for reliability, Null-safe)
    $patientName = $resAnalysis->reservation->patient->name ?? $resAnalysis->reservation->name ?? 'Patient';
    $patientPhone = $resAnalysis->reservation->patient->phone ?? $resAnalysis->reservation->phone ?? 'N/A';
    $qrData = "VIST-" . $resAnalysis->reservation->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
    $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');

    return view('Adminstration.eligibility-check', [
        'booking' => $resAnalysis,
        'questions' => $questions,
        'barcode' => $barcode
    ]);
    }

    /**
     * Process the eligibility check answers.
     */
    public function submitEligibilityCheck(Request $request, AnalysisEligibilityService $eligibilityService, $id)
    {
        $resAnalysis = ReservationAnalysis::findOrFail($id);

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'exists:options,id',
        ]);

        // 1. Save answers
        foreach ($request->answers as $questionId => $optionId) {
            PatientAnswer::updateOrCreate(
                [
                    'patient_id' => $resAnalysis->reservation->patient_id,
                    'question_id' => $questionId,
                ],
                ['option_id' => $optionId]
            );
        }

        // 2. Call the eligibility service
        $result = $this->checkExecutionEligibility($eligibilityService, $id);
        $data = json_decode($result->getContent(), true);

        return redirect()->route('reservations')->with('success', 'تم تقييم الأهلية بنجاح. الحالة الحالية: '.$data['status']);
    }

    /**
     * Show the combined eligibility check form for all analyses in a reservation.
     */
    public function showFullEligibilityCheck($id)
    {
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse.questions.options'])->findOrFail($id);

        // Generate Professional QR Code (Base64 for reliability, Null-safe)
        $patientName = $reservation->patient->name ?? $reservation->name ?? 'Patient';
        $patientPhone = $reservation->patient->phone ?? $reservation->phone ?? 'N/A';
        $qrData = "VIST-" . $reservation->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
        $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');

        return view('Adminstration.eligibility-check', [
            'reservation' => $reservation,
            'patient' => $reservation->patient,
            'barcode' => $barcode,
        ]);
    }

    /**
     * Process the eligibility check answers for all analyses in a reservation.
     */
    public function submitFullEligibilityCheck(Request $request, AnalysisEligibilityService $eligibilityService, $id)
    {
        \Log::info("Starting full eligibility check for reservation: $id");
        $reservation = Reservation::with('reservationAnalyses.analyse')->findOrFail($id);

        $request->validate([
            'answers' => 'required|array',
        ]);

        // 1. Save answers for the patient
        foreach ($request->answers as $questionId => $optionData) {
            $optionIds = is_array($optionData) ? $optionData : [$optionData];

            // Remove old answers for this question to support multi-select sync
            PatientAnswer::where('patient_id', $reservation->patient_id)
                ->where('question_id', $questionId)
                ->delete();

            foreach ($optionIds as $optionId) {
                if (! $optionId) {
                    continue;
                }

                PatientAnswer::create([
                    'patient_id' => $reservation->patient_id,
                    'question_id' => $questionId,
                    'option_id' => $optionId,
                ]);
            }

            // Auto-set sub-questions if parent is 'NO' (e.g., Medication -> Diabetes Medication)
            $optionId = is_array($optionData) ? ($optionData[0] ?? null) : $optionData;
            if ($optionId) {
                $option = Option::find($optionId);
                if ($option && $option->value === 'NO') {
                    $subQs = Question::where('parent_question_id', $questionId)->get();
                    foreach ($subQs as $subQ) {
                        $noOption = Option::where('question_id', $subQ->id)->where('value', 'NO')->first();
                        if ($noOption) {
                            PatientAnswer::updateOrCreate(
                                ['patient_id' => $reservation->patient_id, 'question_id' => $subQ->id],
                                ['option_id' => $noOption->id]
                            );
                        }
                    }
                }
            }
        }

        // 2. Run eligibility check for each analysis in the reservation
        $statusMap = [
            'block' => 'INVALID',
            'warning' => 'VALID_WITH_NOTE',
            'eligible' => 'READY',
        ];

        $viewStatusMap = [
            'INVALID' => 'blocked',
            'VALID_WITH_NOTE' => 'warning',
            'READY' => 'ready',
        ];

        $results = [];
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            try {
                $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->analysis_id);

                $jsonStatus = $statusMap[$checkResult['status']] ?? 'READY';
                $viewStatus = $viewStatusMap[$jsonStatus] ?? 'ready';

                // Update database status
                $resAnalysis->update(['status' => $viewStatus]);

                $results[] = [
                    'analysis_id' => $resAnalysis->id,
                    'name' => $resAnalysis->analyse ? $resAnalysis->analyse->name : "Analysis #{$resAnalysis->analysis_id}",
                    'status' => $viewStatus, // for frontend UI
                    'fasting_valid' => ($jsonStatus !== 'INVALID'),
                    'eligibility_status' => $jsonStatus, // for user requested JSON
                    'notes' => $checkResult['notes'] ?? [],
                    'action' => $checkResult['status'],
                ];
            } catch (\Exception $e) {
                \Log::error("Error checking eligibility for analysis {$resAnalysis->analysis_id}: " . $e->getMessage());
                $results[] = [
                    'analysis_id' => $resAnalysis->id,
                    'name' => $resAnalysis->analyse ? $resAnalysis->analyse->name : "Analysis #{$resAnalysis->analysis_id}",
                    'status' => 'ready',
                    'notes' => ["خطأ داخلي أثناء التقييم: " . $e->getMessage()],
                    'action' => 'eligible',
                ];
            }
        }

        \Log::info("Eligibility check completed for reservation: $id. Found " . count($results) . " results.");

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة أهلية جميع التحاليل بنجاح.',
                'results' => $results,
            ]);
        }

        return redirect()->route('reservations')->with('success', 'تم تحديث حالة أهلية جميع التحاليل بنجاح.');
    }

    /**
     * Update the status of a specific reservation analysis.
     */
    public function updateAnalysisStatus(Request $request, $id)
    {
        $resAnalysis = ReservationAnalysis::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:booked,ready,blocked,warning,pending_approval,completed',
        ]);

        $resAnalysis->update(['status' => $request->status]);

        // If it's AJAX, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $resAnalysis->status,
                'message' => 'تم تحديث حالة التحليل بنجاح.',
            ]);
        }

        return back()->with('success', 'تم تحديث حالة التحليل بنجاح.');
    }

    /**
     * Show detailed eligibility results for a reservation.
     */
    public function showEligibilityResults(AnalysisEligibilityService $eligibilityService, $id)
    {
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse'])->findOrFail($id);

        $results = [];
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->analysis_id);
            $results[] = [
                'name' => $resAnalysis->analyse->name,
                'status' => $checkResult['status'],
                'notes' => $checkResult['notes'] ?? []
            ];
        }

        // Get all questions relevant to this reservation's analyses
        $analyseIds = $reservation->reservationAnalyses->pluck('analysis_id');
        
        // Get patient answers related to these analyses
        $patientAnswers = PatientAnswer::with(['question', 'option'])
            ->where('patient_id', $reservation->patient_id)
            ->whereHas('question', function($query) use ($analyseIds) {
                $query->whereIn('analyse_id', $analyseIds);
            })
            ->get()
            ->groupBy('question_id');

    // Generate Professional QR Code (Base64 for reliability, Null-safe)
    $patientName = $reservation->patient->name ?? $reservation->name ?? 'Patient';
    $patientPhone = $reservation->patient->phone ?? $reservation->phone ?? 'N/A';
    $qrData = "VIST-" . $reservation->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
    $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');

    return view('Adminstration.eligibility-results', compact('reservation', 'results', 'patientAnswers', 'barcode'));
    }

    /**
     * Show a printable diagnostic report for a reservation.
     */
    public function printEligibilityReport(AnalysisEligibilityService $eligibilityService, $id)
    {
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse'])->findOrFail($id);

        $results = [];
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->analysis_id);
            $results[] = [
                'name' => $resAnalysis->analyse->name,
                'status' => $checkResult['status'],
                'notes' => $checkResult['notes'] ?? []
            ];
        }

        $analyseIds = $reservation->reservationAnalyses->pluck('analysis_id');
        
        $patientAnswers = PatientAnswer::with(['question', 'option'])
            ->where('patient_id', $reservation->patient_id)
            ->whereHas('question', function($query) use ($analyseIds) {
                $query->whereIn('analyse_id', $analyseIds);
            })
            ->get()
            ->groupBy('question_id');

    // Generate Professional QR Code (Base64 for reliability, Null-safe)
    $patientName = $reservation->patient->name ?? $reservation->name ?? 'Patient';
    $patientPhone = $reservation->patient->phone ?? $reservation->phone ?? 'N/A';
    $qrData = "VIST-" . $reservation->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
    $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');

    return view('Adminstration.eligibility-report', compact('reservation', 'results', 'patientAnswers', 'barcode'));
    }
}
