@php
    $committeeList = ($committees ?? collect())->values();
@endphp

<section id="committee" class="wow section-with-bg">
    <div class="container wow fadeInUp">
        <div class="section-header">
            <h2>Conference Committee</h2>
            <p>The leadership, advisory, organizing, technical, and track committees of ICMRIA 2027.</p>
        </div>

        @if($committeeList->count() > 0)
            <div class="row committee-vertical-wrapper">
                <!-- Left Vertical Tabs -->
                <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
                    <div class="committee-nav-card shadow-sm">
                        <div class="committee-nav-header p-3 border-bottom">
                            <h5 class="mb-0 font-weight-bold" style="color: var(--brand-navy, #0e1b4d); font-size: 15px;">
                                <i class="fa fa-users mr-2" style="color: var(--brand-blue, #1b75bb);"></i> Committees
                            </h5>
                        </div>
                        <div class="nav flex-column nav-pills committee-vertical-nav" id="committeeTabs" role="tablist" aria-orientation="vertical">
                            @foreach($committeeList as $committee)
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                   id="cm-tab-{{ $committee->id }}"
                                   data-toggle="pill"
                                   href="#cm-content-{{ $committee->id }}"
                                   role="tab"
                                   aria-controls="cm-content-{{ $committee->id }}"
                                   aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <span class="committee-tab-name">{{ $committee->name }}</span>
                                    <i class="fa fa-angle-right tab-arrow"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Tab Content -->
                <div class="col-lg-8 col-md-7">
                    <div class="tab-content committee-tab-content shadow-sm p-4 bg-white" id="committeeTabsContent">
                        @foreach($committeeList as $committee)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                 id="cm-content-{{ $committee->id }}"
                                 role="tabpanel"
                                 aria-labelledby="cm-tab-{{ $committee->id }}">

                                <div class="committee-content-header border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h3 class="mb-1 font-weight-bold" style="color: var(--brand-navy, #0e1b4d); font-size: 22px;">
                                            {{ $committee->name }}
                                        </h3>
                                        @if($committee->description)
                                            <p class="text-muted small mb-0">{{ $committee->description }}</p>
                                        @endif
                                    </div>
                                    <span class="badge badge-light px-3 py-2 border rounded-pill text-muted small mt-2 mt-sm-0">
                                        @if($committee->members->count() > 0)
                                            {{ $committee->members->count() }} Members
                                        @elseif($committee->subCommittees->count() > 0)
                                            {{ $committee->subCommittees->count() }} Tracks
                                        @endif
                                    </span>
                                </div>

                                {{-- Direct members --}}
                                @if($committee->members->count() > 0)
                                    @include('main.sections.partials.committee-member-list', ['members' => $committee->members])
                                @endif

                                {{-- Sub-committees (e.g. Track Chairs → per-track groups) --}}
                                @foreach($committee->subCommittees as $sub)
                                    @continue($sub->members->count() === 0)
                                    <div class="sub-committee-group mb-4">
                                        <div class="sub-committee-header p-2 px-3 mb-3 rounded" style="background-color: #f1f6fb; border-left: 4px solid var(--brand-blue, #1b75bb);">
                                            <h4 class="mb-0 font-weight-bold" style="color: var(--brand-navy, #0e1b4d); font-size: 15px;">
                                                {{ $sub->name }}
                                            </h4>
                                        </div>
                                        @include('main.sections.partials.committee-member-list', ['members' => $sub->members])
                                    </div>
                                @endforeach

                                @if($committee->members->count() === 0 && $committee->subCommittees->isEmpty())
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa fa-info-circle fa-2x mb-2 text-primary"></i>
                                        <p class="mb-0">Committee members are being updated by the secretariat.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <p class="text-center">Will be updated soon.</p>
        @endif
    </div>
</section>

@push('style')
<style>
    #committee {
        padding: 60px 0;
    }
    
    #committee .committee-nav-card {
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    
    #committee .committee-nav-header {
        background-color: #f8fafc;
    }
    
    #committee .committee-vertical-nav .nav-link {
        color: var(--brand-navy, #0e1b4d);
        font-weight: 600;
        border-radius: 0;
        border: none;
        border-left: 4px solid transparent;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        border-bottom: 1px solid #f1f3f5;
        transition: all 0.25s ease;
        background: transparent;
        text-decoration: none;
    }
    
    #committee .committee-vertical-nav .nav-link:last-child {
        border-bottom: none;
    }
    
    #committee .committee-vertical-nav .nav-link:hover {
        background-color: #f4f8fc;
        color: var(--brand-blue, #1b75bb);
        padding-left: 22px;
    }
    
    #committee .committee-vertical-nav .nav-link.active {
        background-color: #eaf3fa;
        color: var(--brand-blue, #1b75bb);
        border-left-color: var(--brand-blue, #1b75bb);
        font-weight: 700;
    }
    
    #committee .committee-vertical-nav .nav-link .tab-arrow {
        color: #adb5bd;
        font-size: 13px;
        transition: transform 0.2s ease, color 0.2s ease;
    }
    
    #committee .committee-vertical-nav .nav-link.active .tab-arrow {
        color: var(--brand-blue, #1b75bb);
        transform: translateX(4px);
    }
    
    #committee .committee-tab-content {
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        min-height: 480px;
    }
    
    #committee .advisor-list-item {
        border-left: 4px solid transparent;
        transition: all 0.25s ease;
        border-color: #f1f3f5;
    }
    
    #committee .advisor-list-item:hover {
        border-left-color: var(--brand-blue, #1b75bb);
        background-color: #f8fafc;
        padding-left: 20px !important;
    }
    
    #committee .member-action .btn-outline-primary:hover {
        background-color: var(--brand-blue, #1b75bb);
        color: #fff !important;
    }

    @media (max-width: 767.98px) {
        #committee .committee-vertical-nav .nav-link {
            padding: 10px 14px;
            font-size: 13px;
        }
        #committee .committee-tab-content {
            padding: 20px 15px !important;
        }
    }
</style>
@endpush