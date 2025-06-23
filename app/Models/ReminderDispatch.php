<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class ReminderDispatch extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'appointment_id',
        'scheduled_for',
        'status',
        'channel',
        'sent_at',
        'log',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
