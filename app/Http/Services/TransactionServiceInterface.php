<?php

namespace App\Http\Services;

interface TransactionServiceInterface
{
    public function createTransaction(array $data);
    public function getAllTransactions();
    public function getTransactionById(int $id);
    public function updateTransaction(int $id, array $data);
    public function deleteTransaction(int $id);
    public function getTransaction(string $id);
}
