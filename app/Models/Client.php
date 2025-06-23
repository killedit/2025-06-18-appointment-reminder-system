<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;
use App\Models\ReminderDispatch;

class Client extends Model
{
    //
    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_id'
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function reminders()
    {
        return $this->hasMany(ReminderDispatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
