<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'analyse_id',
        'gender',
        'birth_date',
        'preferred_date',
        'preferred_time',
        'status',
        'admin_notes',
        'patient_id',
        'history_id',
        'reservation_id',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'preferred_time' => 'datetime:H:i',
        'birth_date' => 'date',
    ];

    public function analyses()
    {
        return $this->belongsToMany(Analyse::class, 'request_reservation_analyses');
    }

    // Keep the old method for backward compatibility
    public function analyse()
    {
        return $this->belongsTo(Analyse::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function history()
    {
        return $this->belongsTo(History::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
