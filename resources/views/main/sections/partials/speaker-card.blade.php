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

    $isTba = str_contains($speaker->name, 'TBA');
@endphp

<div class="{{ $colClass ?? 'col-lg-3 col-md-6 col-sm-6 mb-4' }}">
    <div class="card speaker-card-item h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0;">
        <div class="speaker-photo-wrapper" style="position: relative; background: #EEF4FA; height: 260px; overflow: hidden;">
            <img src="{{ $speaker->photo ? $speaker->photo->getUrl() : asset('img/default-speaker.jpg') }}"
                 alt="{{ $speaker->name }}" 
                 class="speaker-img"
                 style="width: 100%; height: 100%; object-fit: cover;">

            @if($speaker->speakerType)
                <span class="speaker-badge" style="position: absolute; top: 12px; left: 12px; background: rgba(0, 51, 102, 0.9); color: #fff; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                    {{ $speaker->speakerType->title }}
                </span>
            @endif

            @if($isTba)
                <span class="badge badge-warning font-weight-bold" style="position: absolute; bottom: 12px; right: 12px; font-size: 11px; padding: 4px 10px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                    <i class="fa fa-clock-o mr-1"></i> TBA
                </span>
            @endif

            @if($trackLabel)
                <span class="speaker-track-badge" title="{{ $speaker->track ? $speaker->track->name : $trackLabel }}" style="position: absolute; top: 12px; right: 12px; background: #0055A0; color: #fff; font-size: 11px; font-weight: 700; padding: 4px 9px; border-radius: 6px;">
                    <i class="fa fa-tag mr-1"></i>{{ $trackLabel }}
                </span>
            @endif
        </div>

        <div class="card-body speaker-card-body d-flex flex-column text-center p-3 bg-white">
            <h5 class="speaker-name font-weight-bold mb-1" style="color: #003366; min-height: 38px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                {{ $speaker->name }}
            </h5>

            @if($speaker->affiliation || $speaker->country)
                <p class="speaker-affiliation text-muted mb-2 small" style="min-height: 30px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa fa-globe mr-1 text-primary"></i>
                    {{ $speaker->affiliation }}
                </p>
            @endif

            @if($speaker->focus_area)
                <p class="speaker-topic small mb-3 p-2 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; color: #334155; font-size: 12px;">
                    <strong class="text-primary">Theme:</strong> {{ $speaker->focus_area }}
                </p>
            @endif

            <div class="mt-auto pt-2">
                @if(!$isTba && $speaker->slug)
                    <a href="{{ route('speaker', ['slug' => $speaker->slug]) }}" class="btn btn-sm btn-outline-primary btn-block rounded-pill" style="border-color: #0055A0; color: #0055A0; font-weight: 600;">
                        View Profile &amp; Sessions <i class="fa fa-arrow-right ml-1"></i>
                    </a>
                @else
                    <span class="d-block small text-muted py-1" style="background: #F1F5F9; border-radius: 20px; font-weight: 600;">
                        <i class="fa fa-info-circle mr-1 text-primary"></i> Official Announcement Soon
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>