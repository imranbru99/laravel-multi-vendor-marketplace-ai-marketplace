<?php

namespace App\Filament\ResourcePages;

use App\Domain\Audit;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->using(function (array $data) {
            $user = User::create(UserResource::validateData($data));
            Audit::record('user.created', (string) $user->id);

            return $user;
        })];
    }
}
