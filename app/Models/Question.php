<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject', 'question_text',
    ];

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id','id');
    }

    public function questionnaires()
    {
        return $this->belongsToMany(Questionnaire::class, 'questionnaire_question', 'question_id', 'questionnaire_id');
    }

}
