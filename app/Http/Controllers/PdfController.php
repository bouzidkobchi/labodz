<?php

namespace App\Http\Controllers;

use App\Models\Request_reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PdfController extends Controller
{
    /**
     * Generate PDF for an appointment (supports both requests and confirmed bookings)
     *
     * @param int $id ID of the record
     * @param string $type Type of record ('request' or 'confirmed')
     */
    public function generateReservationPdf(Request $request, $id, $type = 'confirmed')
    {
        // Force French locale for medical documents (Physician/Admin reports)
        \App::setLocale('fr');

        $reservation = null;
        $isAuthorized = false;

        // 1. Authorization & Fetching
        if ($type === 'request') {
            $reservation = \App\Models\Request_reservation::with(['analyses', 'patient'])->findOrFail($id);
            
            // Allow if admin OR if it was just booked in this session
            if (Auth::guard('administrator')->check() || session('download_pdf') == $id) {
                $isAuthorized = true;
            }
        } else {
            $reservation = \App\Models\Reservation::with(['analyses', 'patient'])->findOrFail($id);

            // Allow if admin
            if (Auth::guard('administrator')->check()) {
                $isAuthorized = true;
            }
            // Allow if doctor linked to it
            elseif (Auth::guard('doctor')->check() && Auth::guard('doctor')->id() == $reservation->doctor_id) {
                $isAuthorized = true;
            }
            // Allow if patient in session
            elseif (session('patient_reservation_id') == $id) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to medical document.');
        }

        // 2. Data Preparation Consistency
        // Analysis Name Translation Mapping
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
            'تحليل فيتامين د' => 'Vitamine D'
        ];

        // Preparation Instructions Translation Mapping
        $instructionTranslations = [
            'لا يتطلب صيام. يمكن إجراء التحليل في أي وقت.' => 'À jeun non requis. Peut être effectué à tout moment.',
            'يتطلب الصيام لمدة 8-12 ساعة قبل التحليل. يُسمح بشرب الماء فقط.' => 'Jeûne de 8 à 12 heures requis. Seule l\'eau est autorisée.',
            'يتطلب الصيام لمدة 12 ساعة قبل التحليل. تجنب الأطعمة الدسمة في اليوم السابق.' => 'Jeûne de 12 heures requis. Éviter les aliments gras la veille.',
            'يفضل الصيام لمدة 8 ساعات. تجنب الأدوية التي قد تؤثر على الكبد قبل الفحص بعد استشارة الطبيب.' => 'Jeûne de 8 heures préférable. Éviter les médicaments affectant le foie sans avis médical.',
            'يفضل الصيام لمدة 8 ساعات. شرب كمية كافية من الماء في اليوم السابق.' => 'Jeûne de 8 heures préférable. Bien s\'hydrater la veille.',
            'جمع عينة البول الصباحي الأول. غسل المنطقة التناسلية قبل جمع العينة.' => 'Recueillir les premières urines du matin après toilette intime.',
            'لا يتطلب أي تحضيرات خاصة. يمكن إجراء التحليل في أي وقت.' => 'Aucune préparation spéciale. Peut être effectué à tout moment.',
            'لا يتطلب صيام. تجنب أدوية الغدة الدرقية قبل 4 ساعات من التحليل بعد استشارة الطبيب.' => 'À jeun non requis. Éviter les médicaments thyroïdiens 4h avant sans avis médical.',
            'لا يتطلب صيام. يمكن إجراء التحليل في أي وقت من اليوم.' => 'À jeun non requis. Peut être effectué à tout moment.'
        ];

        // Reference Ranges Translation Mapping
        $rangeTranslations = [
            'صائم: 70-100 mg/dL' => 'À jeun : 70-100 mg/dL',
            'عشوائي: أقل من 140 mg/dL' => 'Aléatoire : < 140 mg/dL',
            'صائم: 70-100 mg/dL، عشوائي: أقل من 140 mg/dL' => 'À jeun : 70-100 mg/dL, Aléatoire : < 140 mg/dL',
            'أقل من 200 mg/dL' => 'Inférieur à 200 mg/dL',
            'أقل من 150 mg/dL' => 'Inférieur à 150 mg/dL',
            'سليم' => 'Normal',
            'طبيعي' => 'Normal',
        ];

        // Normalize analyses for Blade (unified access)
        $analysesList = $reservation->analyses;
        
        foreach ($analysesList as $analysis) {
            $analysis->name_fr = $analysisTranslations[$analysis->name] ?? $analysis->name;
            $analysis->prep_ar = $analysis->preparation_instructions;
            $analysis->prep_fr = $instructionTranslations[$analysis->preparation_instructions] ?? null;
            
            // Translate pivot reference range if it exists
            if (isset($analysis->pivot->reference_range)) {
                $rawRange = $analysis->pivot->reference_range;
                $analysis->pivot->reference_range = $rangeTranslations[$rawRange] ?? $rawRange;
            }
        }

        // Normalize Patient Data for Blade
        $patientName = $reservation->patient->name ?? $reservation->name ?? 'Patient';
        $patientPhone = $reservation->patient->phone ?? $reservation->phone ?? 'N/A';
        $appointmentDate = $reservation->analysis_date ?? $reservation->preferred_date ?? $reservation->created_at;
        $appointmentTime = $reservation->time ?? $reservation->preferred_time ?? '--:--';

        // 3. Determine Report Mode
        $isResultReport = $analysesList->contains(fn($a) => !empty($a->pivot->result_value));
        $isEligibilityReport = false;
        
        // Manual override if report_type is specified
        if ($request->has('report_type')) {
            $isResultReport = ($request->report_type === 'results');
            $isEligibilityReport = ($request->report_type === 'eligibility');
        }

        // 4. Fetch Eligibility Data if needed
        $eligibilityResults = [];
        $patientAnswers = null;
        
        if ($isEligibilityReport) {
            $eligibilityService = app(\App\Services\AnalysisEligibilityService::class);
            
            // Re-use and Expand translation maps
            $questionTranslations = [
                'هل أنت صائم حالياً؟' => 'Êtes-vous à jeun actuellement ?',
                'منذ متى وأنت صائم؟' => 'Depuis combien de temps êtes-vous à jeun ?',
                'هل تتناول أي أدوية حالياً؟' => 'Prenez-vous des médicaments actuellement ?',
                'هل تعاني من أي أمراض مزمنة؟' => 'Souffrez-vous de maladies chroniques ?',
                'هل أجريت أي عمليات جراحية مؤخراً؟' => 'Avez-vous subi une chirurgie récemment ?',
                'هل أنتِ حامل؟' => 'Êtes-vous enceinte ?',
                'هل تعاني من حساسية تجاه أدوية معينة؟' => 'Avez-vous des allergies médicateuses ?',
                // FNS/FBS Specific Questions
                'هل صمت لمدة 8 إلى 12 ساعة قبل التحليل؟ (Fasting Duration)' => 'Avez-vous jeûné pendant 8 à 12 heures avant l\'analyse ?',
                'متى كانت آخر وجبة تناولتها؟ (Time Since Last Meal)' => 'À quand remonte votre dernier repas ?',
                'هل كانت آخر وجبة غنية بالسكريات أو الدهون؟ (Type of Last Meal)' => 'Votre dernier repas était-il riche en sucres ou en graisses ?',
                'هل شربت أي شيء خلال فترة الصيام؟ (Drinks During Fasting)' => 'Avez-vous bu quelque chose pendant le jeûne ?',
                'هل تناولت أي دواء هذا الصباح؟ (Medication This Morning)' => 'Avez-vous pris des médicaments ce matin ?',
                'هل هو دواء للسكري (أنسولين / ميتفورمين)؟ (Is it diabetes medication?)' => 'S\'agit-il d\'un médicament contre le diabète (Insuline / Metformine) ?',
                'هل لديك تشخيص مسبق بمرض السكري؟ (Previous Diabetes Diagnosis)' => 'Avez-vous un diagnostic préalable de diabète ?',
                'الأعراض الحالية (اختر كل ما ينطبق): (Current Symptoms)' => 'Symptômes actuels (cocher tout ce qui s\'applique) :',
                'هل مارست نشاطاً بدنياً مكثفاً في الـ 24 ساعة الماضية؟ (Intense Physical Activity)' => 'Avez-vous pratiqué une activité physique intense au cours des dernières 24 heures ?',
                'هل دخنت خلال فترة الصيام؟ (Smoking During Fasting)' => 'Avez-vous fumé pendant la période de jeûne ?',
                'هل يوجد حمل؟ (Pregnancy - Female only)' => 'Y a-t-il une grossesse ?',
            ];

            $optionTranslations = [
                'نعم' => 'Oui',
                'لا' => 'Non',
                'نعم (YES)' => 'Oui',
                'لا (NO)' => 'Non',
                'أكثر من 8 ساعات' => 'Plus de 8 heures',
                'أقل من 8 ساعات' => 'Moins de 8 heures',
                'سكر' => 'Diabète',
                'ضغط' => 'Hypertension',
                // Detailed Options
                'أقل من 4 ساعات (LESS_THAN_4H)' => 'Moins de 4 heures',
                'بين 8 إلى 12 ساعة (BETWEEN_8_12H)' => 'Entre 8 et 12 heures',
                'أكثر من 12 ساعة (MORE_THAN_12H)' => 'Plus de 12 heures',
                'غير معروف (UNKNOWN)' => 'Inconnu',
                'ماء فقط (WATER_ONLY)' => 'Eau uniquement',
                'قهوة أو شاي بدون سكر (COFFEE_OR_TEA)' => 'Café ou thé sans sucre',
                'مشروب سكري أو حليب (SUGARY_DRINK)' => 'Boisson sucrée ou lait',
                'أول مرة (FIRST_TIME)' => 'Première fois',
                'عطش شديد (SEVERE_THIRST)' => 'Soif intense',
                'تبول متكرر (FREQUENT_URINATION)' => 'Mictions fréquentes',
                'تعب غير عادي (UNUSUAL_FATIGUE)' => 'Fatigue inhabituelle',
                'لا يوجد (NONE)' => 'Aucun',
                'غير قابل للتطبيق (NOT_APPLICABLE)' => 'Non applicable',
            ];

            foreach ($analysesList as $resAnalysis) {
                $checkResult = $eligibilityService->checkEligibility($reservation->patient_id, $resAnalysis->id);
                
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

                $eligibilityResults[] = [
                    'name' => $resAnalysis->name_fr,
                    'status' => $checkResult['status'],
                    'notes' => $translatedNotes
                ];
            }

            $analyseIds = $analysesList->pluck('id');
            $patientAnswers = \App\Models\PatientAnswer::with(['question', 'option'])
                ->where('patient_id', $reservation->patient_id)
                ->whereHas('question', function($query) use ($analyseIds) {
                    $query->whereIn('analyse_id', $analyseIds);
                })
                ->get();

            // Translate answers
            foreach ($patientAnswers as $ans) {
                if ($ans->question) $ans->question->question = $questionTranslations[$ans->question->question] ?? $ans->question->question;
                if ($ans->option) $ans->option->text = $optionTranslations[$ans->option->text] ?? $ans->option->text;
            }
            $patientAnswers = $patientAnswers->groupBy('question_id');
        }

        // 5. QR Code Generation
        $barcode = null;
        if (extension_loaded('gd')) {
            $qrPrefix = $isResultReport ? 'RESULT-' : ($isEligibilityReport ? 'ELIG-' : 'VIST-');
            $qrType = $type == 'request' ? 'REQ-' : 'CONF-';
            $qrData = $qrPrefix . $qrType . $reservation->id . " | Patient: " . $patientName . " | Date: " . $appointmentDate;
            $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');
        }

        // 6. PDF Rendering
        $pdf = Pdf::loadView('reservation-pdf', [
            'reservation' => $reservation,
            'analyses' => $analysesList,
            'barcode' => $barcode,
            'patientName' => $patientName,
            'patientPhone' => $patientPhone,
            'appointmentDate' => $appointmentDate,
            'appointmentTime' => $appointmentTime,
            'type' => $type,
            'isResultReport' => $isResultReport,
            'isEligibilityReport' => $isEligibilityReport,
            'eligibilityResults' => $eligibilityResults,
            'patientAnswers' => $patientAnswers
        ]);

        $pdf->setPaper('A4', 'portrait');
        $filename = 'appointment_' . str_replace(' ', '_', $patientName) . '_' . now()->format('YmdHi') . '.pdf';

        if (ob_get_length()) ob_end_clean();

        return $pdf->download($filename);
    }
}
