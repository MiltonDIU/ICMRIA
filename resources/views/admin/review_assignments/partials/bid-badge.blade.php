@php
    $bidStyles = ['want' => 'success', 'can' => 'info', 'neutral' => 'light border', 'conflict' => 'danger'];
@endphp
@if($preference)
    <span class="badge badge-{{ $bidStyles[$preference] ?? 'light border' }}">{{ \App\Models\PaperBid::LABELS[$preference] ?? $preference }}</span>
@else
    <span class="text-muted">&mdash;</span>
@endif
