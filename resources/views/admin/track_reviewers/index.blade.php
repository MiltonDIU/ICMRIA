@extends('layouts.admin')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header">Reviewers by Track</div>
    <div class="card-body">
        <p class="text-muted mb-0">
            @if($seesEverything)
                You can manage the reviewer pool of every track.
            @else
                You can manage the reviewers of the tracks and sub-tracks you chair.
                An overall Track Chair covers the whole track; a Sub-Track Chair covers their own sub-track.
            @endif
            Adding someone here makes them available for paper assignment; it does not assign them a paper yet.
        </p>
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">
        You are not listed as the chair of any track yet, so there is nothing to manage here.
        An administrator assigns chairs under <strong>Tracks &amp; Chairs</strong>.
    </div>
@endif

@foreach($scopes as $scope)
    @php
        $track = $scope['track'];
        $subTrack = $scope['sub_track'];
        $key = $track->id . ':' . ($subTrack->id ?? 'all');
        $assigned = $reviewersByScope[$key] ?? collect();
        $formId = 'add-reviewer-' . str_replace(':', '-', $key);
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                @if($subTrack)
                    <strong>{{ $subTrack->name }}</strong>
                    <br><small class="text-muted">{{ $track->name }}</small>
                @else
                    <strong>{{ $track->name }}</strong>
                    <br><small class="text-muted">Whole track — covers every sub-track below it</small>
                @endif
            </div>
            <span class="badge {{ $assigned->isEmpty() ? 'badge-warning' : 'badge-success' }}">
                {{ $assigned->count() }} reviewer{{ $assigned->count() === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="card-body">
            @if($assigned->isEmpty())
                <p class="text-muted">No reviewers here yet.</p>
            @else
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Reviewer</th>
                                <th>Email</th>
                                <th>Expertise</th>
                                <th class="text-right">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assigned as $assignment)
                                <tr>
                                    <td>{{ $assignment->user->name ?? 'Unknown' }}</td>
                                    <td><small>{{ $assignment->user->email ?? '' }}</small></td>
                                    <td>
                                        @forelse($assignment->expertise ?? [] as $topic)
                                            <span class="badge badge-light border mr-1">{{ $topic }}</span>
                                        @empty
                                            <small class="text-muted">—</small>
                                        @endforelse
                                    </td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.track-reviewers.destroy', $assignment->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Remove this reviewer from this track? Their account stays.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form action="{{ route('admin.track-reviewers.store') }}" method="POST" id="{{ $formId }}">
                @csrf
                <input type="hidden" name="track_id" value="{{ $track->id }}">
                <input type="hidden" name="sub_track_id" value="{{ $subTrack->id ?? '' }}">

                <div class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">Name*</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">Email*</label>
                        <input type="email" name="email" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold mb-1">Expertise</label>
                        <input type="text" name="expertise" class="form-control form-control-sm"
                               placeholder="{{ $subTrack->name ?? $track->name }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-sm btn-success btn-block">
                            <i class="fa fa-plus"></i> Add Reviewer
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">
                    An existing account with this address is given the Reviewer role and added here.
                    A new address creates an account; the reviewer sets their own password through the reset link.
                </small>
            </form>
        </div>
    </div>
@endforeach

@endsection
