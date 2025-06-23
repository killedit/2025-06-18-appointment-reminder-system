<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentStatusController;
use App\Http\Controllers\ReminderController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/getToken', [RegisteredUserController::class, 'getToken']);

Route::middleware(['auth:sanctum', 'token.expired'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::delete('/clients/{id}', [ClientController::class, 'delete']);
});

Route::middleware(['auth:sanctum', 'token.expired'])->group(function () {
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'delete']);

    // Only these need timezone handling.
    Route::middleware(['appointment.timezone'])->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
        Route::get('/appointments/{id}/reminders', [ReminderController::class, 'index']);
    });

    // Appointment status
    Route::post('/appointments/{appointment}/status', [AppointmentStatusController::class, 'update']);
});

// Route::middleware(['auth:sanctum', 'token.expired'])->group(function () {
//     Route::get('/appointments/{id}/reminders', [ReminderController::class, 'index']);
// });
