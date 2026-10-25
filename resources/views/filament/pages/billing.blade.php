<x-filament-panels::page>
<p>Sandbox subscription billing only. Plan prices are configurable examples, not an offer of a paid service. No money is collected.</p>
@if($errors->any())<div role="alert">{{ $errors->first() }}</div>@endif
@php $current=app(\App\Domain\Subscriptions::class)->current(); @endphp
@if($current)<x-filament::section heading="Current subscription"><p>{{ $current->plan->name }} · {{ $current->status }} · Period ends {{ $current->period_ends_at->format('M j, Y H:i') }}</p><p>{{ $current->cancelled_at ? 'Renewal cancelled. Access continues until the period ends.' : ($current->auto_renew ? 'Sandbox renewal enabled' : 'Automatic renewal is off') }}</p><x-filament::button wire:click="cancelRenewal" color="gray">Cancel automatic renewal</x-filament::button></x-filament::section>@endif
<label><input type="checkbox" wire:model="autoRenew"> Enable simulated automatic renewal</label>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px">
@foreach(\App\Models\SubscriptionPlan::where('active',true)->get() as $plan)
<x-filament::section :heading="$plan->name"><p>{{ \App\Domain\Money::format($plan->price_minor,$plan->currency) }} / {{ $plan->period_days }} days (sandbox)</p><p>@foreach($plan->limits as $key=>$limit){{ ucwords(str_replace('_',' ',$key)) }}: {{ $limit }}<br>@endforeach</p><p>Included modules: {{ implode(', ',$plan->modules) }}</p><x-filament::button wire:click="subscribe({{ $plan->id }})" wire:loading.attr="disabled">Activate sandbox plan</x-filament::button></x-filament::section>
@endforeach
</div>
<x-filament::section heading="Billing history">@forelse(\App\Models\SubscriptionInvoice::latest()->get() as $invoice)<p>{{ $invoice->reference }} · {{ \App\Domain\Money::format($invoice->amount_minor,$invoice->currency) }} · {{ $invoice->status }} · {{ $invoice->paid_at->format('M j, Y') }} · <a href="{{ route('billing.invoice',$invoice->id) }}">Download invoice</a></p>@empty<p>No billing events yet.</p>@endforelse</x-filament::section>
</x-filament-panels::page>
