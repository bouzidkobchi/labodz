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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // Filter by first letter of patient name
        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->whereHas('patient', function ($q) use ($letter) {
                if ($letter === '#') {
                    // Filter for names starting with non-alphabetical characters
                    $q->where('name', 'regexp', '^[^a-zA-Z]');
                } else {
                    $q->where('name', 'like', "$letter%");
                }
            });
        }

        // Search for patient by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Apply dynamic sorting
        $sortBy = $request->get('sort_by', 'analysis_date');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Define allowable sort fields
        $allowedSorts = [
            'name' => 'patient.name', // Will be handled differently for joins
            'analysis_date' => 'analysis_date',
            'time' => 'time',
            'status' => 'status'
        ];

        if ($sortBy === 'name') {
            // Join patient table for sorting by name
            $query->join('patients', 'reservations.patient_id', '=', 'patients.id')
                  ->select('reservations.*')
                  ->orderBy('patients.name', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
            
            // Secondary sort for consistency if sorting by date
            if ($sortBy === 'analysis_date') {
                $query->orderBy('time', $sortOrder);
            }
        }

        // Fetch booked reservations, paginated
        $bookings = $query->paginate(15);

        // Add Professional QR Codes to the collection (Null-safe)
        foreach ($bookings as $booking) {
            $patientName = $booking->patient->name ?? $booking->name ?? 'Patient';
            $patientPhone = $booking->patient->phone ?? $booking->phone ?? 'N/A';
            $qrData = "VIST-" . $booking->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
            $booking->barcode = \App\Helpers\BarcodeHelper::getQRUrl($qrData, '150x150');
        }

        return view('Adminstration.reservations', [
            'bookings' => $bookings,
            'doctors' => \App\Models\Doctor::all(),
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
            'analysis_date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'admin_notes' => 'nullable|string',
        ], [
            'analysis_date.after_or_equal' => __('messages.invalid_date_past'),
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

            // Base update for reservation request (will be overridden if < 8h + fasting)
            $reservationRequest->update([
                'status' => 'confirmed',
                'patient_id' => $patient->id,
                'reservation_id' => $reservation->id,
                'admin_notes' => $request->admin_notes,
            ]);

            // Integrated Smart Reminder Logic (Algeria Timezone: Africa/Algiers)
            $patientEmail = $patient->email ?: $reservationRequest->email;
            if ($patientEmail) {
                try {
                    $now = \Carbon\Carbon::now('Africa/Algiers');
                    $appointmentDateTime = \Carbon\Carbon::parse(
                        $request->analysis_date . ' ' . ($request->time ?? '09:00'),
                        'Africa/Algiers'
                    );
                    
                    // High-Precision: Calculate float hours remaining (accounts for multiple days and exact time)
                    $hoursRemaining = $now->floatDiffInHours($appointmentDateTime, false);
                    
                    $requiresFasting = $analyses->contains(function($a) {
                        return $a && str_contains($a->preparation_instructions, 'صيام');
                    });

                    $smartMessage = null;

                    // 1. If < 8h AND requires fasting -> Reschedule Warning
                    if ($hoursRemaining < 8 && $requiresFasting) {
                        // Mark as rejected since there isn't enough time to fast
                        $reservationRequest->update(['status' => 'rejected']);
                        
                        $fastingAnalyses = $analyses->filter(fn($a) => $a && str_contains($a->preparation_instructions, 'صيام'));
                        Mail::send('emails.booking-too-close', [
                            'patientName' => $reservationRequest->name,
                            'fastingAnalyses' => $fastingAnalyses,
                            'analysisDate' => \Carbon\Carbon::parse($request->analysis_date)->format('d/m/Y'),
                            'analysisTime' => $request->time,
                        ], function ($message) use ($patientEmail) {
                            $message->to($patientEmail)
                                    ->subject('تنبيه: يجب إعادة جدولة موعد تحاليلكم - labo.dz');
                        });
                        
                        Log::info('Appointment too close for fasting (' . round($hoursRemaining, 2) . 'h). Marked as rejected for: ' . $patientEmail);
                        return redirect()->route('reservation.requests')->with('error', 'تم رفض الطلب تلقائياً لعدم كفاية الوقت للصيام (أقل من 8 ساعات)');
                    } 
                    
                    // 2. If between 8h and 14h AND requires fasting -> Send immediate Reminder
                    elseif ($hoursRemaining >= 8 && $hoursRemaining < 14 && $requiresFasting) {
                        $fullHours = floor($hoursRemaining);
                        $remainingMins = round(($hoursRemaining - $fullHours) * 60);
                        $smartMessage = "تذكير: موعد التحليل يقترب! متبقي " . $fullHours . " ساعة و " . $remainingMins . " دقيقة. يرجى البدء في الصيام الآن لضمان دقة النتائج.";
                        $this->sendImmediateReminder($patient, $analyses, $request, $reservationRequest, $smartMessage);
                    }
                    
                    // 3. If more than 14h AND requires fasting -> Preparation instruction
                    elseif ($hoursRemaining >= 14 && $requiresFasting) {
                        $smartMessage = "ملاحظة: هذا التحليل يتطلب الصيام لمدة 14 ساعة قبل الموعد. يرجى البدء في الصيام عند اقتراب الموعد بـ 14 ساعة لضمان النتائج.";
                        $this->sendImmediateReminder($patient, $analyses, $request, $reservationRequest, $smartMessage);
                    }
                    
                    // 4. All other cases (Non-fasting) -> Standard Confirmation
                    else {
                        $this->sendImmediateReminder($patient, $analyses, $request, $reservationRequest);
                    }

                } catch (\Exception $remException) {
                    Log::error('Smart Reminder Logic Error: ' . $remException->getMessage());
                }
            }

            return redirect()->route('reservation.requests')->with('success', 'تم تأكيد الطلب وإرسال الإشعارات بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تأكيد الطلب: '.$e->getMessage());
        }
    }

    /**
     * Helper to send the standard confirmation email with PDF attachment
     */
    private function sendImmediateReminder($patient, $analyses, $request, $reservationRequest, $smartMessage = null)
    {
        $patientEmail = $patient->email ?: $reservationRequest->email;
        if (!$patientEmail) return;

        try {
            // Reload reservation request with analyses for PDF generation
            $reservationRequest->load('analyses');

            // Build translation maps
            $analysisTranslations = [
                'تحليل الدم الشامل' => 'Numération Formule Sanguine (NFS)',
                'تحليل السكر في الدم' => 'Glycémie à jeun',
                'تحليل الكوليسترول والدهون' => 'Bilan Lipidique',
                'تحليل وظائف الكبد' => 'Bilan Hépatique',
                'تحليل وظائف الكلى' => 'Bilan Rénal',
                'تحليل البول الكامل' => 'Examen des Urines (ECBU)',
                'فصيلة الدم' => 'Groupage Sanguin',
                'سرعة الترسيب' => 'Vitesse de Sédimentation (VS)',
                'تحليل وظائف الغدة الدرقية' => 'Bilan Thyroïdien',
                'تحليل فيتامين د' => 'Vitamine D',
            ];

            $instructionTranslations = [
                'لا يتطلب صيام. يمكن إجراء التحليل في أي وقت.' => 'À jeun non requis. Peut être effectué à tout moment.',
                'يتطلب الصيام لمدة 8-12 ساعة قبل التحليل. يُسمح بشرب الماء فقط.' => 'Jeûne de 8 à 12 heures requis. Seule l\'eau est autorisée.',
                'يتطلب الصيام لمدة 12 ساعة قبل التحليل. تجنب الأطعمة الدسمة في اليوم السابق.' => 'Jeûne de 12 heures requis. Éviter les aliments gras la veille.',
                'يفضل الصيام لمدة 8 ساعات. تجنب الأدوية التي قد تؤثر على الكبد قبل الفحص بعد استشارة الطبيب.' => 'Jeûne de 8 heures préférable. Éviter les médicaments affectant le foie sans avis médical.',
                'يفضل الصيام لمدة 8 ساعات. شرب كمية كافية من الماء في اليوم السابق.' => 'Jeûne de 8 heures préférable. Bien s\'hydrater la veille.',
                'جمع عينة البول الصباحي الأول. غسل المنطقة التناسلية قبل جمع العينة.' => 'Recueillir les premières urines du matin après toilette intime.',
                'لا يتطلب أي تحضيرات خاصة. يمكن إجراء التحليل في أي وقت.' => 'Aucune preparation spéciale. Peut être effectué à tout moment.',
                'لا يتطلب صيام. تجنب أدوية الغدة الدرقية قبل 4 ساعات من التحليل بعد استشارة الطبيب.' => 'À jeun non requis. Éviter les médicaments thyroïdiens 4h avant sans avis médical.',
                'لا يتطلب صيام. يمكن إجراء التحليل في أي وقت من اليوم.' => 'À jeun non requis. Peut être effectué à tout moment.',
            ];

            // Apply translations to analyses
            foreach ($reservationRequest->analyses as $analysis) {
                $analysis->name_fr = $analysisTranslations[$analysis->name] ?? $analysis->name;
                $analysis->prep_ar = $analysis->preparation_instructions;
                $analysis->prep_fr = $instructionTranslations[$analysis->preparation_instructions] ?? null;
            }

            // Generate QR code
            $barcode = null;
            if (extension_loaded('gd')) {
                $qrData = "VIST-" . $reservationRequest->id . " | Patient: " . $reservationRequest->name . " | Phone: " . $reservationRequest->phone;
                $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');
            }

            // Generate PDF
            $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('reservation-pdf', [
                'reservation' => $reservationRequest,
                'barcode' => $barcode,
            ])->setPaper('A4', 'portrait')->output();

            // Save to temp file
            $tempPath = storage_path('app/temp_reservation_' . $reservationRequest->id . '.pdf');
            file_put_contents($tempPath, $pdfContent);

            // Send email with PDF attachment
            $analysisDate = \Carbon\Carbon::parse($request->analysis_date)->format('d/m/Y');
            $subject = $smartMessage ? 'إرشاد هام وتأكيد موعد التحليل - labo.dz' : 'تأكيد حجز التحليل - labo.dz';

            Mail::send('emails.booking-confirmed', [
                'patientName' => $reservationRequest->name,
                'reservationId' => $reservationRequest->id,
                'analysisDate' => $analysisDate,
                'analysisTime' => $request->time,
                'analyses' => $analyses,
                'smartMessage' => $smartMessage,
            ], function ($message) use ($patientEmail, $reservationRequest, $tempPath, $subject) {
                $message->to($patientEmail)
                        ->subject($subject)
                        ->attach($tempPath, [
                            'as' => 'preparation_' . str_replace(' ', '_', $reservationRequest->name) . '.pdf',
                            'mime' => 'application/pdf',
                        ]);
            });

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            Log::info('Booking confirmation email sent to: ' . $patientEmail);
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email: ' . $e->getMessage());
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

        // Run eligibility check for each analysis in the reservation
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

        // --- Start French Translation Map ---
        $analysisTranslations = [
            'تحليل الدم الشامل' => 'Numération Formule Sanguine (NFS)',
            'تحليل السكر في الدم' => 'Glycémie à jeun',
            'تحليل الكوليسترول والدهون' => 'Bilan Lipidique',
            'تحليل وظائف الكبد' => 'Bilan Hépatique',
            'تحليل وظائف الكلى' => 'Bilan Rénal',
            'تحليل البول الكامل' => 'Examen des Urines (ECBU)',
            'فصيلة الدم' => 'Groupage Sanguin',
            'سرعة الترسيب' => 'Vitesse de Sédimentation (VS)',
            'تحليل وظائف الغدة الدرقية' => 'Bilan Thyroïdien',
            'تحليل فيتامين د' => 'Vitamine D',
        ];

        $questionTranslations = [
            'هل أنت صائم حالياً؟' => 'Êtes-vous à jeun actuellement ?',
            'منذ متى وأنت صائم؟' => 'Depuis combien de temps êtes-vous à jeun ?',
            'هل تتناول أي أدوية حالياً؟' => 'Prenez-vous des médicaments actuellement ?',
            'هل تعاني من أي أمراض مزمنة؟' => 'Souffrez-vous de maladies chroniques ?',
            'هل أجريت أي عمليات جراحية مؤخراً؟' => 'Avez-vous subi une chirurgie récemment ?',
            'هل أنتِ حامل؟' => 'Êtes-vous enceinte ?',
            'هل تعاني من حساسية تجاه أدوية معينة؟' => 'Avez-vous des allergies médicamenteuses ?',
        ];

        $optionTranslations = [
            'نعم' => 'Oui',
            'لا' => 'Non',
            'أكثر من 8 ساعات' => 'Plus de 8 heures',
            'أقل من 8 ساعات' => 'Moins de 8 heures',
            'سكر' => 'Diabète',
            'ضغط' => 'Hypertension',
        ];

        $instructionTranslations = [
            'لا يتطلب صيام. يمكن إجراء التحليل في أي وقت.' => 'À jeun non requis. Peut être effectué à tout moment.',
            'يتطلب الصيام لمدة 8-12 ساعة قبل التحليل. يُسمح بشرب الماء فقط.' => 'Jeûne de 8 à 12 heures requis. Seule l\'eau est autorisée.',
            'يتطلب الصيام لمدة 12 ساعة قبل التحليل. تجنب الأطعمة الدسمة في اليوم السابق.' => 'Jeûne de 12 heures requis. Éviter les aliments gras la veille.',
        ];
        // --- End French Translation Map ---

        $results = [];
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            try {
                $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->analysis_id);

                $jsonStatus = $statusMap[$checkResult['status']] ?? 'READY';
                $viewStatus = $viewStatusMap[$jsonStatus] ?? 'ready';

                // Update database status
                $resAnalysis->update(['status' => $viewStatus]);

                // Translate notes
                $translatedNotes = [];
                if (isset($checkResult['notes'])) {
                    foreach ($checkResult['notes'] as $note) {
                        // Split note if it's "Question: Option"
                        if (str_contains($note, ': ')) {
                            [$q, $o] = explode(': ', $note, 2);
                            $translatedNotes[] = ($questionTranslations[$q] ?? $q) . ': ' . ($optionTranslations[$o] ?? $o);
                        } else {
                            $translatedNotes[] = $questionTranslations[$note] ?? $note;
                        }
                    }
                }

                $results[] = [
                    'analysis_id' => $resAnalysis->id,
                    'name' => $resAnalysis->analyse ? ($analysisTranslations[$resAnalysis->analyse->name] ?? $resAnalysis->analyse->name) : "Analysis #{$resAnalysis->analysis_id}",
                    'status' => $viewStatus,
                    'fasting_valid' => ($jsonStatus !== 'INVALID'),
                    'eligibility_status' => $jsonStatus,
                    'notes' => $translatedNotes,
                    'action' => $checkResult['status'],
                ];
            } catch (\Exception $e) {
                \Log::error("Error checking eligibility for analysis {$resAnalysis->analysis_id}: " . $e->getMessage());
                $results[] = [
                    'analysis_id' => $resAnalysis->id,
                    'name' => $resAnalysis->analyse ? ($analysisTranslations[$resAnalysis->analyse->name] ?? $resAnalysis->analyse->name) : "Analysis #{$resAnalysis->analysis_id}",
                    'status' => 'ready',
                    'notes' => ["Erreur interne: " . $e->getMessage()],
                    'action' => 'eligible',
                ];
            }
        }

        \Log::info("Eligibility check completed for reservation: $id. Found " . count($results) . " results.");

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'L\'évaluation a été mise à jour avec succès.',
                'results' => $results,
            ]);
        }

        return redirect()->route('reservations')->with('success', 'L\'évaluation a été mise à jour avec succès.');
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
        \App::setLocale('fr');
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse'])->findOrFail($id);

        // --- Translation Maps ---
        $analysisTranslations = [
            'تحليل الدم الشامل' => 'Numération Formule Sanguine (NFS)',
            'تحليل السكر في الدم' => 'Glycémie à jeun',
            'تحليل الكوليسترول والدهون' => 'Bilan Lipidique',
            'تحليل وظائف الكبد' => 'Bilan Hépatique',
            'تحليل وظائف الكلى' => 'Bilan Rénal',
            'تحليل البول الكامل' => 'Examen des Urines (ECBU)',
            'فصيلة الدم' => 'Groupage Sanguin',
            'سرعة الترسيب' => 'Vitesse de Sédimentation (VS)',
            'تحليل وظائف الغدة الدرقية' => 'Bilan Thyroïdien',
            'تحليل فيتامين د' => 'Vitamine D',
        ];

        $questionTranslations = [
            'هل أنت صائم حالياً؟' => 'Êtes-vous à jeun actuellement ?',
            'منذ متى وأنت صائم؟' => 'Depuis combien de temps êtes-vous à jeun ?',
            'هل تتناول أي أدوية حالياً؟' => 'Prenez-vous des médicaments actuellement ?',
            'هل تعاني من أي أمراض مزمنة؟' => 'Souffrez-vous de maladies chroniques ?',
            'هل أجريت أي عمليات جراحية مؤخراً؟' => 'Avez-vous subi une chirurgie récemment ?',
            'هل أنتِ حامل؟' => 'Êtes-vous enceinte ?',
            'هل تعاني من حساسية تجاه أدوية معينة؟' => 'Avez-vous des allergies médicamenteuses ?',
        ];

        $optionTranslations = [
            'نعم' => 'Oui',
            'لا' => 'Non',
            'أكثر من 8 ساعات' => 'Plus de 8 heures',
            'أقل من 8 ساعات' => 'Moins de 8 heures',
            'سكر' => 'Diabète',
            'ضغط' => 'Hypertension',
        ];

        $results = [];
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->analysis_id);
            
            $translatedNotes = [];
            if (isset($checkResult['notes'])) {
                foreach ($checkResult['notes'] as $note) {
                    if (str_contains($note, ': ')) {
                        [$q, $o] = explode(': ', $note, 2);
                        $translatedNotes[] = ($questionTranslations[$q] ?? $q) . ': ' . ($optionTranslations[$o] ?? $o);
                    } else {
                        $translatedNotes[] = $questionTranslations[$note] ?? $note;
                    }
                }
            }

            $results[] = [
                'name' => $resAnalysis->analyse ? ($analysisTranslations[$resAnalysis->analyse->name] ?? $resAnalysis->analyse->name) : "Analysis #{$resAnalysis->analysis_id}",
                'status' => $checkResult['status'],
                'notes' => $translatedNotes
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
            ->get();

        // Translate questions and options in patientAnswers
        foreach ($patientAnswers as $ans) {
            if ($ans->question) {
                $ans->question->question = $questionTranslations[$ans->question->question] ?? $ans->question->question;
            }
            if ($ans->option) {
                $ans->option->text = $optionTranslations[$ans->option->text] ?? $ans->option->text;
            }
        }

        $patientAnswers = $patientAnswers->groupBy('question_id');

        // Generate Professional QR Code
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
        \App::setLocale('fr');
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse'])->findOrFail($id);

        // --- Translation Maps ---
        $analysisTranslations = [
            'تحليل الدم الشامل' => 'Numération Formule Sanguine (NFS)',
            'تحليل السكر في الدم' => 'Glycémie à jeun',
            'تحليل الكوليسترول والدهون' => 'Bilan Lipidique',
            'تحليل وظائف الكبد' => 'Bilan Hépatique',
            'تحليل وظائف الكلى' => 'Bilan Rénal',
            'تحليل البول الكامل' => 'Examen des Urines (ECBU)',
            'فصيلة الدم' => 'Groupage Sanguin',
            'سرعة الترسيب' => 'Vitesse de Sédimentation (VS)',
            'تحليل وظائف الغدة الدرقية' => 'Bilan Thyroïdien',
            'تحليل فيتامين د' => 'Vitamine D',
        ];

        $questionTranslations = [
            'هل أنت صائم حالياً؟' => 'Êtes-vous à jeun actuellement ?',
            'منذ متى وأنت صائم؟' => 'Depuis combien de temps êtes-vous à jeun ?',
            'هل تتناول أي أدوية حالياً؟' => 'Prenez-vous des médicaments actuellement ?',
            'هل تعاني من أي أمراض مزمنة؟' => 'Souffrez-vous de maladies chroniques ?',
            'هل أجريت أي عمليات جراحية مؤخراً؟' => 'Avez-vous subi une chirurgie récemment ?',
            'هل أنتِ حامل؟' => 'Êtes-vous enceinte ?',
            'هل تعاني من حساسية تجاه أدوية معينة؟' => 'Avez-vous des allergies médicamenteuses ?',
        ];

        $optionTranslations = [
            'نعم' => 'Oui',
            'لا' => 'Non',
            'أكثر من 8 ساعات' => 'Plus de 8 heures',
            'أقل من 8 ساعات' => 'Moins de 8 heures',
            'سكر' => 'Diabète',
            'ضغط' => 'Hypertension',
        ];

        $results = [];
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->analysis_id);
            
            $translatedNotes = [];
            if (isset($checkResult['notes'])) {
                foreach ($checkResult['notes'] as $note) {
                    if (str_contains($note, ': ')) {
                        [$q, $o] = explode(': ', $note, 2);
                        $translatedNotes[] = ($questionTranslations[$q] ?? $q) . ': ' . ($optionTranslations[$o] ?? $o);
                    } else {
                        $translatedNotes[] = $questionTranslations[$note] ?? $note;
                    }
                }
            }

            $results[] = [
                'name' => $resAnalysis->analyse ? ($analysisTranslations[$resAnalysis->analyse->name] ?? $resAnalysis->analyse->name) : "Analysis #{$resAnalysis->analysis_id}",
                'status' => $checkResult['status'],
                'notes' => $translatedNotes
            ];
        }

        $analyseIds = $reservation->reservationAnalyses->pluck('analysis_id');
        
        $patientAnswers = PatientAnswer::with(['question', 'option'])
            ->where('patient_id', $reservation->patient_id)
            ->whereHas('question', function($query) use ($analyseIds) {
                $query->whereIn('analyse_id', $analyseIds);
            })
            ->get();

        // Translate questions and options in patientAnswers
        foreach ($patientAnswers as $ans) {
            if ($ans->question) {
                $ans->question->question = $questionTranslations[$ans->question->question] ?? $ans->question->question;
            }
            if ($ans->option) {
                $ans->option->text = $optionTranslations[$ans->option->text] ?? $ans->option->text;
            }
        }

        $patientAnswers = $patientAnswers->groupBy('question_id');

        // Generate Professional QR Code
        $patientName = $reservation->patient->name ?? $reservation->name ?? 'Patient';
        $patientPhone = $reservation->patient->phone ?? $reservation->phone ?? 'N/A';
        $qrData = "VIST-" . $reservation->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
        $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');

        return view('Adminstration.eligibility-report', compact('reservation', 'results', 'patientAnswers', 'barcode'));
    }

    /**
     * Link physician to reservation
     */
    public function updateReferral(Request $request, $id)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->update(['doctor_id' => $request->doctor_id]);

        return response()->json([
            'success' => true,
            'message' => 'تم ربط الطبيب بنجاح',
        ]);
    }

    /**
     * Notify participants (Patient & Doctor) about results
     */
    public function notifyParticipants(Request $request, $id)
    {
        $reservation = Reservation::with(['patient', 'doctor', 'reservationAnalyses.analyse'])->findOrFail($id);
        $target = $request->get('target', 'both'); // patient, doctor, both
        
        $notifications = [];
        $errors = [];
        $additionalNotes = $reservation->result_notes ?? '';

        // 1. Notify Patient
        if ($target == 'patient' || $target == 'both') {
            if (!$reservation->patient || !$reservation->patient->email) {
                $errors[] = __('messages.patient_no_email');
            } else {
                try {
                    \Mail::send('emails.test-result', [
                        'patient' => $reservation->patient,
                        'reservation' => $reservation,
                        'additional_notes' => $additionalNotes,
                    ], function($message) use ($reservation) {
                        $message->to($reservation->patient->email)
                                ->subject(__('messages.results_ready_title') . ' - labo.dz');
                    });

                    // Create persistent reminder for the patient portal
                    \App\Models\Reminder::create([
                        'patient_id' => $reservation->patient_id,
                        'reservation_id' => $reservation->id,
                        'message' => __('messages.results_ready_desc'),
                        'scheduled_for' => now(),
                        'sent_at' => now(),
                        'is_sent' => true,
                    ]);

                    $notifications[] = __('messages.patient');
                } catch (\Exception $e) {
                    \Log::error("Failed to notify patient for reservation {$id}: " . $e->getMessage());
                    $errors[] = "خطأ في إرسال بريد المريض: " . $e->getMessage();
                }
            }
        }

        // 2. Notify Doctor
        if ($target == 'doctor' || $target == 'both') {
            if (!$reservation->doctor_id) {
                $errors[] = __('messages.no_doctor_assigned');
            } elseif (!$reservation->doctor || !$reservation->doctor->email) {
                $errors[] = __('messages.doctor_no_email');
            } else {
                try {
                    \Mail::send('emails.test-result', [
                        'patient' => $reservation->patient,
                        'reservation' => $reservation,
                        'additional_notes' => $additionalNotes . "\n(Référé par: " . $reservation->doctor->name . ")",
                    ], function($message) use ($reservation) {
                        $message->to($reservation->doctor->email)
                                ->subject('تنبيه نتائج المريض: ' . ($reservation->patient->name ?? 'مريض') . ' - labo.dz');
                    });
                    $notifications[] = __('messages.doctor');
                } catch (\Exception $e) {
                    \Log::error("Failed to notify doctor for reservation {$id}: " . $e->getMessage());
                    $errors[] = "خطأ في إرسال بريد الطبيب: " . $e->getMessage();
                }
            }
        }

        // Construct response message
        $messageLines = [];
        if (count($notifications) > 0) {
            $messageLines[] = 'تم إرسال التنبيهات لـ: ' . implode(', ', $notifications);
        }
        if (count($errors) > 0) {
            $messageLines[] = implode(' | ', $errors);
        }

        $finalMessage = !empty($messageLines) ? implode(' | ', $messageLines) : 'لم يتم تنفيذ أي عملية';

        return response()->json([
            'success' => count($notifications) > 0,
            'message' => $finalMessage,
        ]);
    }

    /**
     * Show the results entry form for a specific reservation
     */
    public function showResultsForm($id)
    {
        $reservation = Reservation::with(['patient', 'reservationAnalyses.analyse'])->findOrFail($id);
        
        // Ensure that each reservation analysis has a unit and reference range pre-filled if they are empty
        foreach ($reservation->reservationAnalyses as $resAnalysis) {
            if (empty($resAnalysis->unit) && !empty($resAnalysis->analyse->unit)) {
                $resAnalysis->unit = $resAnalysis->analyse->unit;
            }
            if (empty($resAnalysis->reference_range) && !empty($resAnalysis->analyse->normal_range)) {
                $resAnalysis->reference_range = $resAnalysis->analyse->normal_range;
            }
        }

        return view('Adminstration.results-form', compact('reservation'));
    }

    /**
     * Update clinical results for a reservation
     */
    public function updateResults(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $resultsData = $request->input('results', []);

        foreach ($resultsData as $analysisId => $data) {
            $resAnalysis = ReservationAnalysis::where('reservation_id', $id)
                ->where('id', $analysisId)
                ->first();

            if ($resAnalysis) {
                $value = $data['value'] ?? null;
                $clinicalStatus = 'Normal';

                // Automated Safety Check (Critical Values)
                if ($value !== null && is_numeric($value)) {
                    $analyse = $resAnalysis->analyse;
                    if ($analyse) {
                        if ($analyse->max_critical !== null && $value >= $analyse->max_critical) {
                            $clinicalStatus = 'CRITICAL';
                        } elseif ($analyse->min_critical !== null && $value <= $analyse->min_critical) {
                            $clinicalStatus = 'CRITICAL';
                        }
                    }
                }

                $resAnalysis->update([
                    'result_value' => $value,
                    'unit' => $data['unit'] ?? null,
                    'reference_range' => $data['reference_range'] ?? null,
                    'clinical_status' => $clinicalStatus,
                ]);
            }
        }

        // Trigger notification if requested
        if ($request->has('notify_patient') && $request->notify_patient == '1') {
            $this->notifyParticipants($request->merge(['target' => 'patient']), $id);
        }

        return redirect()->route('reservations')->with('success', __('messages.results_updated'));
    }
}
