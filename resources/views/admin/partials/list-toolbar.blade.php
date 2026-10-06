{{--
    Search, track filter, sort and page size for a paper list (App\Services\PaperListFilters).
    Expects: $listFilters, $tracks, $route (index route name), $tabParam and $tabValue (the
    tab to stay on), $allTracksLabel (optional), $showPayment (optional: the Fee filter).
--}}
<form method="GET" action="{{ route($route) }}" class="form-row align-items-end">
    @if($tabValue !== '')
        <input type="hidden" name="{{ $tabParam }}" value="{{ $tabValue }}">
    @endif
    <div class="col-md-3 mb-2">
        <label for="list-q" class="small text-muted mb-1">Search</label>
        <input type="search" id="list-q" name="q" value="{{ $listFilters->search }}" class="form-control form-control-sm"
               placeholder="Paper ID, title or author">
    </div>
    @if($tracks->isNotEmpty())
        <div class="col-md-3 mb-2">
            <label for="list-track" class="small text-muted mb-1">Track</label>
            <select id="list-track" name="track" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">{{ $allTracksLabel ?? 'All tracks' }}</option>
                @foreach($tracks as $track)
                    <optgroup label="{{ Str::limit($track->name, 60) }}">
                        <option value="t{{ $track->id }}" @selected($listFilters->track === 't' . $track->id)>Whole track</option>
                        @foreach($track->subTracks as $subTrack)
                            <option value="s{{ $subTrack->id }}" @selected($listFilters->track === 's' . $subTrack->id)>{{ Str::limit($subTrack->name, 60) }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    @endif
    @if($showPayment ?? false)
        <div class="col-md-1 mb-2">
            <label for="list-payment" class="small text-muted mb-1">Fee</label>
            <select id="list-payment" name="payment" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach(\App\Services\PaperListFilters::PAYMENT as $key => $label)
                    <option value="{{ $key }}" @selected($listFilters->payment === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-md-2 mb-2">
        <label for="list-sort" class="small text-muted mb-1">Sort</label>
        <select id="list-sort" name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
            @foreach($listFilters->sorts as $key => $label)
                <option value="{{ $key }}" @selected($listFilters->sort === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-1 mb-2">
        <label for="list-per" class="small text-muted mb-1">Per page</label>
        <select id="list-per" name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
            @foreach(\App\Services\PaperListFilters::PER_PAGE as $n)
                <option value="{{ $n }}" @selected($listFilters->perPage === $n)>{{ $n }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-{{ ($showPayment ?? false) ? 2 : 3 }} mb-2">
        <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Apply</button>
        @if($listFilters->isFiltered())
            <a href="{{ route($route, $tabValue !== '' ? [$tabParam => $tabValue] : []) }}" class="btn btn-sm btn-link">Clear</a>
        @endif
    </div>
</form>
