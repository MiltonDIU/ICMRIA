@extends('layouts.admin')
@section('content')

<div class="card mb-3">
    <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        @php $retentionDays = (int) config('activitylog.clean_after_days'); @endphp
        <span><i class="fa fa-history mr-2"></i> Activity &amp; Audit Logs (Last {{ $retentionDays }} Days Retention)</span>
        <span class="badge badge-info">Auto Clean: {{ $retentionDays }} Days</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="form-inline mb-3">
            <input type="text" name="search" class="form-control mr-2" placeholder="Search user, action, event..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
            @if(request('search'))
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary ml-2">Reset</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User (Causer)</th>
                        <th>Event</th>
                        <th>Subject / Target</th>
                        <th>Changes (Old vs New)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td style="white-space: nowrap;">
                                {{ $activity->created_at ? $activity->created_at->format('Y-m-d H:i:s') : '-' }}
                                <br><small class="text-muted">{{ $activity->created_at ? $activity->created_at->diffForHumans() : '' }}</small>
                            </td>
                            <td>
                                @if($activity->causer)
                                    <strong>{{ $activity->causer->name ?? 'User' }}</strong><br>
                                    <small class="text-muted">{{ $activity->causer->email ?? '' }}</small>
                                @else
                                    <span class="badge badge-secondary">System / Guest</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($activity->event) {
                                        'created' => 'badge-success',
                                        'updated' => 'badge-info',
                                        'deleted' => 'badge-danger',
                                        default => 'badge-primary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($activity->event ?? $activity->description) }}</span>
                            </td>
                            <td>
                                <strong>{{ class_basename($activity->subject_type ?? '') }}</strong>
                                @if($activity->subject_id)
                                    <small class="text-muted">(ID: {{ $activity->subject_id }})</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    // A model's own changes are stored in attribute_changes;
                                    // properties only carries what a manual activity() call put there.
                                    $changes = collect($activity->attribute_changes)->isNotEmpty()
                                        ? $activity->attribute_changes
                                        : collect($activity->properties);
                                    $attributes = $changes['attributes'] ?? [];
                                    $old = $changes['old'] ?? [];
                                @endphp

                                @if(!empty($attributes))
                                    <small>
                                        @foreach($attributes as $key => $val)
                                            <div>
                                                <strong>{{ $key }}:</strong>
                                                @if(array_key_exists($key, $old))
                                                    <span class="text-danger"><del>{{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}</del></span> &rarr;
                                                @endif
                                                <span class="text-success">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                            </div>
                                        @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">{{ $activity->description }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No activity logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $activities->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection