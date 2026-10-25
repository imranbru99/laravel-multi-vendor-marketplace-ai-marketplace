<?php

namespace App\Filament\Resources;

use App\Domain\AccessManager;
use App\Domain\Audit;
use App\Domain\TenantContext;
use App\Filament\ResourcePages\ManageUsers;
use App\Models\Branch;
use App\Models\User;
use App\Models\Vendor;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'owner', 'admin'], true) && app(AccessManager::class)->allows(auth()->user(), 'users.manage');
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', app(TenantContext::class)->id())->when(auth()->user()?->role !== 'superadmin', fn ($q) => $q->whereNotIn('role', ['superadmin', 'owner']));
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny() && $record->id !== auth()->id() && static::getEloquentQuery()->whereKey($record->id)->exists();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(100), TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')->password()->minLength(12)->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state)),
            Select::make('role')->options(array_combine($roles = auth()->user()?->role === 'superadmin' ? ['owner', 'admin', 'vendor', 'manager', 'branch', 'warehouse', 'finance', 'support', 'customer', 'business', 'staff'] : ['admin', 'vendor', 'manager', 'branch', 'warehouse', 'finance', 'support', 'customer', 'business', 'staff'], $roles))->required(),
            Select::make('vendor_id')->options(fn () => Vendor::where('status', 'approved')->pluck('name', 'id'))->searchable(),
            Select::make('branch_id')->options(fn () => Branch::pluck('name', 'id'))->searchable(),
        ])->columns(2);
    }

    public static function validateData(array $data): array
    {
        $data['tenant_id'] = app(TenantContext::class)->id();
        if ($data['role'] === 'staff') {
            if (! empty($data['vendor_id']) && ! Vendor::whereKey($data['vendor_id'])->where('status', 'approved')->exists()) {
                throw ValidationException::withMessages(['vendor_id' => 'Choose an approved seller.']);
            }
            if (! empty($data['branch_id']) && (! isset($data['vendor_id']) || ! Branch::whereKey($data['branch_id'])->where('vendor_id', $data['vendor_id'])->exists())) {
                throw ValidationException::withMessages(['branch_id' => 'Choose a branch belonging to the selected seller.']);
            }

            return $data;
        }
        if (in_array($data['role'], ['vendor', 'manager', 'branch', 'warehouse'])) {
            abort_unless(isset($data['vendor_id']) && Vendor::whereKey($data['vendor_id'])->where('status', 'approved')->exists(), 422, 'An approved vendor is required.');
        } else {
            $data['vendor_id'] = null;
            $data['branch_id'] = null;
        }
        if (in_array($data['role'], ['branch', 'warehouse'])) {
            abort_unless(isset($data['branch_id']) && Branch::whereKey($data['branch_id'])->where('vendor_id', $data['vendor_id'])->exists(), 422, 'A matching branch is required.');
        } else {
            $data['branch_id'] = null;
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('email')->searchable(), TextColumn::make('role')->badge(), TextColumn::make('vendor_id'), TextColumn::make('branch_id')])->recordActions([EditAction::make()->using(function (User $record, array $data) {
            abort_unless(static::canEdit($record), 403);
            $record->update(static::validateData($data));
            Audit::record('user.updated', (string) $record->id);

            return $record;
        })]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
