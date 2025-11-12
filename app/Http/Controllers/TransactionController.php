<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
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
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $transactions = $this->transactionService->getAllTransactions();
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_source_id","compte_destination_id","montant"},
     *             @OA\Property(property="compte_source_id", type="integer"),
     *             @OA\Property(property="compte_destination_id", type="integer"),
     *             @OA\Property(property="montant", type="number"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="mode", type="string")
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
            'compte_source_id' => 'required|integer|exists:comptes,id',
            'compte_destination_id' => 'required|integer|exists:comptes,id|different:compte_source_id',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'string',
            'mode' => 'string',
        ]);

        $data['type'] = 'transfert';
        $data['status'] = 'pending';

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
     *             required={"compte_source_id","montant"},
     *             @OA\Property(property="compte_source_id", type="integer"),
     *             @OA\Property(property="montant", type="number"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="mode", type="string")
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
            'compte_source_id' => 'required|integer|exists:comptes,id',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'string',
            'mode' => 'string',
        ]);

        $data['type'] = 'retrait'; // Assuming payment is a withdrawal
        $data['status'] = 'pending';

        $transaction = $this->transactionService->createTransaction($data);

        return response()->json(['data' => $transaction], 201);
    }
}
