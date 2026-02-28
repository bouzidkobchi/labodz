<?php

namespace App\Http\Controllers;

use App\Models\Analyse;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Request_reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Labo_dzController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $analyses = Analyse::all(); // Or use your preferred method to get the data

        return view('Labo_dz', ['analyses' => $analyses]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function booking(Request $request)
    {
        // Validate the request with strict rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^(05|06|07)[0-9]{8}$/'],
            'email' => 'nullable|email:rfc,dns',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'analysisTypes' => 'required_without:prescription|array',
            'analysisTypes.*' => 'exists:analyses,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'prescription' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'phone.regex' => __('messages.invalid_phone_format'),
            'date.after_or_equal' => __('messages.invalid_date_past'),
            'email.email' => __('messages.invalid_email_format'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Handle prescription upload
            $prescriptionPath = null;
            if ($request->hasFile('prescription')) {
                $file = $request->file('prescription');
                $filename = time() . '_' . $file->getClientOriginalName();
                $prescriptionPath = $file->storeAs('prescriptions', $filename, 'public');
            }

            // Create reservation request with patient info
            $requestReservation = Request_reservation::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'analyse_id' => $request->analysisTypes[0] ?? null,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'preferred_date' => $request->date,
                'preferred_time' => $request->time,
                'prescription_path' => $prescriptionPath,
                'status' => 'pending',
            ]);

            // Attach multiple analyses if present
            if ($request->filled('analysisTypes')) {
                $requestReservation->analyses()->attach($request->analysisTypes);
            }

            // Get analysis names and preparation instructions for success message/modal
            $preparations = [];
            $displayNames = [];
            if ($request->filled('analysisTypes')) {
                $analyses = Analyse::whereIn('id', $request->analysisTypes)->get();
                foreach ($analyses as $analysis) {
                    $displayNames[] = $analysis->name;
                    if (!empty($analysis->preparation_instructions)) {
                        $preparations[] = [
                            'name' => $analysis->name,
                            'instructions' => $analysis->preparation_instructions
                        ];
                    }
                }
            }

            $analysisNames = !empty($displayNames) 
                ? ' للتحاليل التالية: ' . implode(', ', $displayNames) 
                : ' مع وصفة طبية مرفقة ';

            // Redirect with success message and trigger PDF download + Prep Modal
            return redirect()->back()
                ->with('success', __('messages.booking_success', [
                    'analyses' => $analysisNames,
                    'name' => $request->name,
                    'phone' => $request->phone
                ]))
                ->with('download_pdf', $requestReservation->id)
                ->with('preparations', $preparations);
        } catch (\Exception $e) {
            Log::error('Booking error:', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء إرسال طلب الحجز، يرجى المحاولة مرة أخرى');
        }
    }

    public function message(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Message::create([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message,
            ]);

            return redirect()->back()->with('success', 'تم إرسال رسالتك بنجاح وسنرد عليك في أقرب وقت');
        } catch (\Exception $e) {
            Log::error('Message sending error:', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء إرسال الرسالة، يرجى المحاولة مرة أخرى');
        }
    }

    public function analysisInfo()
    {
        $analyses = Analyse::all();

        return view('analysis-info', compact('analyses'));
    }
}
