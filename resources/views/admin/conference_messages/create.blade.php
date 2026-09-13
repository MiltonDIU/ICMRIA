@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Create Message
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.conference-messages.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.conference_messages._fields')
            <div class="form-group mt-4">
                <button class="btn btn-danger" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@include('admin.conference_messages._dropzone')
@endsection
