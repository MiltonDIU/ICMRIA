<section id="schedule" class="section-with-bg">
    <div class="container wow fadeInUp">
        <div class="section-header">
            <h2>Conference Program Schedule</h2>
            <p>Comprehensive two-day itinerary for ICMRIA 2027 (9–10 January 2027)</p>
        </div>

        <ul class="nav nav-tabs justify-content-center" role="tablist">
            @foreach($schedules as $key => $day)
                <li class="nav-item">
                    <a class="nav-link{{ $loop->first ? ' active' : '' }}" href="#day-{{ $key }}" role="tab" data-toggle="tab">
                        <i class="fa fa-calendar-check-o mr-1"></i> Day {{ $key }} ({{ $key === 1 ? '9 Jan 2027' : '10 Jan 2027' }})
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content row justify-content-center">
            @foreach($schedules as $key => $day)
                <div role="tabpanel" class="col-lg-9 tab-pane fade{{ $loop->first ? ' show active' : '' }}" id="day-{{ $key }}">
                    @foreach($day as $schedule)
                        @if($schedule->is_active == '1')
                        <div class="row schedule-item">
                            <div class="col-md-2">
                                <time>{{ \Carbon\Carbon::parse($schedule->start_time)->format("h:i A") }}</time>
                            </div>
                            <div class="col-md-10">
                                @php
                                    $speakersList = $schedule->speakers->isNotEmpty() ? $schedule->speakers : ($schedule->speaker ? collect([$schedule->speaker]) : collect());
                                @endphp

                                @if($speakersList->count() === 1)
                                    @php $speaker = $speakersList->first(); @endphp
                                    <div class="speaker">
                                        <img src="{{ $speaker->photo ? $speaker->photo->getUrl() : asset('img/default-speaker.jpg') }}" 
                                             alt="{{ $speaker->name }}" 
                                             style="object-fit: cover;">
                                    </div>

                                    <h4>
                                        @if($schedule->is_workshop == 1)
                                            <a href="{{ route('scheduleDetails', [$schedule->id, $schedule->title]) }}">
                                                {{ $schedule->title }} <span class="badge badge-warning text-dark ml-1">Workshop</span>
                                            </a>
                                        @else
                                            {{ $schedule->title }}
                                        @endif
                                        @if($schedule->scheduleCategory && $schedule->scheduleCategory->is_active)
                                            <span class="badge text-white ml-2 py-1 px-2" style="font-size: 11px; font-weight: 700; background-color: {{ $schedule->scheduleCategory->color ?? '#00396B' }}; vertical-align: middle; border-radius: 4px;">
                                                <i class="fa fa-tag mr-1"></i>{{ $schedule->scheduleCategory->name }}
                                            </span>
                                        @endif
                                        <span>
                                            @if($speaker->slug)
                                                <a href="{{ route('speaker', ['slug' => $speaker->slug]) }}" class="font-weight-bold" style="color: var(--brand-blue);">
                                                    {{ $speaker->name }}
                                                </a>
                                            @else
                                                <strong style="color: var(--brand-blue);">{{ $speaker->name }}</strong>
                                            @endif
                                            @if($speaker->affiliation)
                                                <small class="text-muted font-weight-normal d-block">
                                                    {{ $speaker->affiliation }}@if($speaker->country), {{ $speaker->country }}@endif
                                                </small>
                                            @endif
                                        </span>
                                    </h4>

                                    <p>{{ $schedule->subtitle }}</p>

                                @elseif($speakersList->count() > 1)
                                    <div class="speaker">
                                        <img src="{{ asset('img/default-speaker.jpg') }}" alt="{{ $schedule->title }}">
                                    </div>

                                    <h4>
                                        @if($schedule->is_workshop == 1)
                                            <a href="{{ route('scheduleDetails', [$schedule->id, $schedule->title]) }}">
                                                {{ $schedule->title }} <span class="badge badge-warning text-dark ml-1">Workshop</span>
                                            </a>
                                        @else
                                            {{ $schedule->title }}
                                        @endif
                                        @if($schedule->scheduleCategory && $schedule->scheduleCategory->is_active)
                                            <span class="badge text-white ml-2 py-1 px-2" style="font-size: 11px; font-weight: 700; background-color: {{ $schedule->scheduleCategory->color ?? '#00396B' }}; vertical-align: middle; border-radius: 4px;">
                                                <i class="fa fa-tag mr-1"></i>{{ $schedule->scheduleCategory->name }}
                                            </span>
                                        @endif
                                        <span class="d-block mt-1">
                                            @foreach($speakersList as $idx => $sp)
                                                @if($sp->slug)
                                                    <a href="{{ route('speaker', ['slug' => $sp->slug]) }}" class="font-weight-bold text-primary">
                                                        {{ $sp->name }}
                                                    </a>
                                                @else
                                                    <strong>{{ $sp->name }}</strong>
                                                @endif
                                                @if(!$loop->last), @endif
                                            @endforeach
                                        </span>
                                    </h4>

                                    <p>{{ $schedule->subtitle }}</p>

                                @else
                                    <h4>
                                        @if($schedule->is_workshop == 1)
                                            <a href="{{ route('scheduleDetails', [$schedule->id, $schedule->title]) }}">
                                                {{ $schedule->title }} <span class="badge badge-warning text-dark ml-1">Workshop</span>
                                            </a>
                                        @else
                                            {{ $schedule->title }}
                                        @endif
                                        @if($schedule->scheduleCategory && $schedule->scheduleCategory->is_active)
                                            <span class="badge text-white ml-2 py-1 px-2" style="font-size: 11px; font-weight: 700; background-color: {{ $schedule->scheduleCategory->color ?? '#00396B' }}; vertical-align: middle; border-radius: 4px;">
                                                <i class="fa fa-tag mr-1"></i>{{ $schedule->scheduleCategory->name }}
                                            </span>
                                        @endif
                                    </h4>

                                    <p>{{ $schedule->subtitle }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
