<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class ReservationAnalysis extends Model
{
    use HasFactory, Auditable;

    protected $table = 'reservation_analyses';

    protected $fillable = [
        'reservation_id',
        'analysis_id',
        'status',
        'result_value',
        'unit',
        'reference_range',
        'clinical_status',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function analyse()
    {
        return $this->belongsTo(Analyse::class, 'analysis_id');
    }
}
