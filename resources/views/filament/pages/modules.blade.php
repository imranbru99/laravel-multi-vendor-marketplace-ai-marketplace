<x-filament-panels::page>
<p>Core security, audit history and financial records always remain available. Deactivation checks dependencies and active obligations.</p>
@if($errors->any()) <div role="alert">{{ $errors->first() }}</div> @endif
<label>Find a module<x-filament::input.wrapper><x-filament::input wire:model.live.debounce.300ms="search" placeholder="Search modules" /></x-filament::input.wrapper></label>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
@foreach(config('marketplace.modules') as $key=>$module)
@continue($search && !str_contains(strtolower($module['name'].' '.$module['category']),strtolower($search)))
<x-filament::section :heading="$module['name']">
<p>{{ $module['category'] }} · v1.0</p><p>Dependencies: {{ implode(', ', $module['dependencies']) ?: 'None' }}</p>
<p style="margin:12px 0">{{ app(\App\Domain\ModuleManager::class)->enabled($key) ? 'Enabled' : 'Disabled' }}</p>
<x-filament::button wire:click="toggle('{{ $key }}')" :disabled="$module['core']" wire:loading.attr="disabled">{{ $module['core'] ? 'Core module' : 'Toggle module' }}</x-filament::button>
@if(auth()->user()?->role==='superadmin' && !$module['core'])<p>Platform: {{ \App\Models\PlatformModule::where('key',$key)->where('enabled',false)->exists()?'restricted':'enabled' }}</p><x-filament::button color="gray" wire:click="togglePlatform('{{ $key }}')">Toggle across platform</x-filament::button>@endif</x-filament::section>
@endforeach
</div>
</x-filament-panels::page>
