<?php

namespace App\Domain;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class Inventory
{
    public function change(InventoryBalance $balance, int $quantity, int $reserved, string $reason, string $reference): void
    {
        DB::transaction(function () use ($balance, $quantity, $reserved, $reason, $reference) {
            $balance = InventoryBalance::whereKey($balance->id)->lockForUpdate()->firstOrFail();
            if ($existing = InventoryMovement::where('reference', $reference)->first()) {
                if ($existing->inventory_balance_id !== $balance->id || $existing->quantity_delta !== $quantity || $existing->reserved_delta !== $reserved || $existing->reason !== $reason) {
                    throw ValidationException::withMessages(['reference' => 'This inventory reference was already used for a different movement.']);
                }

                return;
            }
            $newQuantity = $balance->quantity + $quantity;
            $newReserved = $balance->reserved + $reserved;
            if ($newQuantity < 0 || $newReserved < 0 || $newReserved > $newQuantity) {
                throw ValidationException::withMessages(['stock' => 'Insufficient available stock.']);
            }
            $balance->update(['quantity' => $newQuantity, 'reserved' => $newReserved]);
            InventoryMovement::create(['vendor_id' => $balance->vendor_id, 'inventory_balance_id' => $balance->id, 'quantity_delta' => $quantity, 'reserved_delta' => $reserved, 'reason' => $reason, 'reference' => $reference]);
        }, 3);
    }

    public function transfer(InventoryBalance $source, InventoryBalance $destination, int $quantity, string $reference): void
    {
        app(ModuleManager::class)->require('inventory');
        if ($quantity < 1 || $source->offer_id !== $destination->offer_id || $source->vendor_id !== $destination->vendor_id || $source->id === $destination->id) {
            throw ValidationException::withMessages(['stock' => 'Invalid transfer.']);
        }
        DB::transaction(function () use ($source, $destination, $quantity, $reference) {
            InventoryBalance::whereIn('id', [$source->id, $destination->id])->orderBy('id')->lockForUpdate()->get();
            $this->change($source, -$quantity, 0, 'transfer out', $reference.':out');
            $this->change($destination, $quantity, 0, 'transfer in', $reference.':in');
            Audit::record('inventory.transferred', $reference);
        }, 3);
    }
}
