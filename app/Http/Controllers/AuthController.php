<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Http\Services\AuthServiceInterface;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for User Authentication"
 * )
 */
class AuthController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }
    /**
     * Register a new user.
     *
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","telephone","password"},
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="status", type="string", enum={"client","admin"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => $data['status'] ?? 'client',
        ]);

        return response()->json([
            'data' => $user,
            'message' => 'User registered successfully'
        ], 201);
    }

    /**
     * Login using Sanctum - now generates OTP.
     *
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login and generate OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone","password"},
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'telephone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = $this->authService->attemptLogin($request->telephone, $request->password);
        if (!$user) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        $this->authService->generateOTP($user);

        return response()->json([
            'message' => 'OTP sent successfully',
        ]);
    }

    /**
     * Verify OTP and issue token.
     *
     * @OA\Post(
     *     path="/api/auth/verify-otp",
     *     summary="Verify OTP and get token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone","otp"},
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="otp", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token issued",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string")
     *         )
     *     )
     * )
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        $request->validate([
            'telephone' => 'required|string',
            'otp' => 'required|string',
        ]);

        $user = $this->authService->findUserByTelephone($request->telephone);
        if (!$user) {
            return response()->json(['error' => 'user_not_found'], 404);
        }

        if (!$this->authService->verifyOTP($user, $request->otp)) {
            return response()->json(['error' => 'invalid_otp'], 401);
        }

        $token = $user->createToken('API Token');

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Refresh OTP for a user.
     *
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone"},
     *             @OA\Property(property="telephone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'telephone' => 'required|string',
        ]);

        $user = $this->authService->findUserByTelephone($request->telephone);
        if (!$user) {
            return response()->json(['error' => 'user_not_found'], 404);
        }

        $this->authService->generateOTP($user);

        return response()->json([
            'message' => 'OTP refreshed successfully',
        ]);
    }

    /**
     * Logout: revoke user's token.
     *
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="logged_out", type="boolean")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current access token
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['logged_out' => true]);
    }
}
