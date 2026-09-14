@extends('layouts.admin')
@section('content')

@can('track_create')
    <div class="mb-3">
        <a class="btn btn-success" href="{{ route('admin.tracks.create') }}">
            <i class="fa fa-plus"></i> Add Track
        </a>
        <a class="btn btn-info" href="{{ route('admin.sub-tracks.index') }}">
            <i class="fa fa-list"></i> Manage Sub-Tracks
        </a>
    </div>
@endcan

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">Conference Tracks</div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Track</th>
                        <th>Overall Chair</th>
                        <th class="text-center">Sub-Tracks</th>
                        <th class="text-center">Papers</th>
                        <th class="text-center">Reviewers / Paper</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tracks as $track)
                        <tr>
                            <td>
                                <strong>{{ $track->name }}</strong>
                            </td>
                            <td>
                                @forelse($track->chairs as $assignment)
                                    <span class="badge badge-success">{{ $assignment->user->name ?? 'Unknown' }}</span>
                                @empty
                                    <span class="badge badge-warning">Not assigned</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                @php
                                    $chairedSubTracks = $track->subTracks->filter(fn ($sub) => $sub->chairs->isNotEmpty())->count();
                                @endphp
                                {{ $track->sub_tracks_count }}
                                @if($track->sub_tracks_count)
                                    <br>
                                    <small class="{{ $chairedSubTracks === $track->sub_tracks_count ? 'text-success' : 'text-danger' }}">
                                        {{ $chairedSubTracks }} chaired
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">{{ $track->papers_count }}</td>
                            <td class="text-center">
                                @if($track->reviewers_per_paper)
                                    <span class="badge badge-info">{{ $track->reviewers_per_paper }}</span>
                                @else
                                    <small class="text-muted">conference default</small>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('track_edit')
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.tracks.edit', $track->id) }}">
                                        <i class="fa fa-edit"></i> Edit &amp; Assign Chairs
                                    </a>
                                @endcan
                                @can('track_delete')
                                    @if($track->papers_count === 0)
                                        <form action="{{ route('admin.tracks.destroy', $track->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this track and its sub-tracks?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No tracks yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
