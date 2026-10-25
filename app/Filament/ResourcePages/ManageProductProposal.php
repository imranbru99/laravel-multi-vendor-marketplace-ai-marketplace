<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\ProductProposalResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProductProposal extends ManageRecords
{
    protected static string $resource = ProductProposalResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
