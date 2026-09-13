@extends('layouts.admin')
@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.conference-messages.create') }}">
            <i class="fas fa-plus mr-1"></i> Add Message
        </a>
        <a class="btn btn-outline-primary ml-2" href="{{ route('admin.conference-message-categories.index') }}">
            <i class="fas fa-tags mr-1"></i> Manage Message Categories
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header font-weight-bold">
        Conference Messages
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-ConferenceMessage">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Variant</th>
                        <th>Person</th>
                        <th>Designation</th>
                        <th>Published</th>
                        <th>Sort</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $message)
                        <tr data-entry-id="{{ $message->id }}">
                            <td></td>
                            <td>{{ $message->id }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $message->category->name ?? 'General' }}</span>
                            </td>
                            <td>
                                @if($message->variant)
                                    <span class="badge badge-secondary">{{ $message->variant }}</span>
                                @endif
                            </td>
                            <td><strong>{{ $message->person_name }}</strong></td>
                            <td>{{ $message->designation ?? '' }}</td>
                            <td>{!! $message->is_published ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' !!}</td>
                            <td>{{ $message->sort_order }}</td>
                            <td>
                                <a class="btn btn-xs btn-info" href="{{ route('admin.conference-messages.edit', $message->id) }}">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.conference-messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline-block;">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
        let deleteButton = {
            text: 'Delete Selected',
            url: "{{ route('admin.conference-messages.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id')
                });
                if (ids.length === 0) { alert('No rows selected!'); return }
                if (confirm('Are you sure?')) {
                    $.ajax({
                        headers: {'x-csrf-token': _token},
                        method: 'POST',
                        url: config.url,
                        data: { ids: ids, _method: 'DELETE' }
                    }).done(function () { location.reload() })
                }
            }
        }
        dtButtons.push(deleteButton)
        $.extend(true, $.fn.dataTable.defaults, { order: [[ 1, 'desc' ]], pageLength: 100 });
        $('.datatable-ConferenceMessage:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    })
</script>
@endsection