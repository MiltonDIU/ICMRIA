{{--
    The internal discussion of one paper.
    $paper              with discussionMessages.user
    $review             ReviewConsolidation, for reviewer numbers
    $viewerIsCommittee  whether reviewer names may be shown beside their numbers
    $canPost            whether the form is offered
--}}
@php $viewer = auth()->user(); @endphp

@forelse($paper->discussionMessages as $message)
    <div class="pl-3 mb-3" style="border-left: 3px solid {{ (int) $message->user_id === (int) $viewer->id ? '#1D4ED8' : '#CBD5E1' }};">
        <div class="small">
            <strong>{{ \App\Services\Discussion::speakerLabel($message, $viewer, $review, $viewerIsCommittee) }}</strong>
            <span class="text-muted">&middot; {{ $message->created_at->format('j M Y, g:i A') }}</span>
        </div>
        <div style="white-space: pre-line;">{{ $message->body }}</div>
    </div>
@empty
    <p class="text-muted">No messages yet.</p>
@endforelse

@if($canPost)
    <form action="{{ route('admin.discussions.store', $paper->id) }}" method="POST" class="mt-3">
        @csrf
        <div class="form-group mb-2">
            <textarea name="body" class="form-control" rows="3" maxlength="5000" required
                      placeholder="Add to the discussion">{{ old('body') }}</textarea>
        </div>
        <button class="btn btn-sm btn-primary"><i class="fas fa-comment"></i> Post message</button>
        <small class="form-text text-muted">
            Reviewers see one another by number only. Nothing here is sent to the authors.
        </small>
    </form>
@else
    <p class="small text-muted mb-0">The decision has been approved, so the discussion is closed.</p>
@endif
