<?php

namespace App\Http\Controllers;

use App\Models\Analyse;
use App\Models\Question;
use App\Models\Option;
use App\Models\AnalysisRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EligibilityController extends Controller
{
    /**
     * Show the management page for eligibility.
     */
    public function manage($analysis_id)
    {
        $analysis = Analyse::with(['questions.options', 'analysisRules'])->findOrFail($analysis_id);
        
        // Map rules to options for easier display
        $rules = $analysis->analysisRules->groupBy('disallowed_option_id');

        return view('Adminstration.manage-eligibility', compact('analysis', 'rules'));
    }

    /**
     * Store a new question for an analysis.
     */
    public function storeQuestion(Request $request, $analysis_id)
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        Question::create([
            'question' => $request->question,
            'analyse_id' => $analysis_id,
            'type' => 'radio', // Default to radio for simple eligibility
            'order' => Question::where('analyse_id', $analysis_id)->count() + 1,
        ]);

        return redirect()->back()->with('success', __('messages.save_success') ?? 'Saved successfully');
    }

    /**
     * Remove a question and its associated options/rules.
     */
    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
        
        DB::transaction(function () use ($question) {
            // Associated rules and options will be handled by DB cascade if set, 
            // but let's be explicit or rely on Eloquent events if any.
            // AnalysisRule usually links to disallowed_option_id.
            $question->delete();
        });

        return redirect()->back()->with('success', __('messages.delete_success') ?? 'Deleted successfully');
    }

    /**
     * Store a new option and potentially an associated rule.
     */
    public function storeOption(Request $request, $question_id)
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'action' => 'required|in:none,warning,block,approval',
        ]);

        $question = Question::findOrFail($question_id);

        DB::transaction(function () use ($request, $question) {
            $option = Option::create([
                'question_id' => $question->id,
                'text' => $request->text,
                'value' => strtolower(str_replace(' ', '_', $request->text)),
            ]);

            if ($request->action !== 'none') {
                AnalysisRule::create([
                    'analysis_id' => $question->analyse_id,
                    'question_id' => $question->id,
                    'disallowed_option_id' => $option->id,
                    'action' => $request->action,
                ]);
            }
        });

        return redirect()->back()->with('success', __('messages.save_success') ?? 'Saved successfully');
    }

    /**
     * Remove an option and its associated rule.
     */
    public function destroyOption($id)
    {
        $option = Option::findOrFail($id);
        
        DB::transaction(function () use ($option) {
            AnalysisRule::where('disallowed_option_id', $option->id)->delete();
            $option->delete();
        });

        return redirect()->back()->with('success', __('messages.delete_success') ?? 'Deleted successfully');
    }
}
