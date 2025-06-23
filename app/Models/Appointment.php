<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'scheduled_at',
        'timezone',
        'repeat',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function reminders()
    {
        return $this->hasMany(ReminderDispatch::class);
    }
}
