@props(['name','size'=>20])
@php $paths = [
'search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
'bag'=>'<path d="M5 7h14l2 14H3L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/>',
'arrow'=>'<path d="M4 12h16m-6-6 6 6-6 6"/>',
'heart'=>'<path d="m12 21-8-8C-2 6 7 0 12 7 17 0 26 6 20 13Z"/>',
'check'=>'<path d="m5 12 4 4L20 5"/>',
'box'=>'<path d="m12 2 10 5v10l-10 5-10-5V7Z"/><path d="m2 7 10 5 10-5M12 12v10M7 4l10 5"/>',
'shield'=>'<path d="m12 2 9 4v6q0 7-9 10-9-3-9-10V6Z"/><path d="m8 12 3 3 5-6"/>',
'globe'=>'<circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/><path d="M2 12h20"/>',
'menu'=>'<path d="M3 6h18M3 12h18M3 18h18"/>',
'chevron'=>'<path d="m8 4 8 8-8 8"/>',
'star'=>'<path d="m12 2 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1Z"/>',
'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
]; @endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['box'] !!}</svg>
