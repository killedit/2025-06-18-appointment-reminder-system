````markdown
# Appointment Scheduling API

This is a RESTful API built with Laravel for managing appointments and reminders between clients and service providers, i.e. users. It supports time zone-aware scheduling, automated reminders, and recurring appointments.

## 🚀 Features

- User registration & login (Sanctum token-based auth)
- Create, update, delete appointments
- Schedule automated simulation reminders with offsets (e.g. -1 hour, -1 day)
- Supports recurring appointments (weekly, monthly)
- Time zone conversion middleware
- Reminder queue job system

---

## 🛠️ Setup

```bash
git clone https://github.com/killedit/2025-06-18-appointment-reminder-system.git
cd 2025-06-18-appointment-reminder-system

composer install
php artisan key:generate
php artisan migrate
php artisan serve
````

To run jobs:

```bash
php artisan queue:work
```

---

## 🔐 Authentication

This API uses [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum) for token-based authentication.

### RegisterUser

**POST** `/api/register`

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### GenerateUserToken

**POST** `/api/getToken`

```json
{
  "email": "john@example.com",
  "password": "password"
}
```

Returns:

```json
{
    "access_token": "10|nyotofKWPLYtHh61ATrpX5no62TVPGYm9Mjazid91ecfb664",
    "token_type": "Bearer",
    "expires_in": 900
}
```

Pass the token in the `Authorization` header:

```
Authorization: Bearer {{access_token}}
```

---

## 📚 Endpoints

### Users

| Method | URI           | Controller                           | Description              |
| ------ | ------------- | ------------------------------------ | ------------------------ |
| POST   | /api/register | RegisteredUserController\@store      | Register new user        |
| POST   | /api/getToken | RegisteredUserController\@getToken   | Login and get auth token |

### Clients

| Method | URI               | Controller                           | Description              |
| ------ | ----------------- | ------------------------------------ | ------------------------ |
| POST   | /api/clients      | ClientController\@store              | Create a new client/s    |
| GET    | /api/clients      | ClientController\@index              | List all clients per user|
| GET    | /api/clients{id}  | ClientController\@show               | List a client by id      |
| DELETE | /api/clients{id}  | ClientController\@delete             | Delete a client by id    |

### Appointments

| Method | URI                           | Controller                          | Description                            |
| ------ | ----------------------------- | ----------------------------------- | -------------------------------------- |
| GET    | /api/appointments             | AppointmentController\@index        | List all appointments for current user |
| GET    | /api/appointments/{id}        | AppointmentController\@show         | Get a single appointment               |
| POST   | /api/appointments             | AppointmentController\@store        | Create appointment (handles timezones) |
| PUT    | /api/appointments/{id}        | AppointmentController\@update       | Update appointment                     |
| DELETE | /api/appointments/{id}        | AppointmentController\@delete       | Delete appointment                     |
| POST   | /api/appointments/{id}/status | AppointmentStatusController\@update | Update status of appointment           |

### Reminders

| Method | URI                              | Controller                | Description                             |
| ------ | -------------------------------- | ------------------------- | --------------------------------------- |
| GET    | /api/appointments/{id}/reminders | ReminderController\@index | View reminders linked to an appointment |

---

## 🧪 Sample Appointment Creation

**POST** `/api/appointments`

```json
[
  {
    "client_id": 1,
    "scheduled_at": "2025-06-25T14:00:00",
    "notes": "Quarterly review meeting",
    "repeat": "none",
    "timezone": "America/New_York"
  },
  {
    "client_id": 2,
    "scheduled_at": "2025-06-26T09:30:00",
    "notes": "Initial consultation",
    "repeat": "weekly",
    "timezone": "Europe/London"
  }
]
```

---

## 🕓 Reminder Scheduling Logic

Reminders are automatically created for each appointment using configured offsets in `config/reminders.php`. Example:

```php
'default_offsets' => [
    '-1 day',
    '-1 hour',
],
```

If a calculated `scheduled_for` time is already in the past, that reminder will **not be created**.

---

## 🧰 Technologies Used

* PHP 8.3 / Laravel 11
* MySQL
* Laravel Sanctum
* Laravel Queues (jobs & workers)
* Carbon (date/time parsing and timezone handling)

---

## 🔗 Postman Collection

You can test the API using Postman:

- 📁 [Download Postman Collection](postman/2025-06-18-appointment-reminder-system.postman_collection.json)
- 🌐 [Download Environment File](postman/2025-06-18-appointment-reminder-system.postman_environment.json)

> Import both into Postman to get started quickly.

```
