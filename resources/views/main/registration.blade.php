@extends('layouts.main')

@section('content')
@php $abstractOpen = \App\Services\SubmissionRules::abstractWindowIsOpen(); @endphp
    <main id="main" class="main-page">
        <section class="wow fadeIn">
            <div class="title-section" style="background: linear-gradient(135deg, #001f3f 0%, #003366 55%, #004d80 100%); padding: 75px 0 45px;">
                <div class="container">
                    <div class="section-header">
                        <h3>Register Now</h3>
                        {{--                        <p style="color: red">**Notice:** This registration form is currently under testing. Please do not submit any actual registration information at this time, as all submitted data will be deleted. The official registration will open soon.--}}
                        </p>
                    </div>
                </div>
            </div>
            </div>
            <div class="container">
                @if(session()->has('message') || session()->has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <strong>Success!</strong> {{ session()->get('message') ?? session()->get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session()->has('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <strong>Error!</strong> {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session()->has('warning'))
                    <div class="alert alert-warning alert-dismissible">
                        <strong>Note!</strong> {{ session()->get('warning') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session()->has('info'))
                    <div class="alert alert-info alert-dismissible">
                        <strong>Information!</strong> {{ session()->get('info') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @php
                    $eventStartDate = \Carbon\Carbon::parse($settings['registration_start_date'] ?? now());
                    $eventCloseDate = \Carbon\Carbon::parse($settings['registration_close_date'] ?? now()->addMonth());
                    $eventAbstractDeadline = \Carbon\Carbon::parse($settings['abstract_submission_deadline'] ?? $settings['registration_close_date'] ?? now()->addMonth());
                    $eventEarlyRegDate = \Carbon\Carbon::parse($settings['early_registration_last_date'] ?? now()->addWeek());
                    $eventPaymentLastDate = \Carbon\Carbon::parse($settings['payment_last_date'] ?? now()->addMonths(2));
                    $currentDate = \Carbon\Carbon::now();
                @endphp

                @php
                    $registrationNotStarted = $currentDate < $eventStartDate;
                    $registrationClosed = $currentDate > $eventCloseDate;
                    $seatIsFull = ($settings['seat_is_full'] ?? 'false') != 'false';
                @endphp
                        <div class="row">
                            <div class="col-md-5 line">
                                <div class="bg-color sidebar-conference-card">
                                    {{-- Brand / Logo Header --}}
                                    <div class="text-center mb-3">
                                        <img src="{{ asset('img/icmria27-logo.png') }}" alt="ICMRIA 2027" class="img-fluid sidebar-logo mb-2" style="max-height: 52px;">
                                        <h4 class="conference-name mb-1">ICMRIA 2027</h4>
                                        <div class="conference-theme-tag">Connecting Knowledge, Innovation and Society for a Sustainable and Intelligent Future</div>
                                    </div>

                                    {{-- Quick Info Badges --}}
                                    <div class="sidebar-meta-list mb-3">
                                        <div class="meta-item-pill">
                                            <i class="fa fa-calendar-check-o text-primary mr-2" style="font-size: 16px;"></i>
                                            <div>
                                                <div class="meta-item-label">Conference Dates</div>
                                                <div class="meta-item-val">{!! $settings['about_when'] ?? '9–10 January 2027' !!}</div>
                                            </div>
                                        </div>
                                        <div class="meta-item-pill mt-2">
                                            <i class="fa fa-map-marker text-primary mr-2" style="font-size: 18px;"></i>
                                            <div>
                                                <div class="meta-item-label">Venue & Mode</div>
                                                <div class="meta-item-val">Daffodil Smart City, Dhaka &bull; Hybrid (Onsite / Online)</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Official Registration Fees --}}
                                    <div class="fee-information mb-3">
                                        <div class="mb-2">
                                            <h6 class="sidebar-section-title mb-0">
                                                <i class="fa fa-tags text-primary mr-1"></i> Registration & Delegate Fees
                                            </h6>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm fee-table shadow-sm text-center mb-2" style="font-size: 12px;">
                                                <thead>
                                                <tr style="background-color: #F1F5F9;">
                                                    <th class="text-left" style="width: 48%; padding: 8px 6px;">Delegate Category</th>
                                                    <th class="text-primary font-weight-bold" style="width: 26%; padding: 8px 6px;">Early Bird</th>
                                                    <th style="width: 26%; padding: 8px 6px;">Regular</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $feeGroups = [
                                                        'BDT' => ['label' => 'National Delegates (BDT - ৳)', 'icon' => 'fa-flag'],
                                                        'USD' => ['label' => 'International & SAARC (USD - $)', 'icon' => 'fa-globe'],
                                                    ];
                                                    $feeRowLabels = [
                                                        'student'       => ['Student', 'Presenter / Delegate'],
                                                        'academic'      => ['Academic', 'Faculty / Scholar'],
                                                        'industry'      => ['Industry', 'Corporate / R&D'],
                                                        'saarc'         => ['SAARC', 'Nations Delegate'],
                                                        'international' => ['International', 'Delegate'],
                                                    ];
                                                @endphp
                                                @foreach($feeGroups as $groupCurrency => $group)
                                                    @php $groupPrices = $prices->where('currency', $groupCurrency); @endphp
                                                    @if($groupPrices->isNotEmpty())
                                                        <tr style="background: #F8FAFC;">
                                                            <td colspan="3" class="text-left font-weight-bold py-1 px-2" style="font-size: 11px; color: #003366;">
                                                                <i class="fa {{ $group['icon'] }} text-primary mr-1"></i> {{ $group['label'] }}
                                                            </td>
                                                        </tr>
                                                        @foreach($groupPrices as $feeRow)
                                                            <tr>
                                                                @php $feeLabel = $feeRowLabels[$feeRow->category] ?? [ucfirst((string) $feeRow->category), 'Delegate']; @endphp
                                                                <td class="text-left py-2 px-2">
                                                                    <span class="badge badge-light border mr-1">{{ $feeLabel[0] }}</span>
                                                                    {{ $feeLabel[1] }}
                                                                </td>
                                                                <td class="text-primary font-weight-bold py-2">{{ $feeRow->currency_symbol }}{{ number_format($feeRow->early_bird_price) }}</td>
                                                                <td class="py-2">{{ $feeRow->currency_symbol }}{{ number_format($feeRow->regular_price) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Key Timelines --}}
                                    <div class="sidebar-timeline-card mb-3 p-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                        <h6 class="sidebar-section-title mb-2">
                                            <i class="fa fa-clock-o text-primary mr-1"></i> Critical Deadlines
                                        </h6>
                                        <div class="timeline-row d-flex justify-content-between py-1 border-bottom" style="font-size: 12px;">
                                            <span class="text-muted"><i class="fa fa-circle text-info mr-1" style="font-size: 8px;"></i> Abstract Deadline</span>
                                            <span class="font-weight-bold text-dark">{{ $eventAbstractDeadline->format('j M Y') }}</span>
                                        </div>
                                        <div class="timeline-row d-flex justify-content-between py-1 border-bottom" style="font-size: 12px;">
                                            <span class="text-muted"><i class="fa fa-circle text-primary mr-1" style="font-size: 8px;"></i> Early Bird Payment</span>
                                            <span class="font-weight-bold text-primary">Till {{ $eventEarlyRegDate->format('j M Y') }}</span>
                                        </div>
                                        <div class="timeline-row d-flex justify-content-between py-1 border-bottom" style="font-size: 12px;">
                                            <span class="text-muted"><i class="fa fa-circle text-danger mr-1" style="font-size: 8px;"></i> Registration Close</span>
                                            <span class="font-weight-bold text-danger">{{ $eventCloseDate->format('j M Y') }}</span>
                                        </div>
                                        <div class="timeline-row d-flex justify-content-between py-1" style="font-size: 12px;">
                                            <span class="text-muted"><i class="fa fa-flag text-success mr-1" style="font-size: 8px;"></i> Conference Dates</span>
                                            <span class="font-weight-bold text-success">{!! $settings['about_when'] ?? '9–10 Jan 2027' !!}</span>
                                        </div>
                                    </div>

                                    {{-- Key Benefits Highlights --}}
                                    <div class="sidebar-entitlements p-3 rounded mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                        <h6 class="sidebar-section-title mb-2">
                                            <i class="fa fa-check-circle text-success mr-1"></i> Included Delegate Entitlements
                                        </h6>
                                        <ul class="list-unstyled mb-0 pl-1" style="font-size: 12px; line-height: 1.8; color: #334155;">
                                            <li><i class="fa fa-check text-primary mr-2"></i> Full access to all 8 tracks & keynote sessions</li>
                                            <li><i class="fa fa-check text-primary mr-2"></i> Official Presentation & Participation Certificate</li>
                                            <li><i class="fa fa-check text-primary mr-2"></i> Scopus-Indexed Q2 Journal Publication eligibility</li>
                                        </ul>
                                    </div>

                                    {{-- Secretariat Support --}}
                                    <div class="sidebar-contact text-center p-3 rounded" style="background: #EEF4FA; border: 1px dashed #0055A0;">
                                        <div class="small font-weight-bold text-primary mb-1">
                                            <i class="fa fa-envelope-o mr-1"></i> Secretariat & Submission Inquiries
                                        </div>
                                        <div class="small text-dark font-weight-bold">
                                            <a href="mailto:{{ $settings['contact_email'] ?? 'fahadhossain.swe@diu.edu.bd' }}" style="color: #003366;">
                                                {{ $settings['contact_email'] ?? 'fahadhossain.swe@diu.edu.bd' }}
                                            </a>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="fa fa-whatsapp text-success mr-1"></i> {{ $settings['contact_phone'] ?? '+8801946704373' }}
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-7 line">
                                <div class="bg-color-form">
                                    @if($registrationNotStarted)
                                        <div class="text-center py-5">
                                            <i class="fa fa-hourglass-start fa-3x text-primary mb-3"></i>
                                            <h4>Registration Has Not Started Yet</h4>
                                            <p class="text-muted mb-0">Registration will open on {{ $eventStartDate->format('j F Y, g:i A') }}.</p>
                                        </div>
                                    @elseif($seatIsFull)
                                        <div class="text-center py-5">
                                            <i class="fa fa-users fa-3x text-danger mb-3"></i>
                                            <h4>Registration is Full</h4>
                                            <p class="text-muted mb-0">All available seats have been taken.</p>
                                        </div>
                                    @elseif($registrationClosed)
                                        <div class="text-center py-5">
                                            <i class="fa fa-lock fa-3x text-danger mb-3"></i>
                                            <h4>Registration Has Closed</h4>
                                            <p class="text-muted mb-0">The registration window closed on {{ $eventCloseDate->format('j F Y, g:i A') }}.</p>
                                        </div>
                                    @else
                                    <form method="POST" action="{{ route('register') }}" id="registrationForm">
                                        @csrf

                                        <h4 class="mb-4 text-primary"><strong>Participant Information</strong></h4>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="first_name"><strong>First Name*</strong></label>
                                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" required autofocus>
                                                @error('first_name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="last_name"><strong>Last Name*</strong></label>
                                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required>
                                                @error('last_name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="email"><strong>Email Address*</strong></label>
                                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                                                @error('email') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="password"><strong>Password*</strong></label>
                                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                                                @error('password') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="password-confirm"><strong>Confirm Password*</strong></label>
                                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="designation"><strong>Designation*</strong></label>
                                                <input type="text" id="designation" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation') }}" required>
                                                @error('designation') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="department"><strong>Department</strong></label>
                                                <input type="text" id="department" name="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="institution"><strong>Institution / Affiliation*</strong></label>
                                            <input type="text" id="institution" name="institution" class="form-control @error('institution') is-invalid @enderror" value="{{ old('institution') }}" required>
                                            @error('institution') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="country_id"><strong>Country*</strong></label>
                                                <select id="country_id" name="country_id" class="form-control @error('country_id') is-invalid @enderror" required>
                                                    <option value="">Select Country</option>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('country_id') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="whatsapp_number"><strong>WhatsApp Number*</strong></label>
                                                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number') }}" required>
                                                @error('whatsapp_number') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="orcid_id"><strong>ORCID iD</strong></label>
                                            <input type="text" id="orcid_id" name="orcid_id" class="form-control @error('orcid_id') is-invalid @enderror"
                                                   value="{{ old('orcid_id') }}" placeholder="0000-0000-0000-0000" maxlength="19">
                                            <small class="form-text text-muted">
                                                Optional. Sixteen digits in four groups, e.g. 0000-0002-1825-0097. The last character may be an X.
                                            </small>
                                            @error('orcid_id') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="price_id"><strong>Delegate Category*</strong></label>
                                            <select id="price_id" name="price_id" class="form-control @error('price_id') is-invalid @enderror" required>
                                                <option value="">Select the category that applies to you</option>
                                                @foreach($prices as $priceOption)
                                                    <option value="{{ $priceOption->id }}" data-category="{{ $priceOption->category }}" {{ old('price_id') == $priceOption->id ? 'selected' : '' }}>
                                                        {{ $priceOption->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                See the fee table for the rate of each category. Early bird rates apply until {{ $eventEarlyRegDate->format('j M Y') }}.
                                            </small>
                                            @error('price_id') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                        </div>

                                        {{--                                        <div class="mb-4">--}}
                                        {{--                                            <label><strong>Mode of Participation*</strong></label><br>--}}
                                        {{--                                            <div class="form-check form-check-inline">--}}
                                        {{--                                                <input class="form-check-input" type="radio" name="participation_mode" id="onsite" value="onsite" {{ old('participation_mode', 'onsite') == 'onsite' ? 'checked' : '' }}>--}}
                                        {{--                                                <label class="form-check-label" for="onsite">Onsite</label>--}}
                                        {{--                                            </div>--}}
                                        {{--                                            <div class="form-check form-check-inline">--}}
                                        {{--                                                <input class="form-check-input" type="radio" name="participation_mode" id="online" value="online" {{ old('participation_mode') == 'online' ? 'checked' : '' }}>--}}
                                        {{--                                                <label class="form-check-label" for="online">Online</label>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        <div class="mb-4">
                                            <label><strong>Mode of Participation*</strong></label>
                                            <div class="participation-pill-group">
                                                <label class="participation-pill" for="onsite">
                                                    <input type="radio" name="participation_mode" id="onsite" value="onsite"
                                                           {{ old('participation_mode', 'onsite') == 'onsite' ? 'checked' : '' }}
                                                           onchange="checkFormValidity();">
                                                    <span>Onsite</span>
                                                </label>
                                                <label class="participation-pill" for="online">
                                                    <input type="radio" name="participation_mode" id="online" value="online"
                                                           {{ old('participation_mode', 'onsite') == 'online' ? 'checked' : '' }}
                                                           onchange="checkFormValidity();">
                                                    <span>Online</span>
                                                </label>
                                            </div>
                                            @error('participation_mode')
                                            <div class="text-danger small mt-1"><strong>{{ $message }}</strong></div>
                                            @enderror
                                        </div>

                                        <div class="mb-4">
                                            <label><strong>I want to:*</strong></label>
                                            <div class="intent-card-group">

                                                <label class="intent-card participant" for="participant_only">
                                                    <input type="radio" name="is_author" id="participant_only" value="0"
                                                           {{ old('is_author', '0') == '0' ? 'checked' : '' }}
                                                           onchange="toggleAbstractSection(); checkFormValidity();">
                                                    <span class="radio-circle"></span>
                                                    <span class="intent-text">
                <span class="intent-title">Register as Participant Only</span>
                <span class="intent-sub">Payment required upon registration</span>
            </span>
                                                </label>

                                                <label class="intent-card author" for="submit_abstract">
                                                    <input type="radio" name="is_author" id="submit_abstract" value="1"
                                                           {{ old('is_author', '0') == '1' ? 'checked' : '' }}
                                                           onchange="toggleAbstractSection(); checkFormValidity();">
                                                    <span class="radio-circle"></span>
                                                    <span class="intent-text">
                <span class="intent-title">Submit an Abstract</span>
                <span class="intent-sub">
                    @if($abstractOpen)
                        Payment required after abstract confirmation
                    @else
                        Register as paper author — submit abstract after email verification
                    @endif
                </span>
            </span>
                                                </label>

                                            </div>
                                            @error('is_author')
                                            <div class="text-danger small mt-1"><strong>{{ $message }}</strong></div>
                                            @enderror
                                        </div>



                                        <!-- Abstract Section -->
                                        <div id="abstract_section" style="display: none;">
                                            <h4 class="mb-4 text-primary"><strong>Abstract Submission Details</strong></h4>

                                            @include('partials.submission-guidance')

                                            <div class="mb-3">
                                                <label for="paper_title"><strong>Paper Title*</strong></label>
                                                <input type="text" id="paper_title" name="paper_title" class="form-control" value="{{ old('paper_title') }}">
                                                @error('paper_title') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="abstract_text"><strong>Abstract ({{ $abstractMinWords }}-{{ $abstractMaxWords }} words)*</strong></label>
                                                <textarea id="abstract_text" name="abstract_text" class="form-control" rows="6" oninput="countWords()" onkeydown="preventExtraWords(event)">{{ old('abstract_text') }}</textarea>
                                                <div id="word_count_display" class="small mt-1 text-muted">Words: <span id="word_count">0</span> / {{ $abstractMaxWords }}</div>
                                                @error('abstract_text') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror
                                            </div>

                                            <div class="mb-3">
                                                @include('partials.keyword-tags')
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="track_id"><strong>Conference Track*</strong></label>
                                                    <select id="track_id" name="track_id" class="form-control" onchange="updateSubTracks()">
                                                        <option value="">Select Track</option>
                                                        @foreach($tracks as $track)
                                                            <option value="{{ $track->id }}" {{ old('track_id') == $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('track_id') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="sub_track_id"><strong>Sub-Theme*</strong></label>
                                                    <select id="sub_track_id" name="sub_track_id" class="form-control">
                                                        <option value="">Select Sub-Theme</option>
                                                    </select>
                                                    @error('sub_track_id') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror
                                                </div>
                                            </div>

                                            @include('partials.conflict-fields')

                                            <div class="mb-4">
                                                <label class="custom-check-card" for="is_corresponding_author">
                                                    <input type="hidden" name="is_corresponding_author" value="0">
                                                    <input type="checkbox" name="is_corresponding_author" id="is_corresponding_author" value="1"
                                                        {{ old('is_corresponding_author', '1') ? 'checked' : '' }}>
                                                    <span class="custom-check-box"></span>
                                                    <span class="custom-check-content">
                                                        <span class="custom-check-title">I am the corresponding author</span>
                                                        <span class="custom-check-sub">The conference will use this email for all communications</span>
                                                    </span>
                                                </label>
                                            </div>

                                            <label class="presenting-author-card" for="presenter_submitter">
                                                <input class="presenting-author-radio" type="radio" name="presenting_author_index" id="presenter_submitter" value="submitter"
                                                    {{ old('presenting_author_index', 'submitter') == 'submitter' ? 'checked' : '' }}>
                                                <span class="presenting-radio-dot"></span>
                                                <span class="presenting-author-content">
                                                    <span class="presenting-author-title">I will be the Presenting Author</span>
                                                    <span class="presenting-author-sub">You will present this paper at the conference</span>
                                                </span>
                                            </label>

                                            <h5 class="mb-3"><strong>Co-Authors</strong></h5>
                                            @if($errors->hasAny(['co_authors', 'co_authors.*']))
                                                <div class="alert alert-danger py-2 small mb-3">
                                                    <strong>Please fix the following co-author errors:</strong>
                                                    <ul class="mb-0 mt-1">
                                                        @foreach($errors->get('co_authors.*') as $errorGroup)
                                                            @foreach($errorGroup as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            <div id="co_authors_container">
                                                <!-- Dynamic Co-authors will be added here -->
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm mb-4" onclick="addCoAuthor()">+ Add Co-Author</button>

                                            <h5 class="mb-3"><strong>Declarations</strong></h5>
                                            <div class="declaration-group mb-4">

                                                <label class="declaration-check-item @error('consent_original') is-invalid-item @enderror" for="consent_original">
                                                    <input type="checkbox" name="consent_original" id="consent_original" value="1" {{ old('consent_original') ? 'checked' : '' }}>
                                                    <span class="decl-check-box"></span>
                                                    <span class="decl-check-label">I confirm that this abstract is original and not published elsewhere.*</span>
                                                </label>
                                                @error('consent_original') <div class="text-danger small px-2 mb-1"><strong>{{ $message }}</strong></div> @enderror

                                                <label class="declaration-check-item @error('consent_review') is-invalid-item @enderror" for="consent_review">
                                                    <input type="checkbox" name="consent_review" id="consent_review" value="1" {{ old('consent_review') ? 'checked' : '' }}>
                                                    <span class="decl-check-box"></span>
                                                    <span class="decl-check-label">I agree to the peer-review process of the conference.*</span>
                                                </label>
                                                @error('consent_review') <div class="text-danger small px-2 mb-1"><strong>{{ $message }}</strong></div> @enderror

                                                <label class="declaration-check-item @error('consent_acceptance') is-invalid-item @enderror" for="consent_acceptance">
                                                    <input type="checkbox" name="consent_acceptance" id="consent_acceptance" value="1" {{ old('consent_acceptance') ? 'checked' : '' }}>
                                                    <span class="decl-check-box"></span>
                                                    <span class="decl-check-label">If accepted, at least one author will register and present.*</span>
                                                </label>
                                                @error('consent_acceptance') <div class="text-danger small px-2 mb-1"><strong>{{ $message }}</strong></div> @enderror

                                                <label class="declaration-check-item @error('consent_no_late_addition') is-invalid-item @enderror" for="consent_no_late_addition">
                                                    <input type="checkbox" name="consent_no_late_addition" id="consent_no_late_addition" value="1" {{ old('consent_no_late_addition') ? 'checked' : '' }}>
                                                    <span class="decl-check-box"></span>
                                                    <span class="decl-check-label">No author can be added after the abstract is submitted.*</span>
                                                </label>
                                                @error('consent_no_late_addition') <div class="text-danger small px-2 mb-1"><strong>{{ $message }}</strong></div> @enderror

                                            </div>
                                        </div>

                                        {{-- Honeypot Bot Protection --}}
                                        <div style="display: none;">
                                            <input type="text" name="extra_info" id="extra_info" value="">
                                        </div>

                                        @include('partials.fee-summary')

                                        <div class="row pt-4 border-top">
                                            <div class="col-md-12">
                                                <div id="action_buttons_participant" style="display: {{ old('is_author') == '1' ? 'none' : 'block' }};">
                                                    @if(($settings['is_payment_enabled'] ?? 'true') == 'true')
                                                        <button type="submit" class="btn btn-primary" name="action" value="save-pay">
                                                            <i class="fa fa-credit-card"></i> Save & Continue
                                                        </button>
                                                    @else
                                                        <button type="submit" class="btn btn-primary" name="action" value="save-close">
                                                            <i class="fa fa-user-plus"></i> Complete Registration
                                                        </button>
                                                    @endif
                                                </div>
                                                <div id="action_buttons_author" style="display: {{ old('is_author') == '1' ? 'block' : 'none' }};">
                                                    <button type="submit" class="btn btn-success" name="action" value="save-close">
                                                        <i class="fa fa-paper-plane"></i> Submit Abstract
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
            </div>
        </section>
    </main>

    <!-- Co-author Template -->
    <template id="co_author_template">
        <div class="co-author-entry border p-3 mb-3 rounded position-relative">
            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px;" onclick="removeCoAuthor(this)">×</button>
            <h6 class="mb-3">Co-Author #<span class="author-index"></span></h6>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="co_authors[{index}][name]" class="form-control form-control-sm" placeholder="Full Name*" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="email" name="co_authors[{index}][email]" class="form-control form-control-sm" placeholder="Email Address*" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="co_authors[{index}][designation]" class="form-control form-control-sm" placeholder="Designation*" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="co_authors[{index}][department]" class="form-control form-control-sm" placeholder="Department">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="co_authors[{index}][institution]" class="form-control form-control-sm" placeholder="Institution*" required>
                </div>
                <div class="col-md-6 mb-2">
                    <select name="co_authors[{index}][country_id]" class="form-control form-control-sm delegate-country-select" required>
                        <option value="">Select Country*</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <select name="co_authors[{index}][price_id]" class="form-control form-control-sm delegate-category-select" required>
                        <option value="">Delegate Category*</option>
                        @foreach($prices as $priceOption)
                            <option value="{{ $priceOption->id }}" data-category="{{ $priceOption->category }}">{{ $priceOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-2 d-flex align-items-center">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="co_authors[{index}][is_student]" value="0">
                        <input type="checkbox" class="custom-control-input co-author-student" id="co_student_{index}" name="co_authors[{index}][is_student]" value="1">
                        <label class="custom-control-label" for="co_student_{index}">
                            This co-author is a student
                        </label>
                    </div>
                </div>
            </div>
            <label class="presenting-author-card mt-2" for="presenter_{index}">
                <input class="presenting-author-radio" type="radio" name="presenting_author_index" id="presenter_{index}" value="{index}">
                <span class="presenting-radio-dot"></span>
                <span class="presenting-author-content">
                    <span class="presenting-author-title">This co-author will be the Presenting Author</span>
                    <span class="presenting-author-sub">They will present this paper at the conference</span>
                </span>
            </label>
        </div>
    </template>

@endsection

@push('script')
    <script>
        let coAuthorIndex = {{ old('co_authors') ? count(old('co_authors')) : 0 }};
        const tracks = @json($tracks);

        // Limits come from the settings table via App\Services\SubmissionRules,
        // so these counters can never disagree with the server-side validator.
        const ABSTRACT_MIN_WORDS = {{ $abstractMinWords }};
        const ABSTRACT_MAX_WORDS = {{ $abstractMaxWords }};

        function splitWords(text) {
            return text.trim().split(/\s+/).filter(w => w !== '');
        }

        // Stops the abstract growing past the limit. Editing or deleting inside the
        // existing text stays free; only keystrokes that would start a new word are
        // refused once the cap is reached.
        function preventExtraWords(event) {
            const textarea = event.target;
            if (event.ctrlKey || event.metaKey) return;
            if (event.key.length !== 1) return;
            if (textarea.selectionStart !== textarea.selectionEnd) return;

            const wordCount = splitWords(textarea.value).length;
            if (wordCount < ABSTRACT_MAX_WORDS) return;

            if (wordCount > ABSTRACT_MAX_WORDS) {
                event.preventDefault();
                return;
            }

            const atEnd = textarea.selectionStart === textarea.value.length;
            const wouldStartNewWord = event.key === ' ' || /\s$/.test(textarea.value);
            if (atEnd && wouldStartNewWord) {
                event.preventDefault();
            }
        }

        function countWords() {
            const textarea = document.getElementById('abstract_text');
            if (!textarea) return;
            const counter = document.getElementById('word_count');
            const display = document.getElementById('word_count_display');

            // Pasting can still overshoot the cap, so trim back to it.
            let words = splitWords(textarea.value);
            if (words.length > ABSTRACT_MAX_WORDS) {
                textarea.value = words.slice(0, ABSTRACT_MAX_WORDS).join(' ');
                words = splitWords(textarea.value);
            }

            const wordCount = words.length;
            counter.innerText = wordCount;

            // Empty is flagged at once because the abstract is mandatory. Once there
            // is text it stays green: being short of the minimum only means the
            // author is not finished writing. Red returns if the cap is exceeded.
            const ok = wordCount > 0 && wordCount <= ABSTRACT_MAX_WORDS;
            display.classList.remove('text-muted');
            display.classList.toggle('text-success', ok);
            display.classList.toggle('text-danger', !ok);
            display.classList.toggle('font-weight-bold', !ok);
        }

        // Initialize counts on load. The keyword chips bring their own init.
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('abstract_text')) countWords();
        });

        function updateSubTracks() {
            const trackId = document.getElementById('track_id').value;
            const subTrackSelect = document.getElementById('sub_track_id');
            const oldSubTrackId = "{{ old('sub_track_id') }}";

            subTrackSelect.innerHTML = '<option value="">Select Sub-Theme</option>';

            if (trackId) {
                const selectedTrack = tracks.find(t => t.id == trackId);
                if (selectedTrack && selectedTrack.sub_tracks) {
                    selectedTrack.sub_tracks.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.text = sub.name;
                        if (sub.id == oldSubTrackId) {
                            option.selected = true;
                        }
                        subTrackSelect.add(option);
                    });
                }
            }
        }

        function toggleAbstractSection() {
            const isAuthor = document.getElementById('submit_abstract').checked;
            const isSubmissionOpen = {{ $abstractOpen ? 'true' : 'false' }};
            const showForm = isAuthor && isSubmissionOpen;

            const abstractSection = document.getElementById('abstract_section');
            if (abstractSection) {
                abstractSection.style.display = showForm ? 'block' : 'none';

                // Every field in the section becomes required, except those marked to
                // opt out — the keyword chip editor is emptied after each entry, so
                // requiring it would make the form impossible to submit.
                const fields = abstractSection.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([data-skip-required]), textarea, select');
                fields.forEach(el => {
                    if (showForm) {
                        el.setAttribute('required', '');
                    } else {
                        el.removeAttribute('required');
                    }
                });
            }

            const authorButtons = document.getElementById('action_buttons_author');
            const participantButtons = document.getElementById('action_buttons_participant');

            if (authorButtons) authorButtons.style.display = showForm ? 'block' : 'none';
            if (participantButtons) participantButtons.style.display = showForm ? 'none' : 'block';

            // If it's Author but submission is closed, show a "Register" button instead of "Submit Abstract"
            @if(!$abstractOpen)
            if (participantButtons) {
                const btn = participantButtons.querySelector('button');
                if (isAuthor) {
                    btn.innerHTML = '<i class="fa fa-user-plus"></i> Register as Author';
                    btn.value = 'save-close';
                } else {
                    if ({{ ($settings['is_payment_enabled'] ?? 'true') == 'true' ? 'true' : 'false' }}) {
                        btn.innerHTML = '<i class="fa fa-credit-card"></i> Save & Continue to Payment';
                        btn.value = 'save-pay';
                    } else {
                        btn.innerHTML = '<i class="fa fa-user-plus"></i> Complete Registration';
                        btn.value = 'save-close';
                    }
                }
            }
            @endif
        }

        function addCoAuthor(data = null) {
            const container = document.getElementById('co_authors_container');
            const template = document.getElementById('co_author_template').innerHTML;
            const html = template.replace(/{index}/g, coAuthorIndex);

            const div = document.createElement('div');
            div.innerHTML = html;
            const entry = div.firstElementChild;

            if (data) {
                entry.querySelector(`input[name="co_authors[${coAuthorIndex}][name]"]`).value = data.name || '';
                entry.querySelector(`input[name="co_authors[${coAuthorIndex}][email]"]`).value = data.email || '';
                entry.querySelector(`input[name="co_authors[${coAuthorIndex}][designation]"]`).value = data.designation || '';
                entry.querySelector(`input[name="co_authors[${coAuthorIndex}][department]"]`).value = data.department || '';
                entry.querySelector(`input[name="co_authors[${coAuthorIndex}][institution]"]`).value = data.institution || '';

                const countrySelect = entry.querySelector(`select[name="co_authors[${coAuthorIndex}][country_id]"]`);
                if (countrySelect && data.country_id) {
                    countrySelect.value = data.country_id;
                }

                const priceSelect = entry.querySelector(`select[name="co_authors[${coAuthorIndex}][price_id]"]`);
                if (countrySelect && priceSelect) {
                    syncCategoryOptions(countrySelect, priceSelect);
                    if (data.price_id) {
                        priceSelect.value = data.price_id;
                    }
                }

                const studentCheckbox = entry.querySelector('.co-author-student');
                if (studentCheckbox) {
                    studentCheckbox.checked = String(data.is_student) === '1';
                }
            }

            // Restore presenting author radio selection if it matches the old index
            const oldPresentingAuthorIndex = "{{ old('presenting_author_index', 'submitter') }}";
            if (oldPresentingAuthorIndex !== 'submitter' && String(oldPresentingAuthorIndex) === String(coAuthorIndex)) {
                entry.querySelector('.presenting-author-radio').checked = true;
            }

            container.appendChild(entry);

            updateAuthorIndices();
            coAuthorIndex++;
            renderFeeSummary();
        }

        function removeCoAuthor(btn) {
            const entry = btn.closest('.co-author-entry');
            const radio = entry.querySelector('.presenting-author-radio');
            const wasChecked = radio ? radio.checked : false;

            entry.remove();
            updateAuthorIndices();

            if (wasChecked) {
                const mainPresenter = document.getElementById('presenter_submitter');
                if (mainPresenter) {
                    mainPresenter.checked = true;
                }
            }

            renderFeeSummary();
        }

        function updateAuthorIndices() {
            const entries = document.querySelectorAll('.co-author-entry');
            entries.forEach((entry, idx) => {
                entry.querySelector('.author-index').innerText = idx + 1;
            });
        }

        // Initialize co-authors if we have old data (validation failed)
        @if(old('co_authors'))
        @foreach(old('co_authors') as $idx => $author)
        addCoAuthor(@json($author));
        @endforeach
        @endif


        // Delegate category depends on country: Bangladesh gets the BDT tiers, the
        // other SAARC states get the SAARC rate, everyone else is international.
        // Countries absent from this map are international. The server enforces the
        // same rule via App\Rules\DelegateCategoryMatchesCountry.
        const allowedCategoriesByCountry = @json($countryCategories);
        const defaultAllowedCategories = ['international'];

        function allowedCategoriesFor(countryId) {
            return allowedCategoriesByCountry[countryId] || defaultAllowedCategories;
        }

        function syncCategoryOptions(countrySelect, categorySelect) {
            if (!countrySelect || !categorySelect) return;

            const allowed = allowedCategoriesFor(countrySelect.value);
            let selectedStillAllowed = false;

            Array.from(categorySelect.options).forEach(option => {
                if (!option.value) return;
                const permitted = allowed.includes(option.dataset.category);
                option.hidden = !permitted;
                option.disabled = !permitted;
                if (permitted && option.selected) selectedStillAllowed = true;
            });

            if (!selectedStillAllowed) {
                categorySelect.value = allowed.length === 1
                    ? (Array.from(categorySelect.options).find(o => o.dataset.category === allowed[0])?.value || '')
                    : '';
            }
        }

        function syncMainCategoryOptions() {
            syncCategoryOptions(
                document.getElementById('country_id'),
                document.getElementById('price_id')
            );
        }

        document.addEventListener('change', event => {
            if (event.target.id === 'country_id') {
                syncMainCategoryOptions();
                renderFeeSummary();
                return;
            }
            if (event.target.classList.contains('delegate-country-select')) {
                const row = event.target.closest('.co-author-entry');
                if (row) {
                    syncCategoryOptions(event.target, row.querySelector('.delegate-category-select'));
                }
            }
            renderFeeSummary();
        });

        document.addEventListener('DOMContentLoaded', () => {
            toggleAbstractSection();
            updateSubTracks();
            syncMainCategoryOptions();
            renderFeeSummary();
        });

        // Placeholder — reserved for future submit button state logic
        function checkFormValidity() {
            // intentionally empty — called by radio onchange handlers
        }

    </script>
@endpush

@push('style')
    <style>
        .sidebar-conference-card {
            border-top: 5px solid #003366;
            padding: 24px;
        }
        .conference-name {
            font-size: 18px;
            font-weight: 700;
            color: #003366;
            letter-spacing: 0.5px;
        }
        .conference-theme-tag {
            font-size: 12px;
            color: #64748B;
            font-style: italic;
        }
        .meta-item-pill {
            display: flex;
            align-items: center;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .meta-item-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
            font-weight: 600;
        }
        .meta-item-val {
            font-size: 12px;
            font-weight: 600;
            color: #1E293B;
            line-height: 1.3;
        }
        .sidebar-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #003366;
        }
        .bg-color, .bg-color-form {
            background: #ffffff;
            padding: 30px;
            height: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .bg-color-form {
            border-top: 5px solid #0055A0;
        }
        .title-section {
            padding: 60px 0 30px;
            color: white;
            margin-bottom: 40px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0055A0;
        }
        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }
        .fee-information {
            font-size: 15px;
            line-height: 1.8;
        }
        .fee-table {
            background: #fff;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        .fee-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #0055A0;
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 10px;
        }
        .fee-table td {
            padding: 10px;
            vertical-align: middle;
            border-top: 1px solid #eee;
        }
        .fee-table tbody tr:hover {
            background-color: #EEF4FA;
        }


        /*/*/
        .main-title {
            display: block;
            font-size: 24px;
            color: #000000;
            font-weight: 700;
            line-height: normal;
            font-family: 'edo', sans-serif;
        }
        .second-title {
            display: block;
            font-family: 'edo', sans-serif;
            font-size: 20px;
            color: black;
            line-height: normal;
            margin: 10px 0;


        }
        .sub-title {
            display: block;
            font-family: 'GlacialIndifference-Regular', sans-serif;
            font-size: 18px;
            color: #000000;
            line-height: normal;
            font-weight: bold;
            padding-bottom: 35px;
        }

        @font-face {
            font-family: 'edo';
            src: url('{{"fonts/edo.ttf"}}') format('truetype');
        }
        @font-face {
            font-family: 'GlacialIndifference-Regular';
            src: url('{{"fonts/GlacialIndifference-Regular.otf"}}') format('truetype');
        }


        /* Pill — Mode of Participation */
        .participation-pill-group { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
        .participation-pill { display: inline-flex; align-items: center; cursor: pointer; }
        .participation-pill input { display: none; }
        .participation-pill span {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 18px; font-size: 13px; font-weight: 500;
            border: 1.5px solid #dee2e6; border-radius: 999px;
            color: #666; background: #fff; transition: all 0.18s;
        }
        .participation-pill span::before {
            content: ''; width: 8px; height: 8px; border-radius: 50%;
            background: #ccc; transition: all 0.18s;
        }
        /* Onsite — Orange */
        .participation-pill:nth-child(1) input:checked + span {
            background: #fff4ec; border-color: #E8650A; color: #b84e07;
        }
        .participation-pill:nth-child(1) input:checked + span::before { background: #E8650A; }
        /* Online — Green */
        .participation-pill:nth-child(2) input:checked + span {
            background: #edf7ea; border-color: #3A7D2C; color: #2a5e1f;
        }
        .participation-pill:nth-child(2) input:checked + span::before { background: #3A7D2C; }
        .participation-pill span:hover { border-color: #adb5bd; background: #f8f9fa; }

        /* Card — I want to */
        .intent-card-group { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
        .intent-card {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px; border: 1.5px solid #dee2e6; border-radius: 10px;
            cursor: pointer; background: #fff; transition: all 0.18s;
        }
        .intent-card:hover { border-color: #adb5bd; background: #fafafa; }
        .intent-card input { display: none; }
        .radio-circle {
            width: 20px; height: 20px; border-radius: 50%;
            border: 2px solid #ccc; flex-shrink: 0; margin-top: 1px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.18s;
        }
        .intent-card input:checked ~ .radio-circle::after {
            content: ''; width: 8px; height: 8px; border-radius: 50%; background: white;
        }
        .intent-text { display: flex; flex-direction: column; }
        .intent-title { font-size: 14px; font-weight: 600; color: #333; }
        .intent-sub { font-size: 12px; color: #888; margin-top: 3px; }

        /* Participant card — Green (ছবির right side) */
        .intent-card.participant:has(input:checked) { background: #edf7ea; border-color: #3A7D2C; }
        .intent-card.participant input:checked ~ .radio-circle { background: #3A7D2C; border-color: #3A7D2C; }
        .intent-card.participant input:checked ~ .intent-text .intent-title { color: #2a5e1f; }
        .intent-card.participant input:checked ~ .intent-text .intent-sub { color: #3A7D2C; }

        /* Author card — Orange (ছবির left side) */
        .intent-card.author:has(input:checked) { background: #fff4ec; border-color: #E8650A; }
        .intent-card.author input:checked ~ .radio-circle { background: #E8650A; border-color: #E8650A; }
        .intent-card.author input:checked ~ .intent-text .intent-title { color: #b84e07; }
        .intent-card.author input:checked ~ .intent-text .intent-sub { color: #E8650A; }

        /* এই দুটো line ঠিক আছে কারণ :has() parent কে target করে */
        .intent-card.participant:has(input:checked) { background: #edf7ea; border-color: #3A7D2C; }
        .intent-card.author:has(input:checked) { background: #fff4ec; border-color: #E8650A; }

        /* এগুলো input এর পরের sibling target করে — input label এর direct child হলে কাজ করবে */
        .intent-card.participant input:checked ~ .radio-circle { background: #3A7D2C; border-color: #3A7D2C; }
        .intent-card.author input:checked ~ .radio-circle { background: #E8650A; border-color: #E8650A; }


        /* ========== Corresponding Author Card ========== */
        .custom-check-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            background: #fff;
            transition: all 0.18s;
            margin-bottom: 0;
            width: 100%;
        }
        .custom-check-card:hover {
            border-color: #0055A0;
            background: #f0f7ff;
        }
        .custom-check-card input[type="checkbox"] {
            display: none;
        }
        .custom-check-box {
            width: 22px;
            height: 22px;
            min-width: 22px;
            border: 2px solid #ccc;
            border-radius: 6px;
            margin-top: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s;
            background: #fff;
        }
        .custom-check-card input[type="checkbox"]:checked ~ .custom-check-box {
            background: #0055A0;
            border-color: #0055A0;
        }
        .custom-check-card input[type="checkbox"]:checked ~ .custom-check-box::after {
            content: '';
            display: block;
            width: 5px;
            height: 10px;
            border: 2px solid #fff;
            border-top: none;
            border-left: none;
            transform: rotate(45deg) translate(-1px, -1px);
        }
        .custom-check-card input[type="checkbox"]:checked ~ .custom-check-content .custom-check-title {
            color: #0056cc;
        }
        .custom-check-card:has(input:checked) {
            border-color: #0055A0;
            background: #f0f7ff;
        }
        .custom-check-content {
            display: flex;
            flex-direction: column;
        }
        .custom-check-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
        }
        .custom-check-sub {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }

        /* ========== Declaration Checkboxes ========== */
        .declaration-group {
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }
        .declaration-check-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 16px;
            cursor: pointer;
            margin: 0;
            transition: background 0.15s;
            border-bottom: 1px solid #f0f0f0;
            width: 100%;
        }
        .declaration-check-item:last-of-type {
            border-bottom: none;
        }
        .declaration-check-item:hover {
            background: #f8f9fa;
        }
        .declaration-check-item input[type="checkbox"] {
            display: none;
        }
        .decl-check-box {
            width: 20px;
            height: 20px;
            min-width: 20px;
            border: 2px solid #ccc;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s;
            background: #fff;
        }
        .declaration-check-item input[type="checkbox"]:checked ~ .decl-check-box {
            background: #28a745;
            border-color: #28a745;
        }
        .declaration-check-item input[type="checkbox"]:checked ~ .decl-check-box::after {
            content: '';
            display: block;
            width: 4px;
            height: 9px;
            border: 2px solid #fff;
            border-top: none;
            border-left: none;
            transform: rotate(45deg) translate(-1px, -1px);
        }
        .declaration-check-item input[type="checkbox"]:checked ~ .decl-check-label {
            color: #1a6e30;
            font-weight: 500;
        }
        .declaration-check-item:has(input:checked) {
            background: #f0faf2;
        }
        .decl-check-label {
            font-size: 13.5px;
            color: #444;
            line-height: 1.4;
            transition: color 0.15s;
        }
        .declaration-check-item.is-invalid-item .decl-check-box {
            border-color: #dc3545;
        }

        /* ========== Presenting Author Card ========== */
        .presenting-author-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            background: #fff;
            transition: all 0.18s;
            margin-bottom: 16px;
            width: 100%;
        }
        .presenting-author-card:hover {
            border-color: #0055A0;
            background: #f0f7ff;
        }
        .presenting-author-card input[type="radio"] {
            display: none;
        }
        .presenting-radio-dot {
            width: 22px;
            height: 22px;
            min-width: 22px;
            border: 2px solid #ccc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s;
            background: #fff;
        }
        .presenting-author-card input[type="radio"]:checked ~ .presenting-radio-dot {
            border-color: #0055A0;
            background: #0055A0;
        }
        .presenting-author-card input[type="radio"]:checked ~ .presenting-radio-dot::after {
            content: '';
            display: block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
        }
        .presenting-author-card:has(input:checked) {
            border-color: #0055A0;
            background: #f0f7ff;
        }
        .presenting-author-content {
            display: flex;
            flex-direction: column;
        }
        .presenting-author-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
            transition: color 0.18s;
        }
        .presenting-author-card input[type="radio"]:checked ~ .presenting-author-content .presenting-author-title {
            color: #0056cc;
        }
        .presenting-author-sub {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }

    </style>


@endpush
