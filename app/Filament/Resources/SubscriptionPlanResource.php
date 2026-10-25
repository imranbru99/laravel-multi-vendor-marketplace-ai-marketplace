<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageSubscriptionPlans;
use App\Filament\Shared\OperationalResource;
use App\Models\SubscriptionPlan;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanResource extends OperationalResource
{
    protected static ?string $model = SubscriptionPlan::class;

    public static string $entity = 'SubscriptionPlan';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(100), TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true), TextInput::make('price_minor')->required()->integer()->minValue(0)->maxValue(1000000000), TextInput::make('currency')->required()->in(['USD', 'BDT', 'EUR', 'GBP'])->default('USD'), TextInput::make('period_days')->required()->integer()->minValue(1)->maxValue(366)->default(30),
            TextInput::make('limits.branch_limit')->required()->integer()->minValue(1), TextInput::make('limits.vendor_limit')->required()->integer()->minValue(1), TextInput::make('limits.warehouse_limit')->required()->integer()->minValue(1), TextInput::make('limits.product_limit')->required()->integer()->minValue(1), CheckboxList::make('modules')->options(fn () => array_map(fn ($m) => $m['name'], config('marketplace.modules')))->required()->columns(2), Toggle::make('active')->default(true),
        ])->columns(2);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSubscriptionPlans::route('/')];
    }
}
