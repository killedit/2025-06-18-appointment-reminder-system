<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppointmentStatusController extends Controller
{
    public function update(Request $request, $id){
        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled,missed',
        ]);

        $appointment = $request->user()->appointments()->find($id);

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        if ($appointment->status === $validated['status']) {
            return response()->json(['message' => 'Status is already set to this value'], Response::HTTP_OK);
        }

        $appointment->status = $validated['status'];
        $appointment->save();

        return response()->json([
            'message' => 'Appointment status updated',
            'appointment' => $appointment,
        ], Response::HTTP_OK);
    }
}
