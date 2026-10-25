<x-filament-panels::page>
<form wire:submit="save" style="display:grid;gap:20px;max-width:720px">
@foreach($data as $key=>$value)
<label>{{ ucwords(str_replace('_',' ',$key)) }}<x-filament::input.wrapper><x-filament::input wire:model="data.{{ $key }}" /></x-filament::input.wrapper>@error('data.'.$key)<span role="alert">{{ $message }}</span>@enderror</label>
@endforeach
<p>Rates use basis points: 1000 = 10%. Amounts use integer minor units: 500 = 5.00.</p><x-filament::button type="submit">Save marketplace settings</x-filament::button>
</form>
</x-filament-panels::page>
