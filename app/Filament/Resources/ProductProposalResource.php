<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageProductProposal;
use App\Filament\Shared\OperationalResource;
use App\Models\ProductProposal;
use Illuminate\Database\Eloquent\Model;

class ProductProposalResource extends OperationalResource
{
    protected static ?string $model = ProductProposal::class;

    public static string $entity = 'ProductProposal';

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && in_array($record->status, ['draft', 'rejected']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProductProposal::route('/')];
    }
}
