@extends('layouts.admin')
@section('content')

@can('sub_track_create')
    <div class="mb-3">
        <a class="btn btn-success" href="{{ route('admin.sub-tracks.create') }}">
            <i class="fa fa-plus"></i> Add Sub-Track
        </a>
        <a class="btn btn-info" href="{{ route('admin.tracks.index') }}">
            <i class="fa fa-list"></i> Back to Tracks
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
    <div class="card-header">Sub-Tracks</div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Track</th>
                        <th>Sub-Track</th>
                        <th>Chair</th>
                        <th class="text-center">Papers</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subTracks as $subTrack)
                        <tr>
                            <td><small class="text-muted">{{ $subTrack->track->name ?? '—' }}</small></td>
                            <td><strong>{{ $subTrack->name }}</strong></td>
                            <td>
                                @forelse($subTrack->chairs as $assignment)
                                    <span class="badge badge-success">{{ $assignment->user->name ?? 'Unknown' }}</span>
                                @empty
                                    <span class="badge badge-warning">Not assigned</span>
                                @endforelse
                            </td>
                            <td class="text-center">{{ $subTrack->papers_count }}</td>
                            <td class="text-right">
                                @can('sub_track_edit')
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.sub-tracks.edit', $subTrack->id) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('sub_track_delete')
                                    @if($subTrack->papers_count === 0)
                                        <form action="{{ route('admin.sub-tracks.destroy', $subTrack->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this sub-track?');">
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
                            <td colspan="5" class="text-center text-muted">No sub-tracks yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <small class="text-muted">Chairs are assigned from the parent track's edit screen.</small>
    </div>
</div>

@endsection
