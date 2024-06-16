<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'expiry_date',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'questionnaire_question', 'questionnaire_id', 'question_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'questionnaire_student','questionnaire_id','student_id')
            ->withPivot('access_url');
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
