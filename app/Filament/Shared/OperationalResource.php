<?php

namespace App\Filament\Shared;

use App\Domain\AccessManager;
use App\Domain\Audit;
use App\Domain\BusinessRules;
use App\Domain\CatalogImporter;
use App\Domain\CatalogPublishing;
use App\Domain\Inventory;
use App\Domain\ModuleManager;
use App\Domain\Money;
use App\Domain\OperationsAccess;
use App\Domain\OrderWorkflow;
use App\Domain\Subscriptions;
use App\Domain\TenantContext;
use App\Models\AiDraft;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class OperationalResource extends Resource
{
    public static string $entity;

    public static function definition(): array
    {
        return config('resources.'.static::$entity);
    }

    public static function getNavigationGroup(): ?string
    {
        return static::definition()['group'];
    }

    public static function getEloquentQuery(): Builder
    {
        return OperationsAccess::scope(parent::getEloquentQuery(), static::$entity, auth()->user());
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user || ! app(AccessManager::class)->entity($user, static::$entity)) {
            return false;
        }
        $module = static::definition()['module'];

        return ! $module || app(ModuleManager::class)->enabled($module);
    }

    public static function canCreate(): bool
    {
        return auth()->check() && ! auth()->user()?->branch_id && app(AccessManager::class)->entity(auth()->user(), static::$entity, 'create') && static::canViewAny() && count(static::definition()['fields']) > 0 && ! in_array(static::$entity, ['SupportTicket', 'Quotation']) && (static::$entity !== 'Vendor' || auth()->user()?->managesMarketplace());
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && ! auth()->user()?->branch_id && app(AccessManager::class)->entity(auth()->user(), static::$entity, 'update') && static::canViewAny() && count(static::definition()['fields']) > 0 && static::getEloquentQuery()->whereKey($record->id)->exists() && ! in_array(static::$entity, ['InventoryBalance', 'PurchaseOrder', 'DigitalAsset', 'CommissionRule', 'TaxRule']);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        $components = [];
        foreach (static::definition()['fields'] as $name => $type) {
            $optional = str_ends_with($type, '?');
            $type = rtrim($type, '?');
            if (str_starts_with($type, 'relation:')) {
                [, $model,$label] = explode(':', $type);
                $field = Select::make($name)->options(function () use ($model, $label) {
                    $class = 'App\\Models\\'.$model;
                    $query = $class::query();
                    if ($model === 'User') {
                        $query->where('tenant_id', app(TenantContext::class)->id());
                    }
                    $user = auth()->user();
                    if ($user?->vendor_id) {
                        $model === 'Vendor' ? $query->whereKey($user->vendor_id) : (\Illuminate\Support\Facades\Schema::hasColumn((new $class)->getTable(), 'vendor_id') ? $query->where('vendor_id', $user->vendor_id) : null);
                    }

                    return $query->limit(1000)->pluck($label, 'id');
                })->searchable()->native(false);
            } elseif (str_starts_with($type, 'select:')) {
                $values = explode('|', substr($type, 7));
                if (static::$entity === 'Quotation' && $name === 'status') {
                    $values = ['requested', 'quoted', 'rejected', 'closed'];
                } $field = Select::make($name)->options(array_combine($values, $values))->default($values[0]);
                // Seller cannot approve its own store.
                if ($name === 'status' && static::$entity === 'Vendor' && ! auth()->user()?->managesMarketplace()) {
                    $field->disabled()->dehydrated(false);
                }
            } else {
                $field = match ($type) {
                    'gallery' => FileUpload::make($name)->disk('public')->directory('tenants/'.app(TenantContext::class)->id().'/catalog')->visibility('public')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(2048)->multiple()->maxFiles(8),
                    'keyvalue' => KeyValue::make($name),
                    'proposal_image' => FileUpload::make($name)->disk('local')->directory('tenants/'.app(TenantContext::class)->id().'/proposals')->visibility('private')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(2048),
                    'csv' => FileUpload::make($name)->disk('local')->directory('tenants/'.app(TenantContext::class)->id().'/imports')->visibility('private')->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])->maxSize(2048),
                    'permissions' => CheckboxList::make($name)->options(fn () => app(AccessManager::class)->registry())->searchable()->columns(2),
                    'file' => FileUpload::make($name)->disk('local')->directory('tenants/'.app(TenantContext::class)->id().'/digital')->visibility('private')->acceptedFileTypes(['application/pdf', 'application/zip', 'text/plain'])->maxSize(10240),
                    'rights' => Toggle::make($name)->label('I have the right to distribute this content')->accepted(),
                    'long' => Textarea::make($name)->rows(4)->maxLength(10000),
                    'number' => TextInput::make($name)->integer()->minValue(0)->maxValue(str_ends_with($name, 'bps') ? 10000 : 1000000000),
                    'bool' => Toggle::make($name)->default(true),
                    'date' => DateTimePicker::make($name),
                    default => TextInput::make($name)->maxLength(255),
                };
                if ($name === 'email') {
                    $field->email();
                }
                if ($name === 'quantity') {
                    $field->minValue(1);
                }
                if ($name === 'accent') {
                    $field->default('#e8e6df')->regex('/^#[0-9a-fA-F]{6}$/');
                }
            }
            if ($name === 'commission_bps' && ! auth()->user()?->managesMarketplace()) {
                $field->disabled()->dehydrated(false);
            }
            if (in_array($name, ['slug', 'sku', 'code'])) {
                $field->unique(table: static::getModel(), column: $name, ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', app(TenantContext::class)->id()));
                if ($name === 'slug') {
                    $field->regex('/^[\\pL\\pN\\pM_-]+$/u');
                }
                if ($name === 'code') {
                    $field->regex('/^[A-Z0-9_-]+$/');
                }
            }
            if (str_ends_with($name, '_id')) {
                $field->label(Str::headline(substr($name, 0, -3)));
            }
            if (str_ends_with($name, '_minor')) {
                $field->label(Str::headline(substr($name, 0, -6)).' (minor units)')->helperText('100 minor units = 1 currency unit. Currency: '.app(TenantContext::class)->get()->currency);
            }
            $components[] = $field->required(! $optional);
        }

        return $schema->components($components)->columns(2);
    }

    public static function creationActions(string $resource): array
    {
        if (! $resource::canCreate()) {
            return [];
        }

        return [CreateAction::make()->using(function (array $data) use ($resource) {
            abort_unless($resource::canCreate(), 403);
            $resource::guardMutation($data);
            if ($resource::$entity === 'CatalogImport') {
                $data['user_id'] = auth()->id();
            }
            if ($resource::$entity === 'AccessRole') {
                app(AccessManager::class)->validateRole($data);
            }
            $operation = function () use ($resource, $data) {
                $class = $resource::getModel();
                Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
                app(Subscriptions::class)->quota($resource::$entity);
                if ($resource::$entity === 'RoleAssignment') {
                    return app(AccessManager::class)->assign($data);
                }
                $record = $resource::$entity === 'Branch' ? app(BusinessRules::class)->branch($data) : $class::create($data);
                Audit::record('record.created', $resource::$entity.':'.$record->id);

                return $record;
            };
            $module = $resource::definition()['module'];

            return $module ? app(ModuleManager::class)->transaction($module, $operation) : DB::transaction($operation);
        })];
    }

    public static function guardMutation(array $data): void
    {
        if (auth()->user()?->vendor_id && isset($data['vendor_id']) && (int) $data['vendor_id'] !== auth()->user()->vendor_id) {
            throw ValidationException::withMessages(['vendor_id' => 'Access to another seller is forbidden.']);
        }
    }

    public static function table(Table $table): Table
    {
        $columns = [];
        foreach (static::definition()['columns'] as $column) {
            $field = TextColumn::make($column)->sortable()->limit(65);
            if (! str_ends_with($column, 'minor') && ! in_array($column, ['quantity', 'reserved', 'used', 'rating', 'errors'])) {
                $field->searchable();
            }
            if ($column === 'status') {
                $field->badge();
            }
            if (str_ends_with($column, 'minor')) {
                $field->formatStateUsing(fn ($state) => Money::format((int) $state, app(TenantContext::class)->get()->currency));
            }
            $columns[] = $field;
        }
        $actions = [];
        if (count(static::definition()['fields'])) {
            $actions[] = EditAction::make()->visible(fn (Model $record) => static::canEdit($record))->using(function (Model $record, array $data) {
                abort_unless(static::canEdit($record), 403);
                static::guardMutation($data);
                $operation = function () use ($record, $data) {
                    if (static::$entity === 'AccessRole') {
                        app(AccessManager::class)->validateRole(array_merge($record->toArray(), $data));
                    }
                    $record->update($data);
                    app(AccessManager::class)->clear();

                    return $record;
                };
                $module = static::definition()['module'];

                return $module ? app(ModuleManager::class)->transaction($module, $operation) : DB::transaction($operation);
            })->after(fn (Model $record) => Audit::record('record.updated', static::$entity.':'.$record->id));
        }
        $actions = array_merge($actions, static::workflowActions());

        return $table->columns($columns)->recordActions($actions)->defaultSort('id', 'desc')->paginated([10, 25, 50]);
    }

    private static function workflowActions(): array
    {
        $allowed = fn (Model $record) => static::getEloquentQuery()->whereKey($record->id)->exists();
        $wrap = fn ($callback, ?string $permission = null) => function (Model $record, array $data) use ($callback, $allowed, $permission) {
            abort_unless(static::canViewAny() && app(AccessManager::class)->entity(auth()->user(), static::$entity, 'execute') && $allowed($record), 403);
            if ($permission) {
                abort_unless(app(AccessManager::class)->allows(auth()->user(), $permission), 403);
            }
            $module = static::definition()['module'];
            if ($module) {
                app(ModuleManager::class)->transaction($module, fn () => $callback($record, $data));
            } else {
                $callback($record, $data);
            } Notification::make()->title('Operation completed')->success()->send();
        };

        return match (static::$entity) {
            'ProductProposal' => [
                Action::make('submitForReview')->visible(fn (?Model $r) => $r && in_array($r->status, ['draft', 'rejected']))->action($wrap(fn ($r, $d) => app(CatalogPublishing::class)->submit($r))),
                Action::make('approvePublication')->visible(fn (?Model $r) => $r && $r->status === 'pending' && app(AccessManager::class)->allows(auth()->user(), 'catalog.approve', auth()->user()->role === 'admin'))->requiresConfirmation()->action($wrap(fn ($r, $d) => app(CatalogPublishing::class)->approve($r), 'catalog.approve')),
                Action::make('rejectSubmission')->visible(fn (?Model $r) => $r && $r->status === 'pending' && auth()->user()->managesMarketplace())->schema([Textarea::make('notes')->required()->maxLength(2000)])->action($wrap(fn ($r, $d) => app(CatalogPublishing::class)->reject($r, $d['notes']), 'catalog.approve')),
            ],
            'CatalogImport' => [
                Action::make('scanCsv')->label('Validate CSV')->visible(fn (?Model $r) => $r && in_array($r->status, ['uploaded', 'needs_correction']))->action($wrap(fn ($r, $d) => app(CatalogImporter::class)->scan($r))),
                Action::make('stageForReview')->visible(fn (?Model $r) => $r && $r->status === 'review_ready')->requiresConfirmation()->action($wrap(fn ($r, $d) => app(CatalogImporter::class)->stage($r))),
            ],
            'CommissionRule','TaxRule' => [Action::make('toggleRule')->label('Enable / disable rule')->action($wrap(fn ($r, $d) => $r->update(['enabled' => ! $r->enabled])))],
            'RoleAssignment' => [Action::make('revoke')->requiresConfirmation()->action($wrap(fn ($r, $d) => app(AccessManager::class)->revoke($r)))],
            'Product' => [Action::make('sandboxDraft')->label('Draft suggestion (sandbox)')->visible(fn () => app(ModuleManager::class)->enabled('ai'))->action($wrap(fn ($r, $d) => AiDraft::create(['product_id' => $r->id, 'user_id' => auth()->id(), 'content' => 'Sandbox suggestion for '.$r->name.'. A considered addition to your everyday collection. Review all claims before approval.'])))],
            'OrderItem' => [Action::make('shipAllocatedItems')->label('Ship allocated units')->visible(fn (?Model $r) => $r && $r->product_type === 'physical' && in_array($r->vendorOrder->status, ['confirmed', 'processing', 'partially_fulfilled']) && $r->shipped_quantity < $r->quantity - $r->refunded_quantity)->schema([TextInput::make('carrier')->required()->maxLength(100), TextInput::make('tracking')->required()->maxLength(100), TextInput::make('quantity')->integer()->minValue(1)->required()])->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->ship($r->vendorOrder, $d['carrier'], $d['tracking'], [['order_item_id' => $r->id, 'quantity' => (int) $d['quantity']]]), 'orders.ship'))],
            'InventoryBalance' => [
                Action::make('adjustStock')->schema([TextInput::make('delta')->integer()->required(), TextInput::make('reason')->required()->maxLength(255)])->action($wrap(fn ($r, $d) => app(Inventory::class)->change($r, (int) $d['delta'], 0, $d['reason'], 'adjust:'.Str::uuid()), 'inventory.adjust')),
                Action::make('transfer')->schema([Select::make('destination')->options(fn () => OperationsAccess::scope(InventoryBalance::with('offer', 'warehouse'), 'InventoryBalance', auth()->user())->get()->mapWithKeys(fn ($stock) => [$stock->id => $stock->offer->sku.' · '.$stock->warehouse->name.' · '.($stock->quantity - $stock->reserved).' available'])->all())->required(), TextInput::make('quantity')->integer()->minValue(1)->required()])->action($wrap(fn ($r, $d) => app(Inventory::class)->transfer($r, OperationsAccess::scope(InventoryBalance::query(), 'InventoryBalance', auth()->user())->findOrFail($d['destination']), (int) $d['quantity'], 'transfer:'.Str::uuid()), 'inventory.transfer')),
            ],
            'VendorOrder' => [
                Action::make('ship')->visible(fn (?Model $r) => $r && in_array($r->status, ['confirmed', 'processing', 'partially_fulfilled']))->schema([TextInput::make('carrier')->required()->maxLength(100), TextInput::make('tracking')->required()->maxLength(100), Repeater::make('items')->schema([Select::make('order_item_id')->label('Order item')->options(fn (?Model $record) => $record?->items()->where('product_type', 'physical')->get()->mapWithKeys(fn ($line) => [$line->id => $line->name.' · '.max(0, $line->quantity - $line->refunded_quantity - $line->shipped_quantity).' remaining'])->all() ?? [])->required(), TextInput::make('quantity')->integer()->minValue(1)->required()])->defaultItems(0)->reorderable(false)->addActionLabel('Select items')->helperText('Leave empty to ship all remaining physical items.')])->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->ship($r, $d['carrier'], $d['tracking'], $d['items'] ?? []), 'orders.ship')),
                Action::make('settleSandbox')->label('Settle (sandbox)')->requiresConfirmation()->visible(fn (?Model $r) => $r && auth()->user()->managesMarketplace() && $r->status === 'completed' && ! $r->settled_minor)->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->settle($r), 'finance.settle')),
            ],
            'Shipment' => [Action::make('packingSlip')->url(fn (?Model $r) => $r ? route('shipment.packing-slip', $r->id) : null)->openUrlInNewTab(), Action::make('markDelivered')->requiresConfirmation()->visible(fn (?Model $r) => $r && $r->status === 'shipped')->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->deliver($r), 'orders.deliver'))],
            'ReturnRequest' => [
                Action::make('authorizeReturn')->label('Authorize return')->visible(fn (?Model $r) => $r && $r->status === 'requested')->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->authorizeReturn($r))),
                Action::make('receiveReturn')->label('Record receipt')->visible(fn (?Model $r) => $r && $r->status === 'authorized')->schema([Textarea::make('notes')->label('Inspection notes')->required()->maxLength(2000)])->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->receiveReturn($r, $d['notes']))),
                Action::make('receiveAndRefundSandbox')->label('Refund received return (sandbox)')->requiresConfirmation()->visible(fn (?Model $r) => $r && $r->status === 'received' && app(AccessManager::class)->allows(auth()->user(), 'returns.refund'))->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->refund($r), 'returns.refund')),
                Action::make('rejectReturn')->label('Reject return')->visible(fn (?Model $r) => $r && in_array($r->status, ['requested', 'authorized']))->schema([Textarea::make('notes')->label('Decision reason')->required()->maxLength(2000)])->action($wrap(fn ($r, $d) => app(OrderWorkflow::class)->rejectReturn($r, $d['notes']))),
            ],
            'PurchaseOrder' => [Action::make('receive')->requiresConfirmation()->visible(fn (?Model $r) => $r && $r->status !== 'received')->action($wrap(fn ($r, $d) => app(BusinessRules::class)->receive($r)))],
            'Booking' => [Action::make('complete')->visible(fn (?Model $r) => $r && $r->status === 'confirmed')->action($wrap(fn ($r, $d) => $r->update(['status' => 'completed'])))],
            'AiDraft' => [Action::make('approve')->requiresConfirmation()->visible(fn (?Model $r) => $r && $r->status === 'pending_review')->action($wrap(function ($r, $d) {
                Product::findOrFail($r->product_id)->update(['description' => $r->content]);
                $r->update(['status' => 'approved']);
                Audit::record('ai.draft_approved', (string) $r->id);
            }))],
            default => [],
        };
    }
}
