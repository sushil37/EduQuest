<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'type',
    ];

    public function questionnaires()
    {
        return $this->belongsToMany(Questionnaire::class, 'questionnaire_student')
            ->withPivot('access_url');
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
