<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question',
        'analyse_id',
        'type',
        'parent_question_id',
        'show_when_option_id',
        'is_multiple',
        'gender_condition',
        'order',
    ];

    /**
     * Get the analysis that owns the question.
     */
    public function analyse()
    {
        return $this->belongsTo(Analyse::class);
    }

    /**
     * Get the options for the question.
     */
    public function options()
    {
        return $this->hasMany(Option::class)->orderBy('id');
    }

    public function parentQuestion()
    {
        return $this->belongsTo(Question::class, 'parent_question_id');
    }

    public function subQuestions()
    {
        return $this->hasMany(Question::class, 'parent_question_id')->orderBy('order');
    }

    public function showWhenOption()
    {
        return $this->belongsTo(Option::class, 'show_when_option_id');
    }
}
