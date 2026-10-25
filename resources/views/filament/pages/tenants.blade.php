<x-filament-panels::page>
<p>Authorized platform access only. Switching marketplace context changes the data shown across this session. Plan changes use simulated billing; no live subscription payment provider is connected.</p>
@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
@foreach(\App\Models\Tenant::all() as $tenant)
<x-filament::section :heading="$tenant->name"><p>{{ $tenant->slug }} · {{ $tenant->plan }} · {{ $tenant->branch_limit }} branches · {{ $tenant->status }}</p><x-filament::button wire:click="switchTenant({{ $tenant->id }})">Open marketplace</x-filament::button> <x-filament::button wire:click="upgrade({{ $tenant->id }})" color="gray">Activate Growth (sandbox)</x-filament::button></x-filament::section>
@endforeach
<x-filament::section heading="Create marketplace"><form wire:submit="createTenant" style="display:grid;gap:16px">@foreach(['name','tenantSlug','domain'] as $field)<label>{{ ucfirst($field) }}<x-filament::input.wrapper><x-filament::input wire:model="{{ $field }}" /></x-filament::input.wrapper></label>@endforeach<x-filament::button type="submit">Create marketplace</x-filament::button></form></x-filament::section>
</x-filament-panels::page>
