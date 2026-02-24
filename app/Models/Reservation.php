<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
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
        return $this->belongsToMany(Analyse::class, 'reservation_analyses');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }
}
