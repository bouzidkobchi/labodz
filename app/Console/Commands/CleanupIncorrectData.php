<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use App\Models\Request_reservation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CleanupIncorrectData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-incorrect-data {--dry-run : Only show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge patients and reservations with incorrect information (legacy data cleanup)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $phoneRegex = '/^(05|06|07)[0-9]{8}$/';
        $now = Carbon::now('Africa/Algiers')->toDateString();

        $this->info("Starting Data Integrity Cleanup...");
        if ($dryRun) {
            $this->warn("!!! DRY RUN MODE - No data will be modified !!!");
        }

        // 1. Target Invalid Patients
        $invalidPatients = Patient::all()->filter(function ($patient) use ($phoneRegex) {
            $invalidPhone = !preg_match($phoneRegex, $patient->phone);
            $invalidEmail = $patient->email && !filter_var($patient->email, FILTER_VALIDATE_EMAIL);
            return $invalidPhone || $invalidEmail;
        });

        $this->comment("Checking Patients...");
        if ($invalidPatients->count() > 0) {
            $this->warn("Found {$invalidPatients->count()} patients with invalid data.");
            foreach ($invalidPatients as $p) {
                $this->line(" - [Patient #{$p->id}] {$p->name} | Phone: {$p->phone} | Email: {$p->email}");
                if (!$dryRun) {
                    $p->delete(); // Cascades to reservations and patient_answers
                }
            }
        } else {
            $this->info("No invalid patients found.");
        }

        // 2. Target Invalid Request Reservations
        $invalidRequests = Request_reservation::all()->filter(function ($req) use ($phoneRegex, $now) {
            $invalidPhone = !preg_match($phoneRegex, $req->phone);
            $invalidEmail = $req->email && !filter_var($req->email, FILTER_VALIDATE_EMAIL);
            $pastPending = ($req->status === 'pending' && $req->preferred_date && $req->preferred_date->toDateString() < $now);
            
            return $invalidPhone || $invalidEmail || $pastPending;
        });

        $this->comment("\nChecking Reservation Requests...");
        if ($invalidRequests->count() > 0) {
            $this->warn("Found {$invalidRequests->count()} requests with invalid data or past pending dates.");
            foreach ($invalidRequests as $r) {
                $reason = [];
                if (!preg_match($phoneRegex, $r->phone)) $reason[] = "Invalid Phone";
                if ($r->email && !filter_var($r->email, FILTER_VALIDATE_EMAIL)) $reason[] = "Invalid Email";
                if ($r->status === 'pending' && $r->preferred_date && $r->preferred_date->toDateString() < $now) $reason[] = "Past Pending Date";
                
                $this->line(" - [Request #{$r->id}] {$r->name} | Reason: " . implode(', ', $reason));
                if (!$dryRun) {
                    $r->delete();
                }
            }
        } else {
            $this->info("No invalid reservation requests found.");
        }

        $this->info("\nCleanup completed.");
        return 0;
    }
}
