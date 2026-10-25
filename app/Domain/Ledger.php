<?php

namespace App\Domain;

use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;

final class Ledger
{
    /** Positive entries are debits; negative entries are credits. */
    public function post(string $reference, string $description, string $currency, array $entries): LedgerTransaction
    {
        if (array_sum(array_column($entries, 'amount_minor')) !== 0 || count($entries) < 2) {
            throw new \LogicException('Unbalanced journal.');
        }

        return DB::transaction(function () use ($reference, $description, $currency, $entries) {
            if ($existing = LedgerTransaction::where('reference', $reference)->first()) {
                return $existing;
            }
            $transaction = LedgerTransaction::create(compact('reference', 'description', 'currency'));
            foreach ($entries as $entry) {
                LedgerEntry::create($entry + ['ledger_transaction_id' => $transaction->id]);
            }

            return $transaction;
        }, 3);
    }
}
