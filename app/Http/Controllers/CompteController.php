<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompteRequest;
use App\Models\Compte;
use App\Models\CodeSecret;
use App\Models\Operation;
use App\Models\User;
use App\Events\ClientAccountCreated;
use App\Http\Services\CompteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="OMPAY API",
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

    public function index(): JsonResponse
    {
        $comptes = $this->compteService->getAllComptes();
        return response()->json(['data' => $comptes]);
    }

    public function show(string $id): JsonResponse
    {
        $compte = $this->compteService->getCompteById($id);
        return response()->json(['data' => $compte]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'solde' => 'numeric',
            'status' => 'string|in:actif,inactif',
        ]);

        $compte = $this->compteService->updateCompte($id, $data);
        return response()->json(['data' => $compte]);
    }

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
     *             @OA\Property(property="user_id", type="string", example="uuid"),
     *             @OA\Property(property="solde", type="number", example=1000.00),
     *             @OA\Property(property="status", type="string", enum={"actif","inactif"}, example="actif"),
     *             @OA\Property(property="type", type="string", enum={"client","marchand"}, example="client"),
     *             @OA\Property(property="code_marchand", type="string", example="MARCHAND123")
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

        $type = $data['type'] ?? 'client';
        $codeMarchand = ($type === 'marchand') ? ($data['code_marchand'] ?? null) : null;

        $compte = Compte::create([
            'user_id' => $data['user_id'],
            'solde' => $data['solde'] ?? 0,
            'status' => $data['status'] ?? 'actif',
            'type' => $type,
            'code_marchand' => $codeMarchand,
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

    /**
     * Get the solde of a compte by numero_compte.
     *
     * @OA\Get(
     *     path="/api/compte/{id}/solde",
     *     summary="Get compte solde by numero_compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Numero du compte",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte solde",
     *         @OA\JsonContent(
     *             @OA\Property(property="solde", type="number")
     *         )
     *     )
     * )
     */
    public function getSolde(string $id): JsonResponse
    {
        $compte = $this->compteService->getCompteByNumero($id);
        if (!$compte) {
            return response()->json(['error' => 'Compte not found'], 404);
        }
        return response()->json(['solde' => $compte->solde]);
    }

    /**
     * Perform a transfer from the compte.
     *
     * @OA\Post(
     *     path="/api/compte/{id}/transfert",
     *     summary="Transfer money from compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero","montant"},
     *             @OA\Property(property="numero", type="string"),
     *             @OA\Property(property="montant", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function transfert(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'numero' => 'required|string',
            'montant' => 'required|numeric|min:0.01',
        ]);

        $sourceCompte = $this->compteService->getCompteById($id);

        // Check balance
        if ($sourceCompte->solde < $data['montant']) {
            return response()->json(['error' => 'insufficient_balance'], 400);
        }

        // Find destination user by telephone
        $destUser = User::where('telephone', $data['numero'])->first();
        if (!$destUser) {
            return response()->json(['error' => 'destination_not_found'], 404);
        }

        $destCompte = $destUser->compte;
        if (!$destCompte) {
            return response()->json(['error' => 'destination_compte_not_found'], 404);
        }

        // Perform transfer
        DB::transaction(function () use ($sourceCompte, $destCompte, $data) {
            $sourceCompte->decrement('solde', $data['montant']);
            $destCompte->increment('solde', $data['montant']);

            // Create operations
            Operation::create([
                'compte_id' => $sourceCompte->id,
                'type' => 'debit',
                'montant' => $data['montant'],
                'description' => 'Transfert sortant',
                'destination_compte_id' => $destCompte->id,
            ]);

            Operation::create([
                'compte_id' => $destCompte->id,
                'type' => 'credit',
                'montant' => $data['montant'],
                'description' => 'Transfert entrant',
                'destination_compte_id' => $sourceCompte->id,
            ]);
        });

        return response()->json(['message' => 'Transfer successful']);
    }

    /**
     * Perform a payment from the compte.
     *
     * @OA\Post(
     *     path="/api/compte/{id}/paiement",
     *     summary="Make a payment from compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant"},
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="code_marchant", type="string"),
     *             @OA\Property(property="montant", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function paiement(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'telephone' => 'nullable|string',
            'code_marchant' => 'nullable|string',
            'montant' => 'required|numeric|min:0.01',
        ]);

        if (!$data['telephone'] && !$data['code_marchant']) {
            return response()->json(['error' => 'telephone_or_code_marchant_required'], 400);
        }

        $compte = $this->compteService->getCompteById($id);

        // Check balance
        if ($compte->solde < $data['montant']) {
            return response()->json(['error' => 'insufficient_balance'], 400);
        }

        // Perform payment
        DB::transaction(function () use ($compte, $data) {
            $compte->decrement('solde', $data['montant']);

            // Create operation
            Operation::create([
                'compte_id' => $compte->id,
                'type' => 'paiement',
                'montant' => $data['montant'],
                'description' => 'Paiement ' . ($data['telephone'] ? 'téléphone' : 'marchant'),
            ]);
        });

        return response()->json(['message' => 'Payment successful']);
    }

    /**
     * Get transactions for the compte.
     *
     * @OA\Get(
     *     path="/api/compte/{id}/transaction",
     *     summary="Get compte transactions",
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
     *         description="List of transactions",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function getTransactions(string $id): JsonResponse
    {
        $compte = $this->compteService->getCompteById($id);
        $operations = $compte->operations()->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $operations]);
    }

    /**
     * Get compte dashboard for authenticated user.
     *
     * @OA\Get(
     *     path="/api/compte/dashboard",
     *     summary="Get compte dashboard",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="compte", type="object")
     *         )
     *     )
     * )
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user()->load('compte');

        return response()->json([
            'user' => $user,
            'compte' => $user->compte,
        ]);
    }
}
