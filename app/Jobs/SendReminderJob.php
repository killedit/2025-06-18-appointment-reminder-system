<?php

namespace App\Jobs;

use App\Models\ReminderDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ReminderDispatch $reminder;

    public function __construct(ReminderDispatch $reminder)
    {
        $this->reminder = $reminder;
    }

    public function handle()
    {
        $reminder = $this->reminder->fresh('appointment.client');

        // Simulation. Next step would be to implement emails Mail::to()->send().
        Log::info("Reminder sent for appointment ID {$reminder->appointment_id} to client '{$reminder->appointment->client->email}'");

        $reminder->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
