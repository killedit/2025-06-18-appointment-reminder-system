<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;
use Carbon\CarbonInterval;
use App\Models\ReminderDispatch;
use App\Jobs\SendReminderJob;

class ReminderController extends Controller
{
    public function index(Request $request, $id)
    {
        $appointment = $request->user()->appointments()->with('reminders')->find($id);

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'appointment' => $appointment->notes,
            'reminders' => $appointment->reminders
        ], Response::HTTP_OK);
    }
}
