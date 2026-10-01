@php
    $messageGroups = $conferenceMessages ?? collect();
    $defaultCategories = [
        'Chief Patron & Patron' => 'Messages from the Chief Patron and Patrons',
        'Organizing Chair'       => 'Message from the Convener & Organizing Chair',
        'General Chair'          => 'Message from the General Chair',
        'TPC Chair'              => 'Message from the Technical Program Committee Chair',
        'Chief Guest'            => 'Addresses from the Inaugural and Valedictory Chief Guests',
    ];

    $tabCategories = $messageGroups->isNotEmpty() ? $messageGroups->keys() : collect(array_keys($defaultCategories));
@endphp

<section id="messages" class="wow fadeInUp" style="padding: 70px 0; background: #f8fafc;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 style="font-size: 32px; font-weight: 800; color: #003366;">Leadership Messages</h2>
            <p style="color: #64748B;">Messages and addresses from the Chief Guest, Patrons and Conference Chairs</p>
        </div>

        <ul class="nav nav-tabs custom-tabs mb-4 justify-content-center" id="messageTabs" role="tablist" style="border-bottom: 2px solid #E2E8F0;">
            @foreach($tabCategories as $category)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                       id="msg-tab-{{ $loop->index }}"
                       data-toggle="tab"
                       href="#msg-content-{{ $loop->index }}"
                       role="tab"
                       aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                       style="font-weight: 700; font-size: 14.5px; color: #475569; border: none; padding: 12px 20px; transition: all 0.3s;">
                        {{ $category }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content pt-3" id="messageTabsContent">
            @foreach($tabCategories as $category)
                @php
                    $items = $messageGroups->get($category, collect());
                @endphp
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                     id="msg-content-{{ $loop->index }}"
                     role="tabpanel">

                    @if($items->isNotEmpty())
                        @foreach($items as $message)
                            <div class="message-card bg-white shadow-sm rounded-lg p-4 p-md-5 mb-4" style="border: 1px solid #E2E8F0; border-radius: 12px;">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-4 mb-md-0">
                                        <img src="{{ $message->photo ? $message->photo->getUrl() : asset('img/default-speaker.jpg') }}"
                                             alt="{{ $message->person_name }}" class="img-fluid rounded-circle shadow-sm message-photo" style="width: 150px; height: 150px; object-fit: cover;">
                                        <h5 class="mt-3 mb-1 font-weight-bold" style="color: #003366;">{{ $message->person_name }}</h5>
                                        @if($message->designation)
                                            <p class="mb-0 small text-dark font-weight-bold">{{ $message->designation }}</p>
                                        @endif
                                        @if($message->affiliation)
                                            <p class="mb-0 small text-muted">{{ $message->affiliation }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-9 pl-md-4 border-left">
                                        @if($message->variant)
                                            <span class="badge px-3 py-1 font-weight-bold text-uppercase mb-3" style="background: rgba(0, 85, 160, 0.1); color: #0055A0; border-radius: 20px;">
                                                {{ $message->variant }}
                                            </span>
                                        @endif
                                        <div class="message-body text-dark" style="font-size: 15px; line-height: 1.85; color: #334155;">
                                            {!! nl2br(e($message->message)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- Coming Soon Placeholder State as instructed by organizing committee --}}
                        <div class="message-card bg-white shadow-sm p-4 p-md-5 mb-4 text-center mx-auto" style="border: 1px dashed #CBD5E1; border-radius: 12px; max-width: 820px;">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-md-4 text-center mb-3 mb-md-0">
                                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 140px; height: 140px; background: #F1F5F9; border: 2px dashed #CBD5E1;">
                                        <i class="fa fa-user-circle-o fa-5x text-muted" style="opacity: 0.5;"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-left text-center">
                                    <span class="badge badge-light px-3 py-1 text-uppercase font-weight-bold mb-2 text-muted" style="border: 1px solid #CBD5E1; border-radius: 20px; font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fa fa-clock-o mr-1"></i> Coming Soon
                                    </span>
                                    <h4 class="font-weight-bold mb-2" style="color: #003366;">{{ $category }}</h4>
                                    <p class="text-muted mb-0" style="line-height: 1.7; font-size: 14px;">
                                        Official leadership message, address and dignitary photographs are being compiled by the secretariat and will be published shortly.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    #messages .custom-tabs .nav-link {
        border-bottom: 3px solid transparent !important;
    }
    #messages .custom-tabs .nav-link:hover,
    #messages .custom-tabs .nav-link.active {
        color: #0055A0 !important;
        border-bottom: 3px solid #0055A0 !important;
        background: transparent !important;
    }
</style>