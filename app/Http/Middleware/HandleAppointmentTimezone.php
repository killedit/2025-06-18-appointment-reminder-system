<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Helpers\ArrayHelper;

class HandleAppointmentTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // AppointmentsController@store, AppointmentsController@update - store `scheduled_at` ONLY IN UTC for database consistency!
        $data = $request->all();
        $appointments = ArrayHelper::isAssoc($data) ? [$data] : $data;

        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            foreach ($data as $index => $item) {
                if (isset($item['scheduled_at'])) {
                    $timezone = $item['timezone'] ?? 'UTC';

                    try {
                        $dt = Carbon::parse($item['scheduled_at'], $timezone);
                        $data[$index]['scheduled_at'] = $dt->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $data[$index]['timezone'] = $timezone;
                    } catch (\Exception $e) {
                        Log::warning("Failed to parse scheduled_at at index $index", [
                            'input' => $item,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $request->replace($data);
        }
        elseif ($request->has('scheduled_at')) {
            $timezone = $request->input('timezone', 'UTC');
            try {
                $dt = Carbon::parse($request->input('scheduled_at'), $timezone);
                $request->merge([
                    'scheduled_at' => $dt->setTimezone('UTC')->format('Y-m-d H:i:s'),
                    'timezone'     => $timezone,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to parse scheduled_at in HandleAppointmentTimezone middleware.', [
                    'scheduled_at' => $request->input('scheduled_at'),
                    'timezone'     => $timezone,
                    'error'        => $e->getMessage(),
                ]);
            }
        }
// dd($appointments);
        // The reponse from AppointsController.
        $response = $next($request);

        // AppointsController@show - the client always sees `scheduled_at` in their LOCAL TIME.
        if (method_exists($response, 'getData')) {
            $data = $response->getData();

            // dd(
            //     $data
            // );
            // {
            //     "message": "No changes detected"
            // }

            // {
            //     "client_id": 1,
            //     "scheduled_at": "2025-06-25T18:00:00",
            //     "notes": "Quarterly review meeting",
            //     "repeat": "none",
            //     "timezone": "America/New_York"
            // }
            // {
            //   "id": 1
            //   "user_id": 1
            //   "client_id": 1
            //   "notes": "Quarterly review meeting"
            //   "status": null
            //   "scheduled_at": "2025-06-25T22:00:00.000000Z"
            //   "repeat": "none"
            //   "timezone": "America/New_York"
            //   "created_at": "2025-06-21T14:40:37.000000Z"
            //   "updated_at": "2025-06-21T18:22:21.000000Z"
            // }

            if (isset($data->scheduled_at)) {
                $timezone = $data->timezone ?? 'UTC';

                $dt = Carbon::parse($data->scheduled_at, 'UTC');
                $data->scheduled_at = $dt->setTimezone($timezone)->format('Y-m-d H:i:s');
            }
            elseif (isset($data->data) && is_array($data->data)) {
                foreach ($data->data as &$appointment) {
                    if (isset($appointment->scheduled_at)) {
                        $timezone = $appointment->timezone ?? 'UTC';
                        $dt = Carbon::parse((string) $appointment->scheduled_at)->setTimezone('UTC');
                        $appointment->scheduled_at = $dt->setTimezone($timezone)->format('Y-m-d H:i:s');
                    }
                }
            }

            $response->setData($data);
        }

        return $response;
    }
}
