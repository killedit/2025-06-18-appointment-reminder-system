<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ArrayHelper;
use Carbon\Carbon;
use App\Services\ReminderService;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $scope = $request->query('scope');

        $query = $user->appointments()->with('client');

        match ($scope) {
            'upcoming' => $query->where('scheduled_at', '>', now()),
            'past'     => $query->where('scheduled_at', '<', now()),
            default    => null
        };

        return response()->json($query->get(), Response::HTTP_OK);
    }

    public function store(Request $request, ReminderService $reminderService)
    {
        $data = $request->all();

        if (ArrayHelper::isAssoc($data)) {
            $data = [$data];
        }

        $createdAppointments = [];
        $errors = [];

        foreach ($data as $appointmentData) {
            $exists = $request->user()->appointments()
                ->where('client_id', $appointmentData['client_id'])
                ->where('scheduled_at', $appointmentData['scheduled_at'])
                ->where('notes', $appointmentData['notes'])
                ->where('timezone', $appointmentData['timezone'] ?? 'UTC')
                ->exists();

            if ($exists) {
                $errors[] = [
                    'appointment' => $appointmentData,
                    'errors' => ['duplicate' => ['An appointment with the same client, scheduled time, notes and timezone already exists.']],
                ];
                continue;
            }

            $validator = validator($appointmentData, [
                'client_id'    => 'required|exists:clients,id',
                'scheduled_at' => 'required|date|after:now',
                'notes'        => 'required|string',
                'repeat'       => 'nullable|in:none,weekly,monthly',
                'timezone'     => 'required|timezone',
            ]);

            if($validator->fails()){
                $errors[] = [
                    'appointment' => $appointmentData,
                    'errors' => $validator->errors()->messages()
                ];
                continue;
            }

            $createdAppointments[] = $request->user()->appointments()->create($validator->validated());

            $reminderService->scheduleReminders(end($createdAppointments));
        }

        return response()->json([
            'created' => $createdAppointments,
            'errors' => $errors,
        ], \Illuminate\Http\Response::HTTP_CREATED);
    }

    public function show(Request $request, $id)
    {
        $appointment = $request->user()->appointments()->find($id);

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($appointment, Response::HTTP_OK);
    }

    public function update(Request $request, $id, ReminderService $reminderService)
    {
        //Bulk update is not in the scope of the task.
        if (!ArrayHelper::isAssoc($request->all())) {
            return response()->json(['message' => 'Only one appointment can be updated at a time.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $appointment = $request->user()->appointments()->find($id);

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date|after_or_equal:now',
            'timezone' => 'sometimes|string|max:255',
            'repeat' => 'sometimes|in:none,weekly,monthly',
            'status' => 'nullable|in:completed,cancelled,missed',
            'notes' => 'sometimes|string',
            'client_id' => 'sometimes|exists:clients,id',
        ]);

        $appointment->fill($validated);

        if (! $appointment->isDirty()) {
            return response()->json(['message' => 'No changes detected'], Response::HTTP_OK);
        }

        $appointment->update($validated);

        $reminderService->scheduleReminders($appointment);

        return response()->json($appointment, Response::HTTP_OK);
    }

    public function delete(Request $request, $id)
    {
        $appointment = $request->user()->appointments()->find($id);

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        $appointment->delete();

        return response()->json([
            'deleted' => $appointment
        ], Response::HTTP_OK);
    }

}
