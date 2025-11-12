<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompteRequest;
use App\Models\Compte;
use App\Models\CodeSecret;
use App\Events\ClientAccountCreated;
use App\Http\Services\CompteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="MOMPAY API",
 *     version="1.0.0",
 *     description="API pour le système de paiement mobile MOMPAY"
 * )
 */
class CompteController extends Controller
{
    protected CompteServiceInterface $compteService;

    public function __construct(CompteServiceInterface $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * Display a listing of comptes.
     *
     * @OA\Get(
     *     path="/api/comptes",
     *     summary="Get all comptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of comptes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $comptes = $this->compteService->getAllComptes();
        return response()->json(['data' => $comptes]);
    }

    /**
     * Display the specified compte.
     *
     * @OA\Get(
     *     path="/api/comptes/{id}",
     *     summary="Get a specific compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $compte = $this->compteService->getCompteById($id);
        return response()->json(['data' => $compte]);
    }

    /**
     * Update the specified compte.
     *
     * @OA\Put(
     *     path="/api/comptes/{id}",
     *     summary="Update a compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="solde", type="number"),
     *             @OA\Property(property="status", type="string", enum={"actif","inactif"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'solde' => 'numeric',
            'status' => 'string|in:actif,inactif',
        ]);

        $compte = $this->compteService->updateCompte($id, $data);
        return response()->json(['data' => $compte]);
    }

    /**
     * Remove the specified compte.
     *
     * @OA\Delete(
     *     path="/api/comptes/{id}",
     *     summary="Delete a compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $this->compteService->deleteCompte($id);
        return response()->json(['message' => 'Compte deleted successfully']);
    }

    /**
     * Create a new compte for a user.
     *
     * @OA\Post(
     *     path="/api/comptes",
     *     summary="Create a new compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="solde", type="number", example=1000.00),
     *             @OA\Property(property="status", type="string", enum={"actif","inactif"}, example="actif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="solde", type="number"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(StoreCompteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $compte = Compte::create([
            'user_id' => $data['user_id'],
            'solde' => $data['solde'] ?? 0,
            'status' => $data['status'] ?? 'actif',
        ]);

        // create a code secret for first connection
        $codePlain = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $codeSecret = CodeSecret::create([
            'user_id' => $compte->user_id,
            'code' => bcrypt($codePlain),
            'is_active' => true,
        ]);

        // Dispatch domain event so listeners can send SMS/email
        event(new ClientAccountCreated($compte->user, $compte, $codePlain));

        return response()->json([
            'data' => $compte,
        ], 201);
    }
}
