{{--
    The comment history between a paper's chairs and the TPC Chair, grouped by round.
    $comments  PaperDecisionComment collection with user, oldest first
--}}
@forelse($comments->groupBy('round') as $round => $roundComments)
    <div class="small text-muted text-uppercase font-weight-bold mt-2 mb-1">Round {{ $round }}</div>
    @foreach($roundComments as $comment)
        <div class="pl-3 mb-2" style="border-left: 3px solid {{ $comment->author_role === 'tpc' ? '#343A40' : '#17A2B8' }};">
            <div class="small">
                <span class="badge badge-{{ $comment->author_role === 'tpc' ? 'dark' : 'info' }}">
                    {{ \App\Models\PaperDecisionComment::ROLES[$comment->author_role] ?? $comment->author_role }}
                </span>
                <strong>{{ $comment->user->name ?? 'Former committee member' }}</strong>
                <span class="text-muted">
                    &middot; {{ \App\Models\PaperDecisionComment::KINDS[$comment->kind] ?? $comment->kind }}
                    &middot; {{ $comment->created_at->format('j M Y, g:i A') }}
                </span>
            </div>
            <div style="white-space: pre-line;">{{ $comment->body }}</div>
        </div>
    @endforeach
@empty
    <p class="text-muted mb-0">No comments yet.</p>
@endforelse
