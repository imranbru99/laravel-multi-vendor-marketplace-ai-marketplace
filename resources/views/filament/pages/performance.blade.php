<x-filament-panels::page>
<p>Current request diagnostics. Capture uses Drutogoti’s container with hashed SQL fingerprints only. SQL text, parameter values, cookies and request bodies are never collected or persisted by this adapter.</p>
@foreach(app(\App\Domain\RequestDiagnostics::class)->summary() as $key=>$value)<x-filament::section :heading="ucwords(str_replace('_',' ',$key))"><strong>{{ $value }}</strong></x-filament::section>@endforeach
<p>Repeated fingerprints are a diagnostic signal, not proof of an N+1 problem. Historical request sampling, cache monitoring and background performance recommendations are not enabled.</p>
</x-filament-panels::page>
