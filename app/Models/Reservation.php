<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class Reservation extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'analysis_date',
        'time',
        'status',
        'result_notes',
        'result_file_path',
        'prescription_path',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function reservationAnalyses()
    {
        return $this->hasMany(ReservationAnalysis::class);
    }

    public function analyses()
    {
        return $this->belongsToMany(Analyse::class, 'reservation_analyses', 'reservation_id', 'analysis_id')
            ->withPivot(['result_value', 'unit', 'reference_range', 'status']);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }
}
