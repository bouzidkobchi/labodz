<?php

namespace App\Http\Controllers;

use App\Models\Request_reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Generate PDF for a reservation request
     *
     * @param int $id Reservation request ID
     * @return \Illuminate\Http\Response
     */
    public function generateReservationPdf($id)
    {
        // Load reservation with related analyses and patient
        $reservation = Request_reservation::with(['analyses', 'patient'])->findOrFail($id);

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

        // Apply translations and preparation data
        foreach ($reservation->analyses as $analysis) {
            $analysis->name_fr = $analysisTranslations[$analysis->name] ?? $analysis->name;
            
            // Map Arabic to French for instructions
            $analysis->prep_ar = $analysis->preparation_instructions;
            $analysis->prep_fr = $instructionTranslations[$analysis->preparation_instructions] ?? null;
        }

        // Generate Professional QR Code with metadata (Null-safe)
        $barcode = null;
        if (extension_loaded('gd')) {
            $patientName = $reservation->patient->name ?? $reservation->name ?? 'Patient';
            $patientPhone = $reservation->patient->phone ?? $reservation->phone ?? 'N/A';
            $qrData = "VIST-" . $reservation->id . " | Patient: " . $patientName . " | Phone: " . $patientPhone;
            $barcode = \App\Helpers\BarcodeHelper::getQRBase64($qrData, '200x200');
        } else {
            \Log::warning('PHP GD extension missing. Skipping QR code in PDF.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('reservation-pdf', compact('reservation', 'barcode'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = 'reservation_' . str_replace(' ', '_', $reservation->name) . '_' . now()->format('YmdHis') . '.pdf';

        // Clean any output buffer to prevent corruption
        if (ob_get_length()) ob_end_clean();

        // Return PDF download with explicit headers
        return $pdf->download($filename);
    }
}
