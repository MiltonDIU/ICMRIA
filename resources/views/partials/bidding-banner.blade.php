{{--
    A notice for reviewers while bidding is open and papers in their tracks still wait for
    their preference. Shown on the dashboard and on My Reviews.
--}}
@can('review_bid')
    @php $biddingNotice = \App\Services\BiddablePapers::notice(auth()->user()); @endphp
    @if($biddingNotice)
        <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center">
            <span class="mr-3 my-1">
                <i class="fas fa-hand-paper mr-1"></i>
                <strong>Paper bidding is open.</strong>
                {{ $biddingNotice['unmarked'] }} of {{ $biddingNotice['total'] }} papers in your tracks are waiting for your preference:
                Want to Review, Can Review, Neutral or Conflict.
            </span>
            <a href="{{ route('admin.paper-bids.index', ['filter' => 'unmarked']) }}" class="btn btn-sm btn-light my-1">
                Mark your preferences
            </a>
        </div>
    @endif
@endcan
