<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Option;
use App\Models\AnalysisRule;

class FnsQuestionsSeeder extends Seeder
{
    public function run()
    {
        $analyseId = 2; // تحليل السكر في الدم (FNS/FBS)

        // Cleanup existing questions for this analysis to avoid duplicates
        $existingQuestionIds = Question::where('analyse_id', $analyseId)->pluck('id');
        AnalysisRule::whereIn('question_id', $existingQuestionIds)->delete();
        Option::whereIn('question_id', $existingQuestionIds)->delete();
        Question::whereIn('id', $existingQuestionIds)->delete();

        // Q1 - Fasting Duration
        $q1 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل صمت لمدة 8 إلى 12 ساعة قبل التحليل؟ (Fasting Duration)',
            'type' => 'radio',
            'order' => 1
        ]);
        $o1_yes = Option::create(['question_id' => $q1->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        $o1_no = Option::create(['question_id' => $q1->id, 'text' => 'لا (NO)', 'value' => 'NO']);

        // Rule for Q1: NO -> INVALID (block)
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q1->id, 'disallowed_option_id' => $o1_no->id, 'action' => 'block']);

        // Q2 - Time Since Last Meal
        $q2 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'متى كانت آخر وجبة تناولتها؟ (Time Since Last Meal)',
            'type' => 'radio',
            'order' => 2
        ]);
        $o2_less4 = Option::create(['question_id' => $q2->id, 'text' => 'أقل من 4 ساعات (LESS_THAN_4H)', 'value' => 'LESS_THAN_4H']);
        $o2_8h = Option::create(['question_id' => $q2->id, 'text' => 'بين 8 إلى 12 ساعة (BETWEEN_8_12H)', 'value' => 'BETWEEN_8_12H']);
        $o2_more12 = Option::create(['question_id' => $q2->id, 'text' => 'أكثر من 12 ساعة (MORE_THAN_12H)', 'value' => 'MORE_THAN_12H']);

        // Rule for Q2: LESS_THAN_4H -> INVALID (block)
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q2->id, 'disallowed_option_id' => $o2_less4->id, 'action' => 'block']);

        // Q3 - Type of Last Meal
        $q3 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل كانت آخر وجبة غنية بالسكريات أو الدهون؟ (Type of Last Meal)',
            'type' => 'radio',
            'order' => 3
        ]);
        $o3_yes = Option::create(['question_id' => $q3->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        $o3_no = Option::create(['question_id' => $q3->id, 'text' => 'لا (NO)', 'value' => 'NO']);
        $o3_unknown = Option::create(['question_id' => $q3->id, 'text' => 'غير معروف (UNKNOWN)', 'value' => 'UNKNOWN']);

        // Rule for Q3: YES -> VALID_WITH_NOTE (warning)
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q3->id, 'disallowed_option_id' => $o3_yes->id, 'action' => 'warning']);

        // Q4 - Drinks During Fasting
        $q4 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل شربت أي شيء خلال فترة الصيام؟ (Drinks During Fasting)',
            'type' => 'radio',
            'order' => 4
        ]);
        $o4_water = Option::create(['question_id' => $q4->id, 'text' => 'ماء فقط (WATER_ONLY)', 'value' => 'WATER_ONLY']);
        $o4_coffee = Option::create(['question_id' => $q4->id, 'text' => 'قهوة أو شاي بدون سكر (COFFEE_OR_TEA)', 'value' => 'COFFEE_OR_TEA']);
        $o4_sugary = Option::create(['question_id' => $q4->id, 'text' => 'مشروب سكري أو حليب (SUGARY_DRINK)', 'value' => 'SUGARY_DRINK']);

        // Rules for Q4
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q4->id, 'disallowed_option_id' => $o4_sugary->id, 'action' => 'block']);
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q4->id, 'disallowed_option_id' => $o4_coffee->id, 'action' => 'warning']);

        // Q5 - Medication
        $q5 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل تناولت أي دواء هذا الصباح؟ (Medication This Morning)',
            'type' => 'radio',
            'order' => 5
        ]);
        $o5_yes = Option::create(['question_id' => $q5->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        $o5_no = Option::create(['question_id' => $q5->id, 'text' => 'لا (NO)', 'value' => 'NO']);

        // Q5 Sub-question
        $q5b = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل هو دواء للسكري (أنسولين / ميتفورمين)؟ (Is it diabetes medication?)',
            'type' => 'radio',
            'parent_question_id' => $q5->id,
            'show_when_option_id' => $o5_yes->id,
            'order' => 6
        ]);
        $o5b_yes = Option::create(['question_id' => $q5b->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        $o5b_no = Option::create(['question_id' => $q5b->id, 'text' => 'لا (NO)', 'value' => 'NO']);

        // Rule for Q5b: YES -> warning
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q5b->id, 'disallowed_option_id' => $o5b_yes->id, 'action' => 'warning']);

        // Q6 - Previous Diabetes Diagnosis
        $q6 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل لديك تشخيص مسبق بمرض السكري؟ (Previous Diabetes Diagnosis)',
            'type' => 'radio',
            'order' => 7
        ]);
        Option::create(['question_id' => $q6->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        Option::create(['question_id' => $q6->id, 'text' => 'لا (NO)', 'value' => 'NO']);
        Option::create(['question_id' => $q6->id, 'text' => 'أول مرة (FIRST_TIME)', 'value' => 'FIRST_TIME']);

        // Q7 - Current Symptoms (Multi-select)
        $q7 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'الأعراض الحالية (اختر كل ما ينطبق): (Current Symptoms)',
            'type' => 'checkbox',
            'is_multiple' => true,
            'order' => 8
        ]);
        Option::create(['question_id' => $q7->id, 'text' => 'عطش شديد (SEVERE_THIRST)', 'value' => 'SEVERE_THIRST']);
        Option::create(['question_id' => $q7->id, 'text' => 'تبول متكرر (FREQUENT_URINATION)', 'value' => 'FREQUENT_URINATION']);
        Option::create(['question_id' => $q7->id, 'text' => 'تعب غير عادي (UNUSUAL_FATIGUE)', 'value' => 'UNUSUAL_FATIGUE']);
        Option::create(['question_id' => $q7->id, 'text' => 'لا يوجد (NONE)', 'value' => 'NONE']);

        // Q8 - Intense Physical Activity
        $q8 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل مارست نشاطاً بدنياً مكثفاً في الـ 24 ساعة الماضية؟ (Intense Physical Activity)',
            'type' => 'radio',
            'order' => 9
        ]);
        $o8_yes = Option::create(['question_id' => $q8->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        $o8_no = Option::create(['question_id' => $q8->id, 'text' => 'لا (NO)', 'value' => 'NO']);

        // Rule for Q8: YES -> warning
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q8->id, 'disallowed_option_id' => $o8_yes->id, 'action' => 'warning']);

        // Q9 - Smoking During Fasting
        $q9 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل دخنت خلال فترة الصيام؟ (Smoking During Fasting)',
            'type' => 'radio',
            'order' => 10
        ]);
        $o9_yes = Option::create(['question_id' => $q9->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        $o9_no = Option::create(['question_id' => $q9->id, 'text' => 'لا (NO)', 'value' => 'NO']);

        // Rule for Q9: YES -> warning
        AnalysisRule::create(['analysis_id' => $analyseId, 'question_id' => $q9->id, 'disallowed_option_id' => $o9_yes->id, 'action' => 'warning']);

        // Q10 - Pregnancy (Female only)
        $q10 = Question::create([
            'analyse_id' => $analyseId,
            'question' => 'هل يوجد حمل؟ (Pregnancy - Female only)',
            'type' => 'radio',
            'gender_condition' => 'female',
            'order' => 11
        ]);
        Option::create(['question_id' => $q10->id, 'text' => 'نعم (YES)', 'value' => 'YES']);
        Option::create(['question_id' => $q10->id, 'text' => 'لا (NO)', 'value' => 'NO']);
        Option::create(['question_id' => $q10->id, 'text' => 'غير قابل للتطبيق (NOT_APPLICABLE)', 'value' => 'NOT_APPLICABLE']);
    }
}
