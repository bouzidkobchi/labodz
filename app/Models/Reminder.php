<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'history_id',
        'patient_id',
        'analyse_id',
        'reservation_id',
        'message',
        'scheduled_for',
        'sent_at',
        'is_sent',
        'error_message',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function history()
    {
        return $this->belongsTo(History::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function analyse()
    {
        return $this->belongsTo(Analyse::class);
    }

    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeReadyToSend($query)
    {
        return $query->pending()
                     ->where('scheduled_for', '<=', now());
    }
}
