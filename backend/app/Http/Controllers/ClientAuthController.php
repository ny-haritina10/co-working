<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClientAuthService;

class ClientAuthController extends Controller
{
    protected $clientAuthService;

    public function __construct(ClientAuthService $clientAuthService)
    {
        $this->clientAuthService = $clientAuthService;
    }

    public function loginOrSignUp(Request $request)
    {
        $request->validate([
            'numero_client' => 'required|string|max:20',
        ]);

        $authData = $this->clientAuthService->loginOrSignUp($request->numero_client);

        return response()->json([
            'message' => 'Authentication successful',
            'client' => $authData['client'],
            'token' => $authData['token'],
        ]);
    }

    public function logout(Request $request)
    {
        $this->clientAuthService->logout($request);

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getAuthenticatedClient(Request $request)
    {
        return response()->json(['client' => $request->user()]);
    }
}