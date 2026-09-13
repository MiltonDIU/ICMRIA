@php
    $messageGroups = $conferenceMessages ?? collect();
@endphp

@if($messageGroups->count() > 0)
<section id="messages" class="wow fadeInUp">
    <div class="container wow fadeInUp">
        <div class="section-header">
            <h2>Messages</h2>
            <p>Messages from the Chief Guest, Patrons and Conference Chairs.</p>
        </div>

        <ul class="nav nav-tabs custom-tabs mb-5 justify-content-center" id="messageTabs" role="tablist">
            @foreach($messageGroups as $category => $items)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                       id="msg-tab-{{ $loop->index }}"
                       data-toggle="tab"
                       href="#msg-content-{{ $loop->index }}"
                       role="tab"
                       aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $category }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="messageTabsContent">
            @foreach($messageGroups as $category => $items)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                     id="msg-content-{{ $loop->index }}"
                     role="tabpanel">
                    @foreach($items as $message)
                        <div class="message-card bg-white shadow-sm rounded p-4 mb-4">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3 mb-md-0">
                                    <img src="{{ $message->photo ? $message->photo->getUrl() : asset('img/default-speaker.jpg') }}"
                                         alt="{{ $message->person_name }}" class="img-fluid rounded message-photo">
                                    <h5 class="mt-3 mb-1 font-weight-bold" style="color: var(--brand-blue);">{{ $message->person_name }}</h5>
                                    @if($message->designation)
                                        <p class="mb-0 small text-dark">{{ $message->designation }}</p>
                                    @endif
                                    @if($message->affiliation)
                                        <p class="mb-0 small text-muted">{{ $message->affiliation }}</p>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    @if($message->variant)
                                        <span class="badge badge-pill mb-2"
                                              style="background: var(--brand-blue); color:#fff;">{{ $message->variant }}</span>
                                    @endif
                                    <div class="message-body text-dark">{!! nl2br(e($message->message)) !!}</div>
                                    @if($message->profile_url)
                                        <a href="{{ $message->profile_url }}" target="_blank" rel="noopener"
                                           class="btn btn-outline-primary btn-sm rounded-pill mt-3"
                                           style="border-color: var(--brand-blue); color: var(--brand-blue);">View Profile</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    #messages { padding: 60px 0; background: #f9f9f9; }
    #messages .custom-tabs .nav-link {
        color: #0e1b4d; font-weight: 600; border: none;
        border-bottom: 3px solid transparent; padding: 10px 25px; transition: all 0.3s ease;
    }
    #messages .custom-tabs .nav-link:hover,
    #messages .custom-tabs .nav-link.active {
        color: var(--brand-blue); border-bottom-color: var(--brand-blue); background: transparent;
    }
    #messages .message-photo { max-width: 160px; width: 100%; object-fit: cover; }
    #messages .message-body { line-height: 1.8; }
</style>
@endif
