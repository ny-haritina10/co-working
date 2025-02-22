<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientAuthService
{
    public function loginOrSignUp(string $numeroClient)
    {
        $client = Client::where('numero_client', $numeroClient)->first();

        if (!$client) {
            $client = Client::create([
                'numero_client' => $numeroClient,
                'name_client' => "Client " . (Client::max('id') + 1), // Generates "Client {id}"
            ]);
        }

        $token = $client->createToken('client-token')->plainTextToken;

        return [
            'client' => $client,
            'token' => $token,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
    }
}