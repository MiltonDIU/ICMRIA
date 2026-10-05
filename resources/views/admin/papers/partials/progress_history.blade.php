{{--
    Every step this paper has taken and who took it (PaperProgressEvent). Names appear
    here, so reviewers never see it: only the author, the administrators and the chairs
    whose scope covers the paper.
--}}
@php
    $canSeeHistory = auth()->id() === $paper->user_id
        || Gate::allows('camera_ready_access')
        || (Gate::allows('paper_access') && \App\Services\ChairScope::for(auth()->user())->canSee($paper));
@endphp

@if($canSeeHistory && $paper->progressEvents->isNotEmpty())
    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-header bg-white py-3">
            <span class="font-weight-bold"><i class="fas fa-history mr-2 text-primary"></i> Progress History</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr><th class="pl-3">When</th><th>Step</th><th>By</th><th>Detail</th></tr>
                    </thead>
                    <tbody>
                        @foreach($paper->progressEvents as $event)
                            <tr>
                                <td class="pl-3 text-nowrap"><small>{{ $event->created_at->format('j M Y, g:i a') }}</small></td>
                                <td><small class="font-weight-bold">{{ $event->label() }}</small></td>
                                <td><small>{{ $event->user->name ?? 'System' }}</small></td>
                                <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($event->note, 120) }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
