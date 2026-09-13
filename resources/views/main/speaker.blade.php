@extends('layouts.main')

@section('content')
<main id="main" class="main-page">
  <section id="speakers-details" class="wow fadeIn">
    <div class="container">
      <div class="section-header">
        <h2>Speaker Details</h2>
        <p>Distinguished speaker profile & conference session details</p>
      </div>

      <div class="row">
        <div class="col-md-5 text-center text-md-left mb-4 mb-md-0">
          <img src="{{ $speaker->photo ? $speaker->photo->getUrl() : asset('img/default-speaker.jpg') }}" 
               alt="{{ $speaker->name }}" 
               class="img-fluid rounded shadow" 
               style="max-height: 420px; width: 100%; object-fit: cover;">
          
          <div class="social mt-3 text-center">
            @if($speaker->twitter && $speaker->twitter !== '#')
              <a href="{{ $speaker->twitter }}" target="_blank" rel="noopener" class="mx-2 text-primary"><i class="fa fa-twitter fa-lg"></i></a>
            @endif
            @if($speaker->facebook && $speaker->facebook !== '#')
              <a href="{{ $speaker->facebook }}" target="_blank" rel="noopener" class="mx-2 text-primary"><i class="fa fa-facebook fa-lg"></i></a>
            @endif
            @if($speaker->linkedin && $speaker->linkedin !== '#')
              <a href="{{ $speaker->linkedin }}" target="_blank" rel="noopener" class="mx-2 text-primary"><i class="fa fa-linkedin fa-lg"></i></a>
            @endif
          </div>
        </div>

        <div class="col-md-7">
          <div class="details">
            <div class="mb-2">
              @if($speaker->speakerType)
                <span class="badge badge-primary px-3 py-2 text-uppercase" style="letter-spacing: 0.5px;">
                  {{ $speaker->speakerType->title }}
                </span>
              @endif
              @if($speaker->track)
                <span class="badge badge-info px-3 py-2 ml-1">
                  {{ $speaker->track->name }}
                </span>
              @endif
            </div>

            <h2 class="mb-1 font-weight-bold" style="color: var(--brand-navy, #00396B);">{{ $speaker->name }}</h2>

            @if($speaker->affiliation || $speaker->country)
              <p class="text-muted font-weight-bold mb-2">
                <i class="fa fa-university mr-1 text-primary"></i> {{ $speaker->affiliation }}@if($speaker->affiliation && $speaker->country), @endif{{ $speaker->country }}
              </p>
            @endif

            @if($speaker->focus_area)
              <div class="p-3 mb-3 rounded" style="background: rgba(0, 85, 160, 0.06); border-left: 4px solid var(--brand-blue, #0055A0);">
                <strong class="text-dark d-block">Session Topic / Focus Area:</strong>
                <span style="color: var(--brand-blue, #0055A0); font-weight: 600;">{{ $speaker->focus_area }}</span>
              </div>
            @endif

            @if($speaker->description)
              <p class="lead" style="font-size: 16px; color: #4a5568;">{{ $speaker->description }}</p>
            @endif

            @if($speaker->full_description)
              <p style="color: #2d3748; line-height: 1.7;">{{ $speaker->full_description }}</p>
            @endif

            @if(isset($schedules) && $schedules->isNotEmpty())
              <div class="speaker-schedule-assignments mt-4 pt-3 border-top">
                <h4 class="font-weight-bold mb-3" style="color: var(--brand-navy, #00396B);">
                  <i class="fa fa-calendar-check-o text-success mr-2"></i>Scheduled Program Sessions
                </h4>
                @foreach($schedules as $session)
                  @if($session->is_active == '1')
                  <div class="card border-0 shadow-sm mb-2 p-3" style="background: #f8fafc; border-left: 4px solid #10BB43 !important;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                      <div>
                        <span class="badge badge-success px-2 py-1 mb-1">Day {{ $session->day_number }}</span>
                        @if($session->scheduleCategory && $session->scheduleCategory->is_active)
                          <span class="badge text-white px-2 py-1 mb-1 ml-1" style="background-color: {{ $session->scheduleCategory->color ?? '#00396B' }}; font-size: 11px;">
                            {{ $session->scheduleCategory->name }}
                          </span>
                        @endif
                      </div>
                      <time class="text-muted font-weight-bold small"><i class="fa fa-clock-o mr-1"></i>{{ \Carbon\Carbon::parse($session->start_time)->format("h:i A") }}</time>
                    </div>
                    <h5 class="mb-1 font-weight-bold text-dark mt-1">{{ $session->title }}</h5>
                    <p class="small text-muted mb-0">{{ $session->subtitle }}</p>
                  </div>
                  @endif
                @endforeach
              </div>
            @endif

            <div class="mt-4">
              <a href="{{ url('/') }}#speakers" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left mr-1"></i> Back to All Speakers
              </a>
              <a href="{{ url('/') }}#schedule" class="btn btn-primary ml-2">
                <i class="fa fa-calendar mr-1"></i> View Full Schedule
              </a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
