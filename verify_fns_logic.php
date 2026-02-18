<?php

use App\Models\Option;
use App\Models\Patient;
use App\Models\PatientAnswer;
use App\Models\Question;
use App\Services\AnalysisEligibilityService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testScenarios($patientId, $analysisId, $service)
{
    echo "--- Testing FNS Eligibility Scenario ---\n";

    // Clear previous answers
    PatientAnswer::where('patient_id', $patientId)->delete();

    // 1. Ready Scenario (all good)
    $q1 = Question::where('analyse_id', $analysisId)->where('question', 'like', '%Fasting Duration%')->first();
    $o1_yes = Option::where('question_id', $q1->id)->where('value', 'YES')->first();

    $q2 = Question::where('analyse_id', $analysisId)->where('question', 'like', '%Time Since Last Meal%')->first();
    $o2_8h = Option::where('question_id', $q2->id)->where('value', 'BETWEEN_8_12H')->first();

    PatientAnswer::create(['patient_id' => $patientId, 'question_id' => $q1->id, 'option_id' => $o1_yes->id]);
    PatientAnswer::create(['patient_id' => $patientId, 'question_id' => $q2->id, 'option_id' => $o2_8h->id]);

    $result = $service->checkEligibility($patientId, $analysisId);
    echo 'SCENARIO 1 (Base): Status: '.$result['status'].' | Notes count: '.count($result['notes'])."\n";

    // 2. Invalid Scenario (Fasting NO)
    $o1_no = Option::where('question_id', $q1->id)->where('value', 'NO')->first();
    PatientAnswer::where('patient_id', $patientId)->where('question_id', $q1->id)->update(['option_id' => $o1_no->id]);
    $result = $service->checkEligibility($patientId, $analysisId);
    echo 'SCENARIO 2 (Fasting NO): Status: '.$result['status'].' | Note: '.($result['notes'][0] ?? 'None')."\n";

    // 3. Warning Scenario (Coffee/Tea)
    PatientAnswer::where('patient_id', $patientId)->where('question_id', $q1->id)->update(['option_id' => $o1_yes->id]);
    $q4 = Question::where('analyse_id', $analysisId)->where('question', 'like', '%Drinks During Fasting%')->first();
    $o4_coffee = Option::where('question_id', $q4->id)->where('value', 'COFFEE_OR_TEA')->first();
    PatientAnswer::create(['patient_id' => $patientId, 'question_id' => $q4->id, 'option_id' => $o4_coffee->id]);
    $result = $service->checkEligibility($patientId, $analysisId);
    echo 'SCENARIO 3 (Coffee/Tea): Status: '.$result['status'].' | Note: '.($result['notes'][0] ?? 'None')."\n";

    // 4. Mixed (Invalid wins)
    $o4_sugary = Option::where('question_id', $q4->id)->where('value', 'SUGARY_DRINK')->first();
    PatientAnswer::where('patient_id', $patientId)->where('question_id', $q4->id)->update(['option_id' => $o4_sugary->id]);
    $result = $service->checkEligibility($patientId, $analysisId);
    echo 'SCENARIO 4 (Mixed - Sugary Drink): Status: '.$result['status'].' | Total Notes: '.count($result['notes'])."\n";
}

$service = new AnalysisEligibilityService();
$patient = Patient::first();
$analysisId = 2;

if ($patient) {
    testScenarios($patient->id, $analysisId, $service);
} else {
    echo "No patient found for testing.\n";
}
