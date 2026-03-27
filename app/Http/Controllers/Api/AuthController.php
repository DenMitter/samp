<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $plainTextToken = Str::random(80);

        $user = User::create([
            ...$data,
            'api_token' => hash('sha256', $plainTextToken),
        ]);

        return $this->jsonWithAuthCookie($request, [
            'message' => 'User registered successfully.',
            'user' => $this->userPayload($user),
            'token' => $plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! password_verify($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $plainTextToken = Str::random(80);

        $user->forceFill([
            'api_token' => hash('sha256', $plainTextToken),
        ])->save();

        return $this->jsonWithAuthCookie($request, [
            'message' => 'Logged in successfully.',
            'user' => $this->userPayload($user),
            'token' => $plainTextToken,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->forceFill([
            'api_token' => null,
        ])->save();

        return response()->json([
            'message' => 'Logged out successfully.',
        ])->withoutCookie($this->authCookieName(), '/', config('session.domain'));
    }

    private function userPayload(User $user): array
    {
        return [
            ...$user->toArray(),
            'is_admin' => $this->isAdminEmail($user->email),
        ];
    }

    private function isAdminEmail(string $email): bool
    {
        $allowedEmails = collect(explode(',', (string) env('ADMIN_EMAILS', 'admin@admin.com')))
            ->map(fn (string $item) => mb_strtolower(trim($item)))
            ->filter()
            ->values();

        return $allowedEmails->contains(mb_strtolower(trim($email)));
    }

    private function jsonWithAuthCookie(Request $request, array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status)->cookie($this->makeAuthCookie($request, $payload['token']));
    }

    private function makeAuthCookie(Request $request, string $token): Cookie
    {
        return cookie(
            $this->authCookieName(),
            $token,
            60 * 24 * 30,
            '/',
            config('session.domain'),
            $request->isSecure(),
            true,
            false,
            'lax'
        );
    }

    private function authCookieName(): string
    {
        return 'escrow_mvp_auth';
    }
}
