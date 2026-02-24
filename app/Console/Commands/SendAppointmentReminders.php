<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Reminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send {--force : Send all pending reminders regardless of time window}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to patients 14 hours before their analysis using Algeria timezone';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting appointment reminders process (Algeria Timezone)...');

        $now = Carbon::now('Africa/Algiers');
        $forceMode = $this->option('force');

        if ($forceMode) {
            $this->warn("FORCE MODE: Sending all pending reminders.");
            $appointments = Reservation::with(['patient', 'reservationAnalyses.analyse'])
                ->where('status', 'booked')
                ->whereDoesntHave('reminders', function ($query) {
                    $query->where('is_sent', true);
                })
                ->get();
        } else {
            // Target window: 13-15 hours from now
            $windowStart = $now->copy()->addHours(13);
            $windowEnd   = $now->copy()->addHours(15);

            $this->info("Looking for appointments between: {$windowStart->toDateTimeString()} and {$windowEnd->toDateTimeString()}");

            $appointments = Reservation::with(['patient', 'reservationAnalyses.analyse'])
                ->where('status', 'booked')
                ->whereBetween('analysis_date', [
                    $windowStart->toDateString(),
                    $windowEnd->toDateString(),
                ])
                ->whereDoesntHave('reminders', function ($query) {
                    $query->where('is_sent', true);
                })
                ->get()
                ->filter(function ($appointment) use ($windowStart, $windowEnd) {
                    $appointmentDateTime = Carbon::parse(
                        $appointment->analysis_date->toDateString() . ' ' . ($appointment->time ?? '00:00'),
                        'Africa/Algiers'
                    );
                    return $appointmentDateTime->between($windowStart, $windowEnd);
                });
        }

        $this->info("Found {$appointments->count()} appointments to remind.");

        $sentCount  = 0;
        $errorCount = 0;

        foreach ($appointments as $appointment) {
            try {
                $analyses = $appointment->reservationAnalyses->map(fn($ra) => $ra->analyse)->filter();

                if ($appointment->patient && $appointment->patient->email) {
                    $appointmentDate = Carbon::parse($appointment->analysis_date)->format('d/m/Y');
                    $appointmentTime = $appointment->time ?? '--:--';

                    Mail::send('emails.appointment-reminder', [
                        'patient'          => $appointment->patient,
                        'analyses'         => $analyses,
                        'appointment_date' => $appointmentDate,
                        'appointment_time' => $appointmentTime,
                    ], function ($message) use ($appointment) {
                        $message->to($appointment->patient->email)
                                ->subject('تذكير: موعد تحاليلكم الطبية - labo.dz');
                    });

                    Reminder::updateOrCreate(
                        [
                            'reservation_id' => $appointment->id,
                            'patient_id'     => $appointment->patient_id,
                        ],
                        [
                            'analyse_id'    => $appointment->reservationAnalyses->first()?->analysis_id ?? 0,
                            'scheduled_for' => $now,
                            'is_sent'       => true,
                            'sent_at'       => $now,
                        ]
                    );

                    $sentCount++;
                    $this->info("✓ Reminder sent to: {$appointment->patient->name}");
                }
            } catch (Exception $e) {
                $errorCount++;
                Log::error('Failed to send reminder ID ' . $appointment->id . ': ' . $e->getMessage());
                $this->error("Error sending to appointment {$appointment->id}");
            }
        }

        $this->info("Done. Sent: {$sentCount}, Errors: {$errorCount}");

        return Command::SUCCESS;
    }
}
