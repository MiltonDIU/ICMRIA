{{--
    Who will attend. The author may change it right up to paying: the fee is charged only
    for attending authors, and plans change after submission. Gone once the fee is paid;
    every other detail follows its own step and deadline.
--}}
@php
    $canChangeAttendance = \App\Services\ProceedingsRules::attendanceChangeAllowed()
        && auth()->id() === $paper->user_id
        && !\App\Services\ProceedingsRules::isPaid($paper)
        && $paper->status !== 'rejected'
        && !\App\Services\ProceedingsRules::isRejected($paper);
@endphp

@if($canChangeAttendance && $paper->authors->isNotEmpty())
    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-user-check mr-2 text-primary"></i> Who Will Attend</span>
            <small class="text-muted">You can change this until the registration fee is paid</small>
        </div>
        <div class="card-body">
            <p class="small text-muted mb-3">
                The registration fee is charged only for the authors ticked here, each at their own delegate category.
                At least one author must attend.
            </p>
            <form action="{{ route('papers.attendance.update', $paper->id) }}" method="POST">
                @csrf
                @foreach($paper->authors as $author)
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="attending_{{ $author->id }}"
                               name="attending[]" value="{{ $author->id }}" {{ $author->is_attending ? 'checked' : '' }}>
                        <label class="custom-control-label pl-2" for="attending_{{ $author->id }}">
                            {{ $author->name }}
                            <small class="text-muted">&middot; {{ $author->price->name ?? 'No category' }}</small>
                        </label>
                    </div>
                @endforeach
                <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="fas fa-save"></i> Save attendance</button>
            </form>
        </div>
    </div>
@endif
