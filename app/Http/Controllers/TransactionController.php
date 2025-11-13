<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Http\Services\TransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="API Endpoints for Transactions"
 * )
 */
class TransactionController extends Controller
{
    protected TransactionServiceInterface $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions.
     *
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="Get all transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="montant", type="string", example="+100.00")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $transactions = $this->transactionService->getAllTransactions();

        $transactions = $transactions->map(function ($transaction) {
            $montant = $transaction->montant;
            if ($transaction->type === 'depot') {
                $montant = '+' . $montant;
            } elseif (in_array($transaction->type, ['retrait', 'paiement'])) {
                $montant = '-' . $montant;
            }
            $transaction->montant = $montant;
            return $transaction;
        });

        return response()->json(['data' => $transactions]);
    }





   /**
    * Perform a transfer transaction.
    *
    * @OA\Post(
    *     path="/api/transactions/transfer",
    *     summary="Perform a transfer",
    *     tags={"Transactions"},
    *     security={{"bearerAuth":{}}},
    * @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"numero","montant"},
    *             @OA\Property(property="numero", type="string", example="771234567"),
    *             @OA\Property(property="montant", type="number", example=100.00)
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Transfer completed",
    *         @OA\JsonContent(
    *             @OA\Property(property="data", type="object")
    *         )
    *     )
    * )
    */
   public function transfer(Request $request): JsonResponse
   {
       $data = $request->validate([
           'numero' => 'required|string',
           'montant' => 'required|numeric|min:0.01',
           'description' => 'string',
           'mode' => 'string',
       ]);

       $sourceCompte = $request->user()->compte;
       if (!$sourceCompte) {
           return response()->json(['error' => 'source_compte_not_found'], 404);
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

       if ($sourceCompte->id === $destCompte->id) {
           return response()->json(['error' => 'cannot_transfer_to_self'], 400);
       }

       $data['compte_source_id'] = $sourceCompte->id;
       $data['compte_destination_id'] = $destCompte->id;
       $data['type'] = 'transfert';
       $data['status'] = 'completed';

       $transaction = $this->transactionService->createTransaction($data);

       return response()->json(['data' => $transaction], 201);
   }

    /**
     * Perform a payment transaction.
     *
     * @OA\Post(
     *     path="/api/transactions/payment",
     *     summary="Perform a payment",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero","montant"},
     *             @OA\Property(property="numero", type="string", description="Numéro de téléphone ou code marchand"),
     *             @OA\Property(property="montant", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function payment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'numero' => 'required|string',
            'montant' => 'required|numeric|min:0.01',
        ]);

        $sourceCompte = $request->user()->compte;
        if (!$sourceCompte) {
            return response()->json(['error' => 'source_compte_not_found'], 404);
        }

        if ($sourceCompte->solde < $data['montant']) {
            return response()->json(['error' => 'insufficient_balance'], 400);
        }

        // Debit the source compte
        $sourceCompte->decrement('solde', $data['montant']);

        $data['compte_source_id'] = $sourceCompte->id;
        $data['type'] = 'paiement';
        $data['status'] = 'completed';

        $transaction = $this->transactionService->createTransaction($data);

        return response()->json(['data' => $transaction], 201);
    }
}
