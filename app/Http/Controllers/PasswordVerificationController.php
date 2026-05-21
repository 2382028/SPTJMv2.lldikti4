<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PasswordVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $payload = $request->json()->all();

        $validator = Validator::make($payload, [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $password = (string)$payload['password'];

        $ok = false;
        $user = Auth::user();
        if ($user) {
            $ok = hash_equals((string) $user->password, (string) $password);
        }
        // Use shared admin confirmation password, defaulting to 'lldikti4' if not set in env
        $sharedPass = (string) env('ADMIN_CONFIRM_PASSWORD', 'lldikti4');
        if (!$ok && $sharedPass !== '') {
            $ok = hash_equals($sharedPass, $password);
        }

        if (!$ok) {
            return response()->json([
                'success' => false,
                'message' => 'Password tidak valid!',
            ], 401);
        }

        // Mark session (optional usage by subsequent operations)
        session(['password_verified' => now()->timestamp]);

        return response()->json([
            'success' => true,
        ]);
    }
}
