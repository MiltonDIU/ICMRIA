@php
    $trackLabel = null;
    if ($speaker->track) {
        if (preg_match('/Track\s*(\d+)/i', $speaker->track->name, $m)) {
            $trackLabel = 'Track-' . $m[1];
        } else {
            $trackLabel = 'Track-' . $speaker->track->id;
        }
    } elseif ($speaker->track_id) {
        $trackLabel = 'Track-' . $speaker->track_id;
    }
@endphp

<div class="{{ $colClass ?? 'col-lg-3 col-md-6 col-sm-6 mb-4' }}">
    <div class="card speaker-card-item h-100 shadow-sm border-0">
        <div class="speaker-photo-wrapper">
            <img src="{{ $speaker->photo ? $speaker->photo->getUrl() : asset('img/default-speaker.jpg') }}"
                 alt="{{ $speaker->name }}" 
                 class="speaker-img">
            @if($speaker->speakerType)
                <span class="speaker-badge">{{ $speaker->speakerType->title }}</span>
            @endif
            @if($trackLabel)
                <span class="speaker-track-badge" title="{{ $speaker->track ? $speaker->track->name : $trackLabel }}">
                    <i class="fa fa-tag mr-1"></i>{{ $trackLabel }}
                </span>
            @endif
        </div>
        <div class="card-body speaker-card-body d-flex flex-column text-center p-3">
            <h4 class="speaker-name mb-1">
                @if($speaker->slug)
                    <a href="{{ route('speaker', ['slug' => $speaker->slug]) }}">{{ $speaker->name }}</a>
                @else
                    {{ $speaker->name }}
                @endif
            </h4>

            @if($speaker->affiliation || $speaker->country)
                <p class="speaker-affiliation text-muted mb-2">
                    <i class="fa fa-university mr-1 text-primary"></i>
                    {{ $speaker->affiliation }}@if($speaker->affiliation && $speaker->country), {{ $speaker->country }}@endif
                </p>
            @endif

            @if($speaker->focus_area)
                <p class="speaker-topic small mb-3">
                    <span class="text-dark font-weight-bold">Topic:</span> {{ $speaker->focus_area }}
                </p>
            @endif

            <div class="mt-auto pt-2">
                @if($speaker->slug)
                    <a href="{{ route('speaker', ['slug' => $speaker->slug]) }}" class="btn btn-sm btn-speaker-profile btn-block">
                        View Profile & Sessions <i class="fa fa-arrow-right ml-1"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
