<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ReminderDispatch;
use App\Jobs\SendReminderJob;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class ReminderService
{
    /**
     * Schedule reminders for an appointment based on default offsets.
     */
    public function scheduleReminders(Appointment $appointment): void
    {
        $offsets = config('reminders.default_offsets');

        foreach ($offsets as $offset) {
            $scheduledTime = Carbon::parse($appointment->scheduled_at, 'UTC')
            ->sub(CarbonInterval::make($offset));

            if ($scheduledTime->isPast()) {
                continue;
            }

            $reminder = ReminderDispatch::create([
                'appointment_id' => $appointment->id,
                'scheduled_for' => $scheduledTime,
                'status' => ReminderDispatch::STATUS_PENDING,
            ]);

            SendReminderJob::dispatch($reminder)->delay($scheduledTime);
        }
    }
}
