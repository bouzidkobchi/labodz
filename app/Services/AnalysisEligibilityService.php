<?php

namespace App\Services;

use App\Models\AnalysisRule;
use App\Models\PatientAnswer;

class AnalysisEligibilityService
{
    /**
     * Check if a patient is eligible for a specific analysis.
     *
     * @param int $patient_id
     * @param int $analysis_id
     * @return string
     */
    public function checkEligibility($patient_id, $analysis_id)
    {
        $rules = AnalysisRule::with(['question', 'option'])
            ->where('analysis_id', $analysis_id)
            ->get();

        $patientAnswers = PatientAnswer::where('patient_id', $patient_id)
            ->get()
            ->groupBy('question_id');

        $status = 'eligible';
        $notes = [];

        foreach ($rules as $rule) {
            $answers = $patientAnswers->get($rule->question_id);
            
            if ($answers) {
                $hasViolated = false;
                foreach ($answers as $answer) {
                    if ($answer->option_id == $rule->disallowed_option_id) {
                        $hasViolated = true;
                        break;
                    }
                }

                if ($hasViolated) {
                    // Update main status (block overrides warning)
                    if ($rule->action === 'block') {
                        $status = 'block';
                    } elseif ($status !== 'block' && $rule->action === 'warning') {
                        $status = 'warning';
                    }

                    // Add note if provided in rule or generate from question
                    $notes[] = $rule->question->question . ': ' . $rule->option->text;
                }
            }
        }

        return [
            'status' => $status,
            'notes' => $notes
        ];
    }
}
