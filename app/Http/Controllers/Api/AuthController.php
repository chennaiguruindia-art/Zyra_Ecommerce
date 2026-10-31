<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Support\ProductImageStorage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Customer registration for mobile apps.
     */
    public function register(Request $request): JsonResponse
    {
        $reservedEmails = ['order@shopwithzyra.in'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                'unique:'.User::class,
                function ($attribute, $value, $fail) use ($reservedEmails) {
                    if (in_array(strtolower($value), $reservedEmails)) {
                        $fail('This email is reserved and cannot be used for registration.');
                    }
                },
            ],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:'.User::class],
            'password' => ['required', 'min:8'],
            'address' => ['nullable', 'string', 'max:500'],
            'nearby_area' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'digits:6'],
            'state' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:customer,seller'],
        ]);

        $token = Str::random(60);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'original_password' => $validated['password'],
            'address' => $validated['address'] ?? null,
            'nearby_area' => $validated['nearby_area'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'state' => $validated['state'] ?? null,
            'district' => $validated['district'] ?? null,
            'role' => ($validated['role'] ?? 'customer') === 'seller' ? 'seller' : 'customer',
            'remember_token' => $token,
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::warning('Registered event failed', ['error' => $e->getMessage()]);
        }

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Throwable $e) {
            Log::warning('Welcome email failed', ['user' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account registered successfully.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    /**
     * Login using email (or phone number) and password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('login') ?: $request->input('email');
        if (!$loginInput) {
            return response()->json([
                'success' => false,
                'message' => 'Email or phone number is required.',
            ], 422);
        }

        $user = User::query()
            ->where('email', $loginInput)
            ->orWhere('phone_number', $loginInput)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Get authenticated customer profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'required', 'string', 'regex:/^[0-9]{10}$/', 'unique:users,phone_number,'.$user->id],
            'address' => ['nullable', 'string', 'max:500'],
            'nearby_area' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'digits:6'],
            'state' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
            if ($validated['avatar']) {
                ProductImageStorage::publish($validated['avatar']);
            }
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password does not match.',
            ], 422);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->original_password = $request->input('new_password');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Logout and revoke session token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->remember_token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Request a password reset link.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => __($status),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }

    /**
     * Format user data safely for API response.
     */
    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'address' => $user->address,
            'nearby_area' => $user->nearby_area,
            'pincode' => $user->pincode,
            'state' => $user->state,
            'district' => $user->district,
            'role' => $user->role ?? 'customer',
            'is_seller' => $user->isSeller(),
            'avatar_url' => $user->avatar ? ProductImageStorage::url($user->avatar) : null,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
