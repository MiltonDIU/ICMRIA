<div class="advisor-list-container mb-3">
    <ul class="list-group list-group-flush shadow-sm rounded">
        @foreach($members as $member)
            <li class="list-group-item advisor-list-item d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3">
                <div class="member-info">
                    <h5 class="mb-1 font-weight-bold" style="color: var(--brand-blue);">{{ $member->name }}</h5>
                    <p class="mb-0 text-dark small">
                        @if($member->pivot->role)
                            <span class="font-weight-bold">{{ $member->pivot->role }}</span>
                            @if($member->designation || $member->institution) &mdash; @endif
                        @endif
                        {{ $member->designation }}@if($member->designation && $member->institution), @endif{{ $member->institution }}
                    </p>
                </div>
                @if($member->profile_url)
                    <div class="member-action mt-2 mt-md-0">
                        <a href="{{ $member->profile_url }}" target="_blank" rel="noopener"
                           class="btn btn-outline-primary btn-sm rounded-pill"
                           style="border-color: var(--brand-blue); color: var(--brand-blue);">View Profile</a>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
