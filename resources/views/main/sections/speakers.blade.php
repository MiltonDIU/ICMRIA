@php
    $keynotes = ($keynoteSpeakers instanceof \Illuminate\Support\Collection) 
        ? ($keynoteSpeakers->first() instanceof \Illuminate\Support\Collection ? $keynoteSpeakers->flatten() : $keynoteSpeakers) 
        : collect($keynoteSpeakers ?? []);

    $invited = ($invitedSpeakers instanceof \Illuminate\Support\Collection) 
        ? ($invitedSpeakers->first() instanceof \Illuminate\Support\Collection ? $invitedSpeakers->flatten() : $invitedSpeakers) 
        : collect($invitedSpeakers ?? []);
@endphp

<section id="speakers" class="section-with-bg wow fadeInUp">
    <div class="container">
        <div class="section-header text-center">
            <h2>Conference Speakers</h2>
            <p>Distinguished Keynote and Invited Speakers for ICMRIA 2027</p>
        </div>

        @if($keynotes->isEmpty() && $invited->isEmpty())
            <div class="text-center py-5">
                <p class="text-muted lead">The official speaker line-up will be announced shortly.</p>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- 1. KEYNOTE SPEAKERS                        --}}
        {{-- ========================================== --}}
        @if($keynotes->isNotEmpty())
            <div class="speakers-group-block mb-5">
                <div class="group-header text-center mb-4">
                    <span class="group-badge keynote-badge"><i class="fa fa-star text-warning mr-1"></i> Plenary Keynotes</span>
                    <h3 class="group-title">Keynote Speakers</h3>
                    <div class="title-bar"></div>
                </div>

                <div class="row justify-content-center">
                    @foreach($keynotes as $speaker)
                        @include('main.sections.partials.speaker-card', [
                            'speaker'  => $speaker,
                            'colClass' => 'col-lg-3 col-md-6 col-sm-6 mb-4'
                        ])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- 2. INVITED SPEAKERS & PLENARY TALKS        --}}
        {{-- ========================================== --}}
        @if($invited->isNotEmpty())
            <div class="speakers-group-block mt-4">
                <div class="group-header text-center mb-4">
                    <span class="group-badge invited-badge"><i class="fa fa-microphone mr-1"></i> Technical & Plenary Talks</span>
                    <h3 class="group-title">Invited Speakers</h3>
                    <div class="title-bar"></div>
                </div>

                <div class="row justify-content-center">
                    @foreach($invited as $speaker)
                        @include('main.sections.partials.speaker-card', [
                            'speaker'  => $speaker,
                            'colClass' => 'col-lg-3 col-md-6 col-sm-6 mb-4'
                        ])
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>

@push('style')
<style>
    /* =========================================================
       SPEAKERS SECTION MODERN STYLES
       ========================================================= */
    #speakers {
        padding: 70px 0;
        background: #f8fafc;
    }

    .group-header {
        position: relative;
    }

    .group-badge {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 5px 14px;
        border-radius: 50px;
        display: inline-block;
        margin-bottom: 8px;
    }

    .keynote-badge {
        background: rgba(0, 57, 107, 0.08);
        color: #00396B;
        border: 1px solid rgba(0, 57, 107, 0.2);
    }

    .invited-badge {
        background: rgba(0, 85, 160, 0.08);
        color: #0055A0;
        border: 1px solid rgba(0, 85, 160, 0.2);
    }

    .group-title {
        font-size: 26px;
        font-weight: 800;
        color: #00396B;
        margin-bottom: 10px;
        letter-spacing: -0.3px;
    }

    .title-bar {
        width: 50px;
        height: 3px;
        background: #10BB43;
        margin: 0 auto 20px auto;
        border-radius: 2px;
    }

    /* Speaker Card Styling */
    .speaker-card-item {
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid #e2e8f0 !important;
    }

    .speaker-card-item:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(0, 57, 107, 0.12) !important;
        border-color: #cbd5e1 !important;
    }

    .speaker-photo-wrapper {
        position: relative;
        overflow: hidden;
        background: #e2e8f0;
        height: 260px;
    }

    .speaker-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .speaker-card-item:hover .speaker-img {
        transform: scale(1.05);
    }

    .speaker-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(0, 57, 107, 0.9);
        color: #ffffff;
        font-size: 10.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        backdrop-filter: blur(4px);
    }

    .speaker-track-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #10BB43;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 9px;
        border-radius: 6px;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(4px);
        z-index: 2;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .speaker-track-badge:hover {
        background: #0d9636;
        transform: scale(1.05);
    }

    .speaker-card-body {
        min-height: 180px;
    }

    .speaker-name {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.35;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .speaker-name a {
        color: #00396B;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .speaker-name a:hover {
        color: #10BB43;
    }

    .speaker-affiliation {
        font-size: 12.5px;
        line-height: 1.4;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .speaker-topic {
        font-size: 12px;
        color: #475569;
        line-height: 1.4;
        background: #f1f5f9;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .btn-speaker-profile {
        background: transparent;
        color: #0055A0;
        border: 1px solid #0055A0;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.25s ease;
    }

    .btn-speaker-profile:hover {
        background: #0055A0;
        color: #ffffff;
        border-color: #0055A0;
    }

    @media (max-width: 767.98px) {
        #speakers {
            padding: 50px 0;
        }
        .speaker-photo-wrapper {
            height: 280px;
        }
        .group-title {
            font-size: 22px;
        }
    }
</style>
@endpush
