<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ArrayHelper;

class ClientController extends Controller
{
    //
    public function index(Request $request)
    {
        $clients = $request->user()->clients()->get();

        return response()->json($clients, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (ArrayHelper::isAssoc($data)) {
            $data = [$data];
        }

        $createdClients = [];
        $errors = [];

        foreach ($data as $clientData) {
            $validator = validator($clientData, [
                'name' => 'required|string|max:255',
                'email' => "required|email|unique:clients,email",
                'phone' => 'nullable|string',
            ]);

            if($validator->fails()){
                $errors[] = [
                    'client' => $clientData,
                    'errors' => $validator->errors()->messages()
                ];
                continue;
            }

            $createdClients[] = $request->user()->clients()->create($validator->validated());
        }

        return response()->json([
            'created' => $createdClients,
            'errors' => $errors,
        ], Response::HTTP_CREATED);

    }

    public function show(Request $request, $id)
    {
        $client = $request->user()->clients()->where('id', $id)->first();

        if (! $client) {
            return response()->json(['message' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($client, Response::HTTP_OK);
    }

    public function delete(Request $request, $id)
    {
        $client = $request->user()->clients()->find($id);

        if (! $client) {
            return response()->json(['message' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $client->delete();

        return response()->json([
            'deleted' => $client
        ], Response::HTTP_OK);
    }
}
