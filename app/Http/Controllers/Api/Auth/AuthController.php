<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * @tags Authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Create a new user account and return access token.
     *
     * @group Authentication
     * @bodyParam name string required The user's name. Example: John Doe
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'team_id'  => 1, // Default team
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'team_id' => $user->team_id,
            ],
        ], 201);
    }

    /**
     * Login user
     *
     * Authenticate user credentials and return access token.
     *
     * @group Authentication
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'team_id' => $user->team_id,
            ],
        ]);
    }

    /**
     * Get authenticated user
     *
     * Retrieve the currently authenticated user's information.
     *
     * @group Authentication
     * @authenticated
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => [
                'id'      => $request->user()->id,
                'name'    => $request->user()->name,
                'email'   => $request->user()->email,
                'team_id' => $request->user()->team_id,
            ],
        ]);
    }

    /**
     * Logout user
     *
     * Revoke the current access token and logout the user.
     *
     * @group Authentication
     * @authenticated
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        // Check if it's a real token (not TransientToken used in tests)
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
