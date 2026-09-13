@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Edit Message
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.conference-messages.update', [$conferenceMessage->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            @include('admin.conference_messages._fields', ['conferenceMessage' => $conferenceMessage])
            <div class="form-group mt-4">
                <button class="btn btn-danger" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@include('admin.conference_messages._dropzone', ['conferenceMessage' => $conferenceMessage])
@endsection
