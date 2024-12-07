<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Throwable;

class AuthController extends Controller
{
    /**
     * Register a new user and generate an access token.
     *
     * @group Authentication
     *
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The expected response format. Example: v1
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: johndoe@example.com
     * @bodyParam password string required The password of the user (must be at least 6 characters). Example: password123
     * @bodyParam password_confirmation string required The password confirmation. Example: password123
     *
     * @response 201 {
     *   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "token_type": "Bearer"
     * }
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": {
     *       "email": ["The email has already been taken."]
     *   }
     * }
     * @response 500 {
     *   "message": "An error occurred during registration.",
     *   "error": "Optional debug error message (if app.debug=true)."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validateRegisterRequest($request);

            $user = $this->createUser($validatedData);
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (ValidationException $exception) {
            return $this->handleValidationException($exception);
        } catch (Throwable $exception) {
            return $this->handleGeneralException($exception, 'An error occurred during registration.');
        }
    }

    /**
     * Log in and generate an access token.
     *
     * @group Authentication
     *
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The expected response format. Example: v1
     *
     * @bodyParam email string required The email of the user. Example: johndoe@example.com
     * @bodyParam password string required The password of the user. Example: password123
     *
     * @response 200 {
     *   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "token_type": "Bearer"
     * }
     * @response 401 {
     *   "message": "Invalid credentials."
     * }
     * @response 500 {
     *   "message": "An error occurred during login.",
     *   "error": "Optional debug error message (if app.debug=true)."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validateLoginRequest($request);

            if (!$this->attemptLogin($validatedData)) {
                return $this->errorResponse('Invalid credentials.', 401);
            }

            /** @var User $user */
            $user = Auth::user();
            $this->revokeExistingTokens($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (ValidationException $exception) {
            return $this->handleValidationException($exception);
        } catch (Throwable $exception) {
            return $this->handleGeneralException($exception, 'An error occurred during login.');
        }
    }

    /**
     * Validate registration request.
     *
     * @param Request $request
     * @return array
     */
    private function validateRegisterRequest(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    private function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Validate login request.
     *
     * @param Request $request
     * @return array
     */
    private function validateLoginRequest(Request $request): array
    {
        return $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to authenticate a user.
     *
     * @param array $credentials
     * @return bool
     */
    private function attemptLogin(array $credentials): bool
    {
        return Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);
    }

    /**
     * Revoke all existing tokens for a user.
     *
     * @param User $user
     * @return void
     */
    private function revokeExistingTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Return a success JSON response.
     *
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    private function successResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Return a validation error JSON response.
     *
     * @param ValidationException $exception
     * @return JsonResponse
     */
    private function handleValidationException(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $exception->errors(),
        ], 422);
    }

    /**
     * Return a general error JSON response.
     *
     * @param Throwable $exception
     * @param string $message
     * @return JsonResponse
     */
    private function handleGeneralException(Throwable $exception, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => config('app.debug') ? $exception->getMessage() : null,
        ], 500);
    }

    /**
     * Return a generic error JSON response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json(['message' => $message], $statusCode);
    }
}
